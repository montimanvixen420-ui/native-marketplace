<?php

class PayMongoCheckout
{
    private string $secretKey;
    private string $baseUrl;
    private string $caBundle;

    public function __construct()
    {
        $config = require __DIR__ . '/../config/paymongo.php';
        $this->secretKey = (string) ($config['secret_key'] ?? '');
        $this->baseUrl = rtrim((string) ($config['base_url'] ?? ''), '/');
        $this->caBundle = (string) ($config['ca_bundle'] ?? '');
    }

    public function isConfigured(): bool
    {
        return $this->secretKey !== '' && $this->baseUrl !== '';
    }

    public function createSession(array $items, array $summary, string $reference): array
    {
        $lineItems = [];
        foreach ($items as $item) {
            $product = $item['product'];
            $label = $product['name'];
            if (!empty($item['variant'])) $label .= ' — ' . $item['variant']['size'];
            // One unit per line makes a voucher discount exact even when a
            // customer buys multiple units of the same product.
            for ($quantity = 0; $quantity < (int) $item['quantity']; $quantity++) {
                $lineItems[] = [
                    'amount' => (int) round((float) $product['price'] * 100),
                    'currency' => 'PHP',
                    'name' => $label,
                    'description' => 'Sold by ' . $product['seller_name'],
                    'quantity' => 1,
                ];
            }
        }

        $subtotal = array_sum(array_map(fn ($seller) => (float) $seller['subtotal'], $summary));
        $grandTotal = array_sum(array_map(fn ($seller) => (float) $seller['total'], $summary));
        $adjustment = $grandTotal - $subtotal;
        if ($adjustment > 0.004) {
            $lineItems[] = [
                'amount' => (int) round($adjustment * 100),
                'currency' => 'PHP',
                'name' => 'Shipping',
                'description' => 'Delivery fee',
                'quantity' => 1,
            ];
        }

        // Checkout Sessions do not accept negative line items, so spread a
        // voucher discount across the individual product units.
        if ($adjustment < -0.004) {
            $discountCentavos = (int) round(abs($adjustment) * 100);
            for ($index = count($lineItems) - 1; $index >= 0 && $discountCentavos > 0; $index--) {
                $deduction = min($lineItems[$index]['amount'], $discountCentavos);
                $lineItems[$index]['amount'] -= $deduction;
                $discountCentavos -= $deduction;
            }
            $lineItems = array_values(array_filter($lineItems, fn ($line) => $line['amount'] > 0));
            if ($discountCentavos > 0 || empty($lineItems)) throw new RuntimeException('The voucher leaves no payable balance for PayMongo checkout.');
        }

        return $this->request('POST', '/v1/checkout_sessions', [
            'data' => ['attributes' => [
                'line_items' => $lineItems,
                'payment_method_types' => ['gcash', 'card', 'paymaya'],
                'description' => 'TINDA Marketplace order',
                'reference_number' => $reference,
                'success_url' => $this->baseUrl . '/checkout/paymongo/success?token=' . rawurlencode($reference),
                'cancel_url' => $this->baseUrl . '/checkout/paymongo/cancel?token=' . rawurlencode($reference),
                'show_description' => true,
                'show_line_items' => true,
            ]],
        ]);
    }

    public function isPaid(string $sessionId): bool
    {
        $session = $this->request('GET', '/v1/checkout_sessions/' . rawurlencode($sessionId));
        $attributes = $session['data']['attributes'] ?? [];
        $status = strtolower((string) ($attributes['payment_intent']['attributes']['status'] ?? $attributes['status'] ?? ''));
        return in_array($status, ['paid', 'succeeded'], true);
    }

    private function request(string $method, string $path, ?array $payload = null): array
    {
        if (!$this->isConfigured()) throw new RuntimeException('PayMongo is not configured. Add PAYMONGO_SECRET_KEY and APP_URL to your server environment.');
        if (!function_exists('curl_init')) throw new RuntimeException('PHP cURL is required for PayMongo payments. Enable extension=curl in php.ini.');

        $curl = curl_init('https://api.paymongo.com' . $path);
        $headers = ['Accept: application/json', 'Authorization: Basic ' . base64_encode($this->secretKey . ':')];
        if ($payload !== null) $headers[] = 'Content-Type: application/json';
        $options = [CURLOPT_RETURNTRANSFER => true, CURLOPT_CUSTOMREQUEST => $method, CURLOPT_HTTPHEADER => $headers, CURLOPT_TIMEOUT => 30, CURLOPT_SSL_VERIFYPEER => true, CURLOPT_SSL_VERIFYHOST => 2];
        if ($this->caBundle !== '' && is_file($this->caBundle)) $options[CURLOPT_CAINFO] = $this->caBundle;
        curl_setopt_array($curl, $options);
        if ($payload !== null) curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($payload, JSON_THROW_ON_ERROR));
        $body = curl_exec($curl);
        $error = curl_error($curl);
        $status = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
        // cURL handles are released automatically on modern PHP versions.
        if ($body === false || $error !== '') throw new RuntimeException('Unable to reach PayMongo: ' . $error);
        $decoded = json_decode($body, true);
       if ($status < 200 || $status >= 300 || !is_array($decoded)) {
    error_log('PAYMONGO DEBUG — status: ' . $status . ' | raw body: ' . $body);
    throw new RuntimeException('DEBUG status=' . $status . ' body=' . substr($body, 0, 500));
}
        return $decoded;
    }
}
