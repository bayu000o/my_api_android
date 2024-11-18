<?php
// Sertakan file konfigurasi koneksi database
require 'koneksi.php';

// Periksa apakah request adalah POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil data JSON dari body
    $input = json_decode(file_get_contents("php://input"), true);

    // Cek apakah "email" dan "password" ada dalam request
    if (isset($input['email']) && isset($input['password'])) {
        $email = $input['email'];
        $password = md5($input['password']); // Hash password dengan MD5

        // Buat query untuk mencari pengguna berdasarkan email dan password
        $sql = "SELECT * FROM user WHERE email = ? AND password = ?";
        $stmt = $koneksi->prepare($sql);

        if ($stmt) { // Pastikan $stmt berhasil dibuat
            $stmt->bind_param("ss", $email, $password);
            $stmt->execute();
            $result = $stmt->get_result();

            // Cek apakah pengguna ditemukan
            if ($result->num_rows > 0) {
                // Ambil data pengguna
                $user = $result->fetch_assoc();
                $response['data'] = [
                    "status" => "success",
                    "message" => "Berhasil login",
                    "user" => [
                        "id" => $user['id'],
                        "name" => $user['username'],
                        "email" => $user['email'],
                        "level" => $user['level'],
                        "bank" => $user['bank']
                    ]
                ];
            } else {
                // Jika tidak ditemukan
                $response = [
                    "status" => "error",
                    "message" => "Email atau password salah"
                ];
            }

            // Tutup statement
            $stmt->close();
        } else {
            // Jika statement tidak bisa dipersiapkan
            $response = [
                "status" => "error",
                "message" => "Kesalahan pada server. Tidak bisa memproses permintaan."
            ];
        }
    } else {
        // Jika email atau password tidak dikirim dalam request
        $response = [
            "status" => "error",
            "message" => "Email dan password harus disertakan"
        ];
    }

    // Kembalikan respons dalam format JSON
    header('Content-Type: application/json');
    echo json_encode($response);
}

// Tutup koneksi jika terhubung
$koneksi->close();
?>
