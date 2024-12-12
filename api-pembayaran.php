<?php
// Sertakan file konfigurasi koneksi database
require 'koneksi.php';

// Periksa apakah request adalah POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil data JSON dari body
    $input = json_decode(file_get_contents("php://input"), true);

    // Pastikan semua data yang dibutuhkan ada dalam input
    if (isset($input['id_donasi']) && isset($input['id_user']) && isset($input['id_bank']) && isset($input['tanggal_donasi']) && isset($input['nominal_donasi'])) {
        $id_donasi = $input['id_donasi'];
        $id_user = $input['id_user'];
        $id_bank = $input['id_bank'];
        $tanggal_donasi = $input['tanggal_donasi'];
        $nominal_donasi = $input['nominal_donasi'];

        // Validasi format tanggal (misalnya format YYYY-MM-DD)
        $tanggal_format = DateTime::createFromFormat('Y-m-d', $tanggal_donasi);
        if (!$tanggal_format) {
            $response = [
                "status" => "error",
                "message" => "Format tanggal tidak valid. Gunakan format YYYY-MM-DD."
            ];
        } else {
            // Simpan data pembayaran ke database tanpa memeriksa apakah sudah ada atau belum
            $insertQuery = "INSERT INTO donasi_detail (id_donasi, id_user, id_bank, tanggal_donasi, nominal_donasi) VALUES (?, ?, ?, ?, ?)";
            $stmt = $koneksi->prepare($insertQuery);

            if ($stmt) {
                $stmt->bind_param("iiisi", $id_donasi, $id_user, $id_bank, $tanggal_donasi, $nominal_donasi);

                if ($stmt->execute()) {
                    $response = [
                        "status" => "success",
                        "message" => "Pembayaran berhasil ditambahkan",
                        "data" => [
                            "id_donasi" => $id_donasi,
                            "id_user" => $id_user,
                            "id_bank" => $id_bank,
                            "tanggal_donasi" => $tanggal_donasi,
                            "nominal_donasi" => $nominal_donasi
                        ]
                    ];
                } else {
                    $response = [
                        "status" => "error",
                        "message" => "Gagal menyimpan data pembayaran ke database."
                    ];
                }

                $stmt->close();
            } else {
                $response = [
                    "status" => "error",
                    "message" => "Kesalahan pada server. Tidak bisa memproses permintaan."
                ];
            }
        }
    } else {
        $response = [
            "status" => "error",
            "message" => "Semua data (id_donasi, id_user, id_bank, tanggal_donasi, nominal_donasi) harus disertakan"
        ];
    }

    // Kembalikan respons dalam format JSON
    header('Content-Type: application/json');
    echo json_encode($response);
}

$koneksi->close();
?>
