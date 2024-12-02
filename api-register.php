<?php
// Sertakan file konfigurasi koneksi database
require 'koneksi.php';

// Periksa apakah request adalah POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil data JSON dari body
    $input = json_decode(file_get_contents("php://input"), true);

    // Pastikan semua data yang dibutuhkan ada dalam input
    if (isset($input['email']) && isset($input['username']) && isset($input['password']) && isset($input['no_telp'])) {
        $email = $input['email'];
        $username = $input['username'];
        $password = $input['password']; // Pastikan password di-hash sebelum disimpan
        $no_telp = $input['no_telp'];

        // Cek apakah email sudah terdaftar
        $checkEmailQuery = "SELECT * FROM user WHERE email = ?";
        $stmt = $koneksi->prepare($checkEmailQuery);

        if ($stmt) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                // Jika email sudah ada
                $response = [
                    "status" => "error",
                    "message" => "Email sudah terdaftar"
                ];
            } else {
                // Jika email belum ada, simpan data ke database
                $insertQuery = "INSERT INTO user (email, username, password, no_telp) VALUES (?, ?, ?, ?)";
                $stmt = $koneksi->prepare($insertQuery);

                if ($stmt) {
                    $hashedPassword =password_hash($password, PASSWORD_BCRYPT); // Ganti dengan hashing yang lebih aman seperti bcrypt di produksi
                    $stmt->bind_param("ssss", $email, $username, $hashedPassword, $no_telp);

                    if ($stmt->execute()) {
                        $response = [
                            "status" => "success",
                            "message" => "Registrasi berhasil",
                            "user" => [
                                "id_user" => $stmt->insert_id,
                                "email" => $email,
                                "username" => $username,
                                "no_telp" => $no_telp
                            ]
                        ];
                    } else {
                        $response = [
                            "status" => "error",
                            "message" => "Gagal menyimpan data ke database"
                        ];
                    }

                    $stmt->close();
                } else {
                    $response = [
                        "status" => "error",
                        "message" => "Kesalahan pada server. Tidak bisa memproses permintaan."
                    ];
                }
            }
        } else {
            $response = [
                "status" => "error",
                "message" => "Kesalahan pada server saat memeriksa email."
            ];
        }
    } else {
        $response = [
            "status" => "error",
            "message" => "Semua data (email, password, username, no_telp) harus disertakan"
        ];
    }

    // Kembalikan respons dalam format JSON
    header('Content-Type: application/json');
    echo json_encode($response);
}

$koneksi->close();
?>
