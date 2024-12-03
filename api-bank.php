<?php
// Sertakan file konfigurasi koneksi database
require 'koneksi.php';

// Periksa apakah request adalah GET
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    // Query untuk mengambil semua data bank
    $sql = "SELECT * FROM bank"; // Nama tabel sesuai database Anda
    $result = $koneksi->query($sql);

    if ($result->num_rows > 0) {
        $banks = [];

        // Loop hasil query dan masukkan ke array
        while ($row = $result->fetch_assoc()) {
            $banks[] = [
                "id" => $row["id"],
                "payment" => $row["payment"],  // Nama bank
                "no_rekening" => $row["no_rekening"],
                "nama_akun" => $row["nama_akun"]
            ];
        }

        // Respons sukses dengan data
        $response = [
            "status" => "success",
            "data" => $banks
        ];
    } else {
        // Jika tidak ada data
        $response = [
            "status" => "error",
            "message" => "Tidak ada data bank yang tersedia"
        ];
    }

    // Kembalikan respons dalam format JSON
    header('Content-Type: application/json');
    echo json_encode($response);
} else {
    // Respons untuk metode request selain GET
    $response = [
        "status" => "error",
        "message" => "Metode request tidak valid"
    ];
    header('Content-Type: application/json');
    echo json_encode($response);
}

$koneksi->close();
?>
