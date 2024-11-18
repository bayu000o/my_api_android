<?php
// Sertakan file konfigurasi koneksi database
require 'koneksi.php';

// Periksa apakah request adalah POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil data JSON dari body
    $input = json_decode(file_get_contents("php://input"), true);

    // Cek apakah "email" ada dalam request
    if (isset($input['email'])) {
        $email = $input['email'];

        // Periksa apakah email terdaftar di database
        $sql = "SELECT * FROM user WHERE email = ?";
        $stmt = $koneksi->prepare($sql);

        if ($stmt) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                // Jika email ditemukan, buat token reset password
                $user = $result->fetch_assoc();
                $reset_token = bin2hex(random_bytes(32)); // Token unik
                $token_expiry = date("Y-m-d H:i:s", strtotime('+1 hour')); // Token berlaku 1 jam

                // Simpan token ke database
                $update_sql = "UPDATE user SET reset_token = ?, token_expiry = ? WHERE email = ?";
                $update_stmt = $koneksi->prepare($update_sql);
                $update_stmt->bind_param("sss", $reset_token, $token_expiry, $email);

                if ($update_stmt->execute()) {
                    // Kirim email reset password
                    $reset_link = "http://example.com/reset-password.php?token=$reset_token";
                    $subject = "Reset Password Anda";
                    $message = "Klik link berikut untuk mereset password Anda: $reset_link";
                    $headers = "From: no-reply@example.com";

                    if (mail($email, $subject, $message, $headers)) {
                        $response = [
                            "status" => "success",
                            "message" => "Link reset password telah dikirim ke email Anda."
                        ];
                    } else {
                        $response = [
                            "status" => "error",
                            "message" => "Gagal mengirim email. Coba lagi nanti."
                        ];
                    }
                } else {
                    $response = [
                        "status" => "error",
                        "message" => "Gagal menyimpan token reset password."
                    ];
                }
                $update_stmt->close();
            } else {
                // Jika email tidak ditemukan
                $response = [
                    "status" => "error",
                    "message" => "Email tidak ditemukan."
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
        // Jika email tidak dikirim dalam request
        $response = [
            "status" => "error",
            "message" => "Email harus disertakan"
        ];
    }

    // Kembalikan respons dalam format JSON
    header('Content-Type: application/json');
    echo json_encode($response);
}

// Tutup koneksi jika terhubung
$koneksi->close();
?>
