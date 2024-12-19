<?php
require 'koneksi.php'; // Koneksi ke database

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $input = json_decode(file_get_contents("php://input"), true);

    // Pastikan semua data yang dibutuhkan ada
    if (isset($input['id_donasi'], $input['id_user'], $input['id_bank'], $input['tanggal_donasi'], $input['nominal_donasi'])) {
        $id_donasi = $input['id_donasi'];
        $id_user = $input['id_user'];
        $id_bank = $input['id_bank'];
        $tanggal_donasi = $input['tanggal_donasi'];
        $nominal_donasi = $input['nominal_donasi'];

        // Simpan data transaksi di database dengan status 'pending'
        $query = "INSERT INTO donasi_detail (id_donasi, id_user, id_bank, tanggal_donasi, nominal_donasi) 
                  VALUES (?, ?, ?, ?, ?)";
        $stmt = $koneksi->prepare($query);

        if ($stmt) {
            $stmt->bind_param("iiisi", $id_donasi, $id_user, $id_bank, $tanggal_donasi, $nominal_donasi);
            $stmt->execute();
            $stmt->close();

            $response = [
                "status" => "success",
                "message" => "Data donasi berhasil disimpan.",
                "data" => [
                    "id_donasi" => $id_donasi,
                    "nominal_donasi" => $nominal_donasi
                ]
            ];
        } else {
            $response = [
                "status" => "error",
                "message" => "Gagal menyimpan data donasi ke database."
            ];
        }
    } else {
        $response = [
            "status" => "error",
            "message" => "Semua data harus disertakan."
        ];
    }

    // Kembalikan respons sebagai JSON
    header('Content-Type: application/json');
    echo json_encode($response);
}
?>
