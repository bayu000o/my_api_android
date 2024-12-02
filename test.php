<?php
// Koneksi ke database
$hostName = "localhost";
$userName = "root";
$password = "";
$dbName = "donasi";

$koneksi = mysqli_connect($hostName, $userName, $password, $dbName);

if ($koneksi->connect_error) {
    die("Koneksi database gagal: " . $koneksi->connect_error);
}

// Variabel untuk menampilkan pesan status
$message = '';

// Pastikan folder uploads/ ada dan dapat diakses
$uploadDir = 'uploads/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true); // Buat folder jika belum ada
}

// Proses pengiriman gambar dan data
if (isset($_POST['submit'])) {
    // Ambil data dari form
    $judul = $_POST['judul'];
    $jenis = $_POST['jenis'];  // Menggunakan jenis sebagai kategori
    $admin = $_POST['admin'];
    $target = $_POST['target'];
    $terkumpul = $_POST['terkumpul'];
    $keterangan = $_POST['keterangan'];
    $status = $_POST['status'];
    $waktu = date('Y-m-d H:i:s'); // Waktu upload

    // Cek apakah file gambar sudah diupload
    if (isset($_FILES['gambar'])) {
        // Nama dan lokasi file gambar
        $fileName = $_FILES['gambar']['name'];
        $fileTmpName = $_FILES['gambar']['tmp_name'];
        $fileError = $_FILES['gambar']['error'];
        $fileSize = $_FILES['gambar']['size'];

        // Validasi file gambar
        if ($fileError === 0) {
            if ($fileSize < 5000000) { // Batas ukuran 5MB
                $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                $allowedExt = ['jpg', 'jpeg', 'png', 'gif'];

                if (in_array($fileExt, $allowedExt)) {
                    // Tentukan nama baru untuk file gambar
                    $fileNewName = uniqid('', true) . '.' . $fileExt;
                    $fileDestination = $uploadDir . $fileNewName;

                    // Pindahkan gambar ke folder
                    if (move_uploaded_file($fileTmpName, $fileDestination)) {
                        // Kondisi: jika jenis "donasi", tidak ada kolom lokasi dan kuota
                        if ($jenis == 'donasi') {
                            $sql = "INSERT INTO kegiatan (judul, gambar, id_jenis, id_admin, target, terkumpul, keterangan, status, waktu)
                                    VALUES ('$judul', '$fileDestination', '$jenis', '$admin', '$target', '$terkumpul', '$keterangan', '$status', '$waktu')";
                        } else {
                            $lokasi = $_POST['lokasi'];
                            $kuota = $_POST['kuota'];
                            $sql = "INSERT INTO kegiatan (judul, gambar, id_jenis, id_admin, target, terkumpul, kuota, lokasi, keterangan, status, waktu)
                                    VALUES ('$judul', '$fileDestination', '$jenis', '$admin', '$target', '$terkumpul', '$kuota', '$lokasi', '$keterangan', '$status', '$waktu')";
                        }

                        if ($koneksi->query($sql) === TRUE) {
                            $message = "Gambar berhasil diupload dan data berhasil disimpan!";
                        } else {
                            $message = "Error dalam query: " . $koneksi->error;
                        }
                    } else {
                        $message = "Gagal memindahkan gambar ke folder.";
                    }
                } else {
                    $message = "Ekstensi file tidak diperbolehkan. Harap upload file dengan format jpg, jpeg, png, atau gif.";
                }
            } else {
                $message = "Ukuran file terlalu besar. Maksimal 5MB.";
            }
        } else {
            $message = "Terjadi kesalahan saat mengupload gambar.";
        }
    } else {
        $message = "Harap pilih gambar untuk diupload.";
    }
}

// Ambil data jenis untuk kategori dari database
$sqlJenis = "SELECT id_jenis, nama_jenis FROM jenis";
$resultJenis = $koneksi->query($sqlJenis);
$jenisData = [];
if ($resultJenis && $resultJenis->num_rows > 0) {
    while ($row = $resultJenis->fetch_assoc()) {
        $jenisData[] = $row;
    }
}

// Ambil data admin untuk pemilihan admin
$sqlAdmin = "SELECT id, nama_admin FROM admin"; // Ubah id_admin ke id
$resultAdmin = $koneksi->query($sqlAdmin);
$adminData = [];
if ($resultAdmin && $resultAdmin->num_rows > 0) {
    while ($row = $resultAdmin->fetch_assoc()) {
        $adminData[] = $row;
    }
}

$koneksi->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Kegiatan</title>
</head>
<body>
    <h1>Upload Kegiatan</h1>

    <!-- Tampilkan pesan status -->
    <?php if ($message): ?>
        <div style="color: red;"><?= $message ?></div>
    <?php endif; ?>

    <!-- Form untuk upload kegiatan -->
    <form action="" method="POST" enctype="multipart/form-data">
        <label for="judul">Judul Kegiatan:</label>
        <input type="text" name="judul" id="judul" required><br><br>

        <label for="jenis">Jenis Kegiatan:</label>
        <select name="jenis" id="jenis" required>
            <?php foreach ($jenisData as $jenis): ?>
                <option value="<?= $jenis['id_jenis'] ?>"><?= $jenis['nama_jenis'] ?></option>
            <?php endforeach; ?>
        </select><br><br>

        <label for="admin">Pilih Admin:</label>
        <select name="admin" id="admin" required>
            <?php foreach ($adminData as $admin): ?>
                <option value="<?= $admin['id'] ?>"><?= $admin['nama_admin'] ?></option>
            <?php endforeach; ?>
        </select><br><br>

        <label for="target">Target:</label>
        <input type="number" name="target" id="target" required><br><br>

        <label for="terkumpul">Terkumpul:</label>
        <input type="number" name="terkumpul" id="terkumpul" required><br><br>

        <label for="keterangan">Keterangan:</label>
        <textarea name="keterangan" id="keterangan" required></textarea><br><br>

        <label for="status">Status:</label>
        <select name="status" id="status" required>
            <option value="aktif">Aktif</option>
            <option value="selesai">Selesai</option>
            <option value="gagal">Gagal</option>
        </select><br><br>

        <label for="gambar">Pilih Gambar:</label>
        <input type="file" name="gambar" id="gambar" accept="image/*" required><br><br>

        <div id="extraFields">
            <div id="lokasiInput">
                <label for="lokasi">Lokasi:</label>
                <input type="text" name="lokasi" id="lokasi"><br><br>
            </div>

            <div id="kuotaInput">
                <label for="kuota">Kuota:</label>
                <input type="number" name="kuota" id="kuota"><br><br>
            </div>
        </div>

        <button type="submit" name="submit">Upload</button>
    </form>

    <script>
        document.getElementById("jenis").addEventListener("change", function() {
            var jenis = this.value;
            if (jenis == 'donasi') {
                document.getElementById("lokasiInput").style.display = "none";
                document.getElementById("kuotaInput").style.display = "none";
            } else {
                document.getElementById("lokasiInput").style.display = "block";
                document.getElementById("kuotaInput").style.display = "block";
            }
        });
    </script>
</body>
</html>
