<?php
// Konfigurasi
require 'koneksi.php';
try {
    // Query untuk mengambil data dari tabel donasi dengan kategori 'bencana'
    $query = "
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
        FROM donasi d
        WHERE d.kategori = 'bencana'  -- Tambahkan kondisi untuk kategori 'bencana'
    ";

    // Eksekusi query
    $result = mysqli_query($koneksi, $query);

    // Periksa jika data ada
    if (mysqli_num_rows($result) > 0) {
        $donasi = [];

        // Loop data hasil query
        while ($row = mysqli_fetch_assoc($result)) {
            $donasi[] = [
                "id_donasi" => $row['id_donasi'],
                "gambar" => $row['gambar'],
                "judul" => $row['judul'],
                "kategori" => $row['kategori'],
                "target" => $row['target'],
                "terkumpul" => $row['terkumpul'],
                "lokasi" => $row['lokasi'],
                "tanggal_tenggat" => $row['tanggal_tenggat'],
                "keterangan" => $row['keterangan'],
                "status" => $row['status'],
            ];
        }

        // Kirim response JSON
        echo json_encode([
            "status" => "success",
            "data" => $donasi,
        ]);
    } else {
        // Jika data kosong
        echo json_encode([
            "status" => "error",
            "message" => "Tidak ada data donasi yang ditemukan untuk kategori 'bencana'",
        ]);
    }
} catch (Exception $e) {
    // Tangani error
    echo json_encode([
        "status" => "error",
        "message" => "Terjadi kesalahan: " . $e->getMessage(),
    ]);
}

// Tutup koneksi
mysqli_close($koneksi);
?>
