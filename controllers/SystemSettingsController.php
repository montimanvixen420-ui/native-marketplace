<?php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/SystemSettings.php';

class SystemSettingsController extends Controller
{
    private SystemSettings $settingsModel;

    // Available payment method options shown as checkboxes
    public const PAYMENT_METHODS = [
        'cod' => 'Cash on Delivery',
        'gcash' => 'GCash',
        'bank_transfer' => 'Bank Transfer',
        'card' => 'Credit/Debit Card',
    ];

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->settingsModel = new SystemSettings();
    }

    // GET /superadmin/settings
    public function index(): void
    {
        $this->requireRole('superadmin');

        $settings = $this->settingsModel->get();
        $enabledMethods = $settings['payment_methods'] !== ''
            ? explode(',', $settings['payment_methods'])
            : [];

        $this->view('superadmin/settings', [
            'name' => $_SESSION['user_name'],
            'settings' => $settings,
            'enabledMethods' => $enabledMethods,
            'paymentOptions' => self::PAYMENT_METHODS,
            'error' => null,
            'saved' => false,
            'active' => 'settings',
        ]);
    }

    // POST /superadmin/settings/update
    public function update(): void
    {
        $this->requireRole('superadmin');

        $commissionRate = (float) ($_POST['commission_rate'] ?? 0);
        $shippingFee = (float) ($_POST['shipping_fee'] ?? 0);
        $freeShippingThresholdRaw = trim($_POST['free_shipping_threshold'] ?? '');
        $freeShippingThreshold = $freeShippingThresholdRaw !== '' ? (float) $freeShippingThresholdRaw : null;
        $paymentMethods = $_POST['payment_methods'] ?? [];

        $validMethodKeys = array_keys(self::PAYMENT_METHODS);
        $paymentMethods = array_values(array_intersect($paymentMethods, $validMethodKeys));

        $error = null;
        if ($commissionRate < 0 || $commissionRate > 100) {
            $error = 'Commission rate must be between 0 and 100.';
        } elseif ($shippingFee < 0) {
            $error = 'Shipping fee cannot be negative.';
        } elseif (empty($paymentMethods)) {
            $error = 'Please enable at least one payment method.';
        }

        if ($error !== null) {
            $settings = $this->settingsModel->get();
            $this->view('superadmin/settings', [
                'name' => $_SESSION['user_name'],
                'settings' => $settings,
                'enabledMethods' => $paymentMethods,
                'paymentOptions' => self::PAYMENT_METHODS,
                'error' => $error,
                'saved' => false,
                'active' => 'settings',
            ]);
            return;
        }

        $this->settingsModel->update($commissionRate, $paymentMethods, $shippingFee, $freeShippingThreshold);

        $settings = $this->settingsModel->get();
        $this->view('superadmin/settings', [
            'name' => $_SESSION['user_name'],
            'settings' => $settings,
            'enabledMethods' => $paymentMethods,
            'paymentOptions' => self::PAYMENT_METHODS,
            'error' => null,
            'saved' => true,
            'active' => 'settings',
        ]);
    }
}