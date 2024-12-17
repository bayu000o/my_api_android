<?php

require 'koneksi.php'; // File koneksi database
require 'config.php'; // File konfigurasi lainnya
 // Pastikan autoload Midtrans disertakan

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $input = json_decode(file_get_contents("php://input"), true);

    if (isset($input['id_donasi'], $input['id_user'], $input['id_bank'], $input['tanggal_donasi'], $input['nominal_donasi'])) {
        $id_donasi = $input['id_donasi'];
        $id_user = $input['id_user'];
        $id_bank = $input['id_bank'];
        $tanggal_donasi = $input['tanggal_donasi'];
        $nominal_donasi = $input['nominal_donasi'];

        // Buat detail transaksi untuk Midtrans
        $transaction = [
            'payment_type' => 'bank_transfer',
            'bank_transfer' => [
                'bank' => getBankCode($id_bank),
            ],
            'transaction_details' => [
                'order_id' => 'order-' . $id_donasi,
                'gross_amount' => $nominal_donasi,
            ],
        ];

        try {
            // Kirim permintaan ke Midtrans
            $chargeResponse = \Midtrans\Snap::createTransaction($transaction);  // Perbaikan nama kelas dari Shap ke Snap

            if (isset($chargeResponse->status_code) && $chargeResponse->status_code == '201') {
                // Transaksi berhasil dibuat di Midtrans
                $token = $chargeResponse->token; // Ambil token
                $redirect_url = $chargeResponse->redirect_url; // Ambil URL pengalihan
            
                // Simpan data transaksi di database dengan status 'pending'
                $query = "INSERT INTO donasi_detail (id_donasi, id_user, id_bank, tanggal_donasi, nominal_donasi, status_pembayaran, token, redirect_url) 
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $koneksi->prepare($query);
                $status_pembayaran = 'pending'; // Status transaksi
                if ($stmt) {
                    $stmt->bind_param("iiisisis", $id_donasi, $id_user, $id_bank, $tanggal_donasi, $nominal_donasi, $status_pembayaran, $token, $redirect_url);
                    $stmt->execute();
                    $stmt->close();
            
                    $response = [
                        "status" => "success",
                        "message" => "Pembayaran berhasil dibuat, silakan lakukan transfer.",
                        "data" => [
                            "token" => $token,
                            "redirect_url" => $redirect_url
                        ]
                    ];
                } else {
                    $response = ["status" => "error", "message" => "Gagal menyimpan data ke database."];
                }
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
        $response = ["status" => "error", "message" => "Semua data harus disertakan."];
    }

    // Kembalikan respons sebagai JSON
    header('Content-Type: application/json');
    echo json_encode($response);
}

function getBankCode($id_bank) {
    switch ($id_bank) {
        case 1: return 'bca';
        case 2: return 'mandiri';
        case 3: return 'bni';
        default: return 'bca';
    }
}
?>
