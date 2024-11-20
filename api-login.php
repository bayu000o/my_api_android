<?php
// Sertakan file konfigurasi koneksi database
require 'koneksi.php';

// Periksa apakah request adalah POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil data JSON dari body
    $input = json_decode(file_get_contents("php://input"), true);

    // Pastikan email dan password ada dalam input
    if (isset($input['email']) && isset($input['password'])) {
        $email = $input['email'];
        $password = md5($input['password']); // Hash password dengan MD5

        // Query untuk mencari pengguna berdasarkan email dan password
        $sql = "SELECT * FROM user WHERE email = ? AND password = ?";
        $stmt = $koneksi->prepare($sql);

        if ($stmt) {
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
                        "id" => $user['id_user'],                       
                        "email" => $user['email'],
                        "name" => $user['username'],
                        "no_telp" => $user['no_telp'],
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

            $stmt->close();
        } else {
            $response = [
                "status" => "error",
                "message" => "Kesalahan pada server. Tidak bisa memproses permintaan."
            ];
        }
    } else {
        $response = [
            "status" => "error",
            "message" => "Email dan password harus disertakan"
        ];
    }

    // Kembalikan respons dalam format JSON
    header('Content-Type: application/json');
    echo json_encode($response);
}

$koneksi->close();
?>
