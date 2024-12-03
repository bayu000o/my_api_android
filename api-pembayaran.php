<?php
// Sertakan file konfigurasi koneksi database
require 'koneksi.php';

// Periksa apakah request adalah POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil data JSON dari body
    $input = json_decode(file_get_contents("php://input"), true);

    // Validasi: Pastikan id_donasi, id_user, tanggal_donasi, dan nominal_donasi ada
    if (isset($input['id_donasi']) && isset($input['id_user']) && isset($input['tanggal_donasi']) && isset($input['nominal_donasi'])) {
        $id_donasi = $input['id_donasi'];
        $id_user = $input['id_user'];
        $id_bank = $input['id_bank'];
        $tanggal_donasi = $input['tanggal_donasi'];
        $nominal_donasi = $input['nominal_donasi'];

        // Query untuk memasukkan data donasi
        $sql = "INSERT INTO donasi (id_donasi, id_user, id_bank, tanggal_donasi, nominal_donasi) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $koneksi->prepare($sql);

        if ($stmt) {
            // Bind parameter dan eksekusi query
            $stmt->bind_param("iisi", $id_donasi, $id_user, $id_bank, $tanggal_donasi, $nominal_donasi);
            if ($stmt->execute()) {
                // Jika berhasil menambahkan donasi
                $response = [
                    "status" => "success",
                    "message" => "Pembayaran donasi berhasil",
                    "donasi" => [
                        "id_donasi" => $id_donasi,
                        "id_user" => $id_user,
                        "id_bank" => $id_bank,
                        "tanggal_donasi" => $tanggal_donasi,
                        "nominal_donasi" => $nominal_donasi
                    ]
                ];
            } else {
                // Jika terjadi kesalahan saat menambahkan data
                $response = [
                    "status" => "error",
                    "message" => "Gagal menambahkan donasi. Silakan coba lagi"
                ];
            }

            $stmt->close();
        } else {
            // Jika query gagal disiapkan
            $response = [
                "status" => "error",
                "message" => "Kesalahan pada server. Tidak bisa memproses permintaan."
            ];
        }
    } else {
        // Jika ada parameter yang tidak ada
        $response = [
            "status" => "error",
            "message" => "id_donasi, id_user, id_bank, tanggal_donasi, dan nominal_donasi harus disertakan"
        ];
    }

    // Kembalikan respons dalam format JSON
    header('Content-Type: application/json');
    echo json_encode($response);
}

$koneksi->close();
?>
