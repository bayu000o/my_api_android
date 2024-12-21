<?php
require 'vendor/autoload.php'; // Menyertakan autoloader Composer

// Set API key dari Midtrans (ambil dari Dashboard Midtrans)
\Midtrans\Config::$serverKey = 'SB-Mid-server-ELhEiWAvfYk5qcIPNH76FtZy'; // Ganti dengan Server Key Anda
\Midtrans\Config::$isProduction = false; // Set true jika sudah siap untuk produksi
\Midtrans\Config::$isSanitized = true;
\Midtrans\Config::$is3ds = true;
?>