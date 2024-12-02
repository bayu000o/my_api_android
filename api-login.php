<?php
// Sertakan file konfigurasi koneksi database
require 'koneksi.php';

// Periksa apakah request adalah POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil data JSON dari body
    $input = json_decode(file_get_contents("php://input"), true);

    // Validasi: Pastikan email dan password ada
    if (isset($input['email']) && isset($input['password'])) {
        $email = $input['email'];
        $password = $input['password']; // Password yang dikirimkan user

        // Query untuk mencari pengguna berdasarkan email
        $sql = "SELECT * FROM user WHERE email = ?";
        $stmt = $koneksi->prepare($sql);

        if ($stmt) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            // Cek apakah pengguna ditemukan
            if ($result->num_rows > 0) {
                // Ambil data pengguna
                $user = $result->fetch_assoc();
                
                // Verifikasi password
                if (password_verify($password, $user['password'])) {
                    $response = [
                        "status" => "success",
                        "message" => "Berhasil login",
                        "user" => [
                            "id_user" => $user['id_user'],
                            "username" => $user['username'],
                            "email" => $user['email'],
                            "no_telp" => $user['no_telp']
                        ]
                    ];
                } else {
                    $response = [
                        "status" => "error",
                        "message" => "Email atau password salah"
                    ];
                }
            } else {
                // Jika email tidak ditemukan
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
        // Jika email atau password tidak ada
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
