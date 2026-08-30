<?php

return [
    'secret_key' => $_SERVER['PAYMONGO_SECRET_KEY'] ?? getenv('PAYMONGO_SECRET_KEY') ?: '',
    'base_url' => rtrim($_SERVER['APP_URL'] ?? getenv('APP_URL') ?: '', '/'),
    'ca_bundle' => $_SERVER['PAYMONGO_CA_BUNDLE'] ?? getenv('PAYMONGO_CA_BUNDLE') ?: '',
];