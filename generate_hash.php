<?php

// I-run ito para makagawa ng hashed password para sa superadmin seed.
// Paraan 1 (CLI):  php generate_hash.php
// Paraan 2 (browser): buksan http://localhost:8000/../generate_hash.php
//                      (o kahit saan mo ilagay, basta ma-access ng browser)

$password = 'SuperAdmin123!'; // ← palitan ng gusto mong password

echo "Password: {$password}\n";
echo "Hashed:   " . password_hash($password, PASSWORD_DEFAULT) . "\n";
echo "\nI-copy ang 'Hashed' value at i-paste sa INSERT statement sa database/schema.sql\n";
