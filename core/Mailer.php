<?php

class Mailer
{
    private array $config;
    private $connection;

    public function __construct()
    {
        $this->config = require __DIR__ . '/../config/mail.php';
    }

    public function isConfigured(): bool
    {
        return $this->config['username'] !== ''
            && $this->config['password'] !== ''
            && filter_var($this->config['from_email'], FILTER_VALIDATE_EMAIL);
    }

    public function send(string $to, string $subject, string $html): bool
    {
        if (!$this->isConfigured() || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        $host = (string) $this->config['host'];
        $prefix = $this->config['encryption'] === 'ssl' ? 'ssl://' : 'tcp://';
        $this->connection = @stream_socket_client($prefix . $host . ':' . (int) $this->config['port'], $errno, $error, 15, STREAM_CLIENT_CONNECT, stream_context_create([
            'ssl' => ['verify_peer' => true, 'verify_peer_name' => true, 'peer_name' => $host],
        ]));
        if (!$this->connection) {
            return false;
        }

        stream_set_timeout($this->connection, 15);
        try {
            $this->expect([220]);
            $this->command('EHLO localhost', [250]);
            if ($this->config['encryption'] === 'tls') {
                $this->command('STARTTLS', [220]);
                if (!stream_socket_enable_crypto($this->connection, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                    throw new RuntimeException('Could not enable SMTP encryption.');
                }
                $this->command('EHLO localhost', [250]);
            }
            $this->command('AUTH LOGIN', [334]);
            $this->command(base64_encode((string) $this->config['username']), [334]);
            $this->command(base64_encode((string) $this->config['password']), [235]);
            $this->command('MAIL FROM:<' . $this->config['from_email'] . '>', [250]);
            $this->command('RCPT TO:<' . $to . '>', [250, 251]);
            $this->command('DATA', [354]);

            $headers = [
                'From: ' . $this->encodeHeader((string) $this->config['from_name']) . ' <' . $this->config['from_email'] . '>',
                'To: <' . $to . '>',
                'Subject: ' . $this->encodeHeader($subject),
                'MIME-Version: 1.0',
                'Content-Type: text/html; charset=UTF-8',
            ];
            $body = implode("\r\n", $headers) . "\r\n\r\n" . $html;
            $body = preg_replace('/(?m)^\./', '..', $body);
            $this->command($body . "\r\n.", [250]);
            $this->command('QUIT', [221]);
            return true;
        } catch (Throwable $e) {
            return false;
        } finally {
            if (is_resource($this->connection)) {
                fclose($this->connection);
            }
        }
    }

    private function command(string $command, array $expected): void
    {
        fwrite($this->connection, $command . "\r\n");
        $this->expect($expected);
    }

    private function expect(array $expected): void
    {
        do {
            $line = fgets($this->connection, 515);
            if ($line === false) {
                throw new RuntimeException('No response from SMTP server.');
            }
            $code = (int) substr($line, 0, 3);
        } while (isset($line[3]) && $line[3] === '-');
        if (!in_array($code, $expected, true)) {
            throw new RuntimeException('Unexpected SMTP response.');
        }
    }

    private function encodeHeader(string $value): string
    {
        return '=?UTF-8?B?' . base64_encode(str_replace(["\r", "\n"], '', $value)) . '?=';
    }
}
