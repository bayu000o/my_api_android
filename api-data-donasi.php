<?php
// Koneksi ke database
require 'koneksi.php';

// Query untuk mendapatkan data donasi
$sql = "
    SELECT 
        d.id_donasi,
        d.gambar,
        d.judul,
        d.kategori,
        d.target,
        d.terkumpul,
        d.lokasi,
        d.tanggal_tenggat,
        d.keterangan,
        d.status
    FROM 
        donasi d
";

$result = $koneksi->query($sql);

// Menyiapkan data untuk output
if ($result && $result->num_rows > 0) {
    $donasi_data = [];
    
    while ($row = $result->fetch_assoc()) {
        // Path ke gambar berdasarkan nama file di database
        $gambarPath = "http://localhost/uploads/{$row['gambar']}";

        // Cek jika file gambar ada di folder uploads
        $filePath = "uploads/{$row['gambar']}";
        if (!file_exists($filePath)) {
            // Jika file gambar tidak ada, gunakan gambar default
            $gambarPath = 'http://localhost/uploads/default.png';
        }

        // Tambahkan data yang valid
        $donasi_data[] = [
            "id" => $row['id_donasi'],
            "gambar" => $gambarPath,
            "judul" => $row['judul'],
            "kategori" => $row['kategori'],
            "target" => $row['target'],
            "terkumpul" => $row['terkumpul'],
            "lokasi" => $row['lokasi'],
            "tanggal_tenggat" => $row['tanggal_tenggat'],
            "keterangan" => !empty($row['keterangan']) ? $row['keterangan'] : 'Tidak ada keterangan',
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

$koneksi->close();
?>
