
<?php
require 'koneksi.php';
require 'config.php';

function getBankCode($id_bank) {
    switch ($id_bank) {
        case 1:
            return 'bca';
        case 2:
            return 'mandiri';
        case 3:
            return 'bri';
        default:
            return 'bca';
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $input = json_decode(file_get_contents("php://input"), true);

    if (isset($input['id_donasi'], $input['id_user'], $input['id_bank'], $input['tanggal_donasi'], $input['nominal_donasi'])) {
        $id_donasi = filter_var($input['id_donasi'], FILTER_SANITIZE_NUMBER_INT);
        $id_user = filter_var($input['id_user'], FILTER_SANITIZE_NUMBER_INT);
        $id_bank = filter_var($input['id_bank'], FILTER_SANITIZE_NUMBER_INT);
        $tanggal_donasi = htmlspecialchars($input['tanggal_donasi']);
        $nominal_donasi = filter_var($input['nominal_donasi'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

        $query = "INSERT INTO donasi_detail (id_donasi, id_user, id_bank, tanggal_donasi, nominal_donasi) VALUES (?, ?, ?, ?, ?)";
        $stmt = $koneksi->prepare($query);
        $stmt->bind_param("iiisi", $id_donasi, $id_user, $id_bank, $tanggal_donasi, $nominal_donasi);

        if ($stmt->execute()) {
            $stmt->close();

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

            $chargeResponse = \Midtrans\Snap::createTransaction($transaction);

            if (isset($chargeResponse->token) && isset($chargeResponse->redirect_url)) {
                $token = $chargeResponse->token;
                $redirect_url = $chargeResponse->redirect_url;

                $response = [
                    "status" => "redirect",
                    "url" => $redirect_url
                ];
            } else {
                $response = [
                    "status" => "error",
                    "message" => "Transaksi gagal dibuat.",
                    "data" => $chargeResponse
                ];
            }
        } else {
            $response = [
                "status" => "error",
                "message" => "Gagal menyimpan data donasi ke database: " . $koneksi->error
            ];
        }
    } else {
        $response = [
            "status" => "error",
            "message" => "Semua data harus disertakan."
        ];
    }

    header('Content-Type: application/json');
    echo json_encode($response);
}
?>
