<?php
// Koneksi ke database
require 'koneksi.php';

// Query untuk mendapatkan data donasi beserta kategori
$sql = "
    SELECT 
        k.id_kegiatan,
        k.gambar,
        k.judul,
        j.nama_jenis AS jenis,
        k.target,
        k.lokasi,
        k.keterangan,
        k.waktu
    FROM 
        kegiatan k
    JOIN 
        jenis j
    ON 
        k.id_jenis = j.id_jenis
    WHERE
        j.nama_jenis = 'volunteer'
";

$result = $koneksi->query($sql);

// Menyiapkan data untuk output
if ($result && $result->num_rows > 0) {
    $donasi_data = [];
    
    while ($row = $result->fetch_assoc()) {
        $donasi_data[] = [
            "id" => $row['id_kegiatan'],
            "gambar" => $row['gambar'],
            "judul" => $row['judul'],
            "kategori" => $row['jenis'],
            "target" => $row['target'],
            "lokasi" => $row['lokasi'],
            "keterangan" => $row['keterangan'],
            "waktu" => $row['waktu']
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
