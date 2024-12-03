<?php
// Koneksi ke database
require 'koneksi.php';

// Query untuk mendapatkan data donasi beserta kategori
$sql = "
    SELECT 
        v.id_volunteer,
        v.gambar,
        v.judul,
        v.kategori,
        v.kuota,
        v.waktu_pelaksanaan,
        v.lokasi,
        v.keterangan,
        v.status
    FROM 
        volunteer v
";

$result = $koneksi->query($sql);

// Menyiapkan data untuk output
if ($result && $result->num_rows > 0) {
    $donasi_data = [];
    
    while ($row = $result->fetch_assoc()) {
        $donasi_data[] = [
            "id" => $row['id_volunteer'],
            "gambar" => $row['gambar'],
            "judul" => $row['judul'],
            "kategori" => $row['kategori'],
            "kuota" => $row['kuota'],
            "waktu" => $row['waktu_pelaksanaan'],
            "lokasi" => $row['lokasi'],
            "keterangan" => $row['keterangan'],
            "status" => $row['status']
        ];
    }

    echo json_encode([
        "status" => "success",
        "data" => $donasi_data
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Data tidak ditemukan."
    ]);
}

// Menutup koneksi
$koneksi->close();
?>
