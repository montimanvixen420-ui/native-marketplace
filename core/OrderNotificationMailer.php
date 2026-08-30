<?php

require_once __DIR__ . '/Mailer.php';

class OrderNotificationMailer
{
    public function sendPlaced(int $orderId, Order $orders): void { $this->send($orders->findForNotification($orderId), 'placed'); }
    public function sendStatus(int $orderId, string $status, Order $orders): void { if (in_array($status, ['shipped', 'completed'], true)) $this->send($orders->findForNotification($orderId), $status); }

    private function send(?array $order, string $event): void
    {
        if (!$order || empty($order['customer_email'])) return;
        $safe = fn($value) => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
        $name = $safe($order['customer_name_for_email']);
        $orderNumber = (int) $order['id'];
        $seller = $safe($order['seller_name']);
        $total = number_format((float) $order['total_amount'], 2);
        if ($event === 'placed') {
            $subject = "Order #{$orderNumber} confirmed";
            $eyebrow = 'ORDER CONFIRMED'; $title = 'Your order is confirmed!';
            $message = "We've received your order from {$seller}. The seller will prepare it soon.";
            $accent = '#0f766e'; $icon = '&#10003;';
        } elseif ($event === 'shipped') {
            $subject = "Order #{$orderNumber} has shipped";
            $eyebrow = 'ON THE WAY'; $title = 'Your parcel has shipped!';
            $message = 'Good news—your order is now on its way to you.';
            $accent = '#4f46e5'; $icon = '&#128230;';
        } else {
            $subject = "Order #{$orderNumber} has been delivered";
            $eyebrow = 'DELIVERED'; $title = 'Your parcel was delivered';
            $message = 'Your order has been marked as delivered. We hope you love your purchase!';
            $accent = '#059669'; $icon = '&#10003;';
        }
        $tracking = '';
        if ($event === 'shipped' && !empty($order['courier'])) {
            $tracking = '<tr><td style="padding:0 28px 24px"><div style="border:1px solid #c7d2fe;background:#eef2ff;border-radius:12px;padding:16px"><p style="margin:0 0 5px;font-size:11px;font-weight:700;letter-spacing:1px;color:#4338ca">TRACKING DETAILS</p><p style="margin:0;color:#1e1b4b;font-size:14px;line-height:22px"><strong>' . $safe($order['courier']) . '</strong><br>Tracking no.: ' . $safe($order['tracking_number']) . '</p></div></td></tr>';
        }
        $html = '<!doctype html><html><body style="margin:0;padding:0;background:#f3f4f6;font-family:Arial,Helvetica,sans-serif;color:#1f2937"><table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f3f4f6;padding:28px 12px"><tr><td align="center"><table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:600px;background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 4px 18px rgba(15,23,42,.08)"><tr><td style="background:#111827;padding:24px 28px"><table role="presentation" width="100%" cellspacing="0" cellpadding="0"><tr><td style="color:#ffffff;font-weight:800;font-size:22px;letter-spacing:.5px">TINDA<span style="color:#5eead4">.</span></td><td align="right" style="color:#9ca3af;font-size:12px">Marketplace</td></tr></table></td></tr><tr><td style="padding:30px 28px 18px"><div style="width:42px;height:42px;line-height:42px;text-align:center;border-radius:50%;background:' . $accent . ';color:#ffffff;font-size:20px;font-weight:bold">' . $icon . '</div><p style="margin:18px 0 6px;font-size:11px;font-weight:700;letter-spacing:1.3px;color:' . $accent . '">' . $eyebrow . '</p><h1 style="margin:0;font-size:25px;line-height:32px;color:#111827">' . $title . '</h1><p style="margin:16px 0 0;font-size:15px;line-height:24px;color:#4b5563">Hi ' . $name . ',<br>' . $message . '</p></td></tr><tr><td style="padding:10px 28px 24px"><div style="border:1px solid #e5e7eb;border-radius:12px;padding:17px 18px"><table role="presentation" width="100%" cellspacing="0" cellpadding="0"><tr><td style="font-size:13px;color:#6b7280">Order number</td><td align="right" style="font-size:14px;font-weight:700;color:#111827">#' . $orderNumber . '</td></tr><tr><td colspan="2" style="height:12px"></td></tr><tr><td style="font-size:13px;color:#6b7280">Shop</td><td align="right" style="font-size:14px;font-weight:600;color:#111827">' . $seller . '</td></tr><tr><td colspan="2" style="height:12px"></td></tr><tr><td style="font-size:13px;color:#6b7280">Order total</td><td align="right" style="font-size:16px;font-weight:800;color:#111827">PHP ' . $total . '</td></tr></table></div></td></tr>' . $tracking . '<tr><td style="padding:0 28px 30px"><p style="margin:0;font-size:13px;line-height:20px;color:#6b7280">You can check the latest progress anytime in <strong style="color:#374151">My Orders</strong>.</p></td></tr><tr><td style="border-top:1px solid #e5e7eb;padding:18px 28px;background:#fafafa"><p style="margin:0;font-size:11px;line-height:18px;color:#9ca3af">This is an automatic notification from TINDA Marketplace. Please do not reply to this email.</p></td></tr></table></td></tr></table></body></html>';
        (new Mailer())->send($order['customer_email'], $subject, $html);
    }
}
