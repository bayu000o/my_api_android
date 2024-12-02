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
        // Verifikasi data sebelum dimasukkan ke dalam array

        // Verifikasi gambar
        $gambarPath = !empty($row['gambar']) && file_exists($row['gambar']) 
            ? 'http://example.com/' . $row['gambar'] 
            : 'http://example.com/uploads/default.png';

        // Verifikasi target dan terkumpul (pastikan angka tidak negatif)
        $target = (is_numeric($row['target']) && $row['target'] >= 0) ? $row['target'] : 0;
        $terkumpul = (is_numeric($row['terkumpul']) && $row['terkumpul'] >= 0) ? $row['terkumpul'] : 0;

        // Verifikasi status (hanya boleh 'aktif' atau 'nonaktif')
       
        // Tambahkan data yang valid
        $donasi_data[] = [
            "id" => $row['id_donasi'],
            "gambar" => $gambarPath,
            "judul" => $row['judul'],
            "kategori" => $row['kategori'],
            "target" => $target,
            "terkumpul" => $terkumpul,
            "lokasi" => $row['lokasi'],
            "tanggal_tenggat" => $row['tanggal_tenggat'],
            "keterangan" => !empty($row['keterangan']) ? $row['keterangan'] : 'Tidak ada keterangan',
            "status" => $row['status']
        ];
    }

    // Cek jika data valid ada yang dikirim
    echo json_encode([
        "status" => "success",
        "data" => $donasi_data
    ]);
} else {
    // Jika tidak ada data ditemukan
    echo json_encode([
        "status" => "error",
        "message" => "Data tidak ditemukan."
    ]);
}

// Menutup koneksi
$koneksi->close();
?>
