<?php
require 'koneksi.php'; // Koneksi ke database
require 'config.php'; // File konfigurasi lainnya
require_once 'vendor/autoload.php'; // Pastikan autoload Midtrans disertakan

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $input = json_decode(file_get_contents("php://input"), true);

    if (isset($input['id_donasi'])) {
        $id_donasi = $input['id_donasi'];

        // Ambil data donasi dari database berdasarkan id_donasi
        $query = "SELECT * FROM donasi_detail WHERE id_donasi = ?";
        $stmt = $koneksi->prepare($query);
        $stmt->bind_param("i", $id_donasi);
        $stmt->execute();
        $result = $stmt->get_result();
        $donasi = $result->fetch_assoc();

        if ($donasi) {
            $nominal_donasi = $donasi['nominal_donasi'];
            $id_bank = $donasi['id_bank'];

            // Buat detail transaksi untuk Midtrans
            $transaction = [
                'payment_type' => 'bank_transfer',
                'bank_transfer' => [
                    'bank' => getBankCode($id_bank),
                ],
                'transaction_details' => [
                    'order_id' => 'order-' . uniqid('donasi_', true),
                    'gross_amount' => $nominal_donasi,
                ],
            ];

            try {
                // Kirim permintaan ke Midtrans
                \Midtrans\Config::$serverKey = 'SB-Mid-server-ELhEiWAvfYk5qcIPNH76FtZy';
                \Midtrans\Config::$isProduction = false; // Gunakan true jika di environment production
                $chargeResponse = \Midtrans\Snap::createTransaction($transaction);

                if (isset($chargeResponse->status_code) && $chargeResponse->status_code == '201') {
                    // Transaksi berhasil dibuat di Midtrans
                    $token = $chargeResponse->token; // Ambil token
                    $redirect_url = $chargeResponse->redirect_url; // Ambil URL pengalihan

                    // Simpan status transaksi di database
                    $query = "UPDATE donasi_detail SET status_pembayaran = 'pending' WHERE id_donasi = ?";
                    $stmt = $koneksi->prepare($query);
                    $stmt->bind_param("i", $id_donasi);
                    $stmt->execute();

                    $response = [
                        "status" => "success",
                        "message" => "Pembayaran berhasil dibuat, silakan lakukan transfer.",
                        "data" => [
                            "token" => $token,
                            "redirect_url" => $redirect_url // Link pembayaran diberikan di sini
                        ]
                    ];
                } else {
                    $response = [
                        "status" => "error",
                        "message" => "Transaksi gagal dibuat.",
                        "data" => $chargeResponse
                    ];
                }
            } catch (Exception $e) {
                $response = ["status" => "error", "message" => $e->getMessage()];
            }
        } else {
            $response = [
                "status" => "error",
                "message" => "Donasi tidak ditemukan."
            ];
        }
    } else {
        $response = [
            "status" => "error",
            "message" => "ID donasi harus disertakan."
        ];
    }

    // Kembalikan respons sebagai JSON
    header('Content-Type: application/json');
    echo json_encode($response);
}

function getBankCode($id_bank) {
    switch ($id_bank) {
        case 1: return 'bca';
        case 2: return 'mandiri';
        case 3: return 'bri';
        case 3: return 'bri';
        default: return 'bca';
    }
}
?>
