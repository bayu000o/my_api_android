<?php
// Koneksi ke database
require 'koneksi.php';

// Cek apakah form di-submit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ambil data dari form
    $judul = $_POST['judul'];
    $kategori = $_POST['kategori'];
    $target = $_POST['target'];
    $terkumpul = $_POST['terkumpul'];
    $lokasi = $_POST['lokasi'];
    $tanggal_tenggat = $_POST['tanggal_tenggat'];
    $keterangan = $_POST['keterangan'];
    $status = $_POST['status'];

    // Proses upload gambar
    $gambar = null;
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
        $uploadDir = 'uploads/';
        $fileName = 'donasi_' . uniqid() . '.' . pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION);
        $filePath = $uploadDir . $fileName;

        // Cek dan buat folder jika belum ada
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Pindahkan file ke folder uploads
        if (move_uploaded_file($_FILES['gambar']['tmp_name'], $filePath)) {
            $gambar = $fileName; // Simpan nama file untuk database
        }
    }

    // Jika gambar gagal diupload, gunakan gambar default
    if (!$gambar) {
        $gambar = 'default.png';
    }

    // Insert data ke database
    $stmt = $koneksi->prepare("
        INSERT INTO donasi (gambar, judul, kategori, target, terkumpul, lokasi, tanggal_tenggat, keterangan, status) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param('sssisssss', $gambar, $judul, $kategori, $target, $terkumpul, $lokasi, $tanggal_tenggat, $keterangan, $status);

    if ($stmt->execute()) {
        echo json_encode([
            "status" => "success",
            "message" => "Data berhasil ditambahkan"
        ]);
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "Gagal menyimpan data"
        ]);
    }

    $stmt->close();
    $koneksi->close();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Donasi</title>
</head>
<body>
    <h1>Form Tambah Donasi</h1>
    <form action="" method="POST" enctype="multipart/form-data">
        <label for="judul">Judul:</label>
        <input type="text" id="judul" name="judul" required><br><br>

        <label for="kategori">Kategori:</label>
        <input type="text" id="kategori" name="kategori" required><br><br>

        <label for="target">Target (Rp):</label>
        <input type="number" id="target" name="target" required><br><br>

        <label for="terkumpul">Terkumpul (Rp):</label>
        <input type="number" id="terkumpul" name="terkumpul" required><br><br>

        <label for="lokasi">Lokasi:</label>
        <input type="text" id="lokasi" name="lokasi" required><br><br>

        <label for="tanggal_tenggat">Tanggal Tenggat:</label>
        <input type="date" id="tanggal_tenggat" name="tanggal_tenggat" required><br><br>

        <label for="keterangan">Keterangan:</label>
        <textarea id="keterangan" name="keterangan"></textarea><br><br>

        <label for="status">Status:</label>
        <select id="status" name="status" required>
            <option value="aktif">Aktif</option>
            <option value="nonaktif">Nonaktif</option>
        </select><br><br>

        <label for="gambar">Upload Gambar:</label>
        <input type="file" id="gambar" name="gambar" accept="image/*"><br><br>

        <button type="submit">Simpan Donasi</button>
    </form>
</body>
</html>
