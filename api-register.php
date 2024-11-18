<?php
// Sertakan file konfigurasi koneksi database
require 'koneksi.php';

// Daftar bank yang diperbolehkan
$allowedBanks = ["Mandiri", "BCA", "BRI", "BNI", "CIMB", "Permata"];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil data JSON dari body
    $input = json_decode(file_get_contents("php://input"), true);

    // Cek apakah "username", "email", "password", dan "bank" ada dalam request
    if (isset($input['username']) && isset($input['email']) && isset($input['password']) && isset($input['bank'])) {
        $nama = $input['username'];
        $email = $input['email'];
        $password = password_hash($input['password'], PASSWORD_BCRYPT);
        $level = "user";
        $bank = $input['bank'];

        // Validasi pilihan bank
        if (!in_array($bank, $allowedBanks)) {
            $response = [
                "status" => "error",
                "message" => "Bank yang dipilih tidak valid. Pilih antara: " . implode(", ", $allowedBanks)
            ];
        } else {
            // Buat query untuk menyimpan data pengguna ke dalam tabel
            $sql = "INSERT INTO user (username, email, password, level, bank) VALUES (?, ?, ?, ?, ?)";
            $stmt = $koneksi->prepare($sql);

            if ($stmt) {
                // Bind parameter dan eksekusi query
                $stmt->bind_param("sssss", $nama, $email, $password, $level, $bank);
                $stmt->execute();

                if ($stmt->affected_rows > 0) {
                    $user_id = $koneksi->insert_id;
                    $response['data'] = [
                        "status" => "success",
                        "message" => "Registrasi Berhasil",
                        "user" => [
                            "id" => $user_id,
                            "username" => $nama,
                            "email" => $email,
                            "level" => $level,
                            "bank" => $bank
                        ]
                    ];
                } else {
                    $response = [
                        "status" => "error",
                        "message" => "Gagal mendaftar, coba lagi."
                    ];
                }
                $stmt->close();
            } else {
                // Jika statement gagal dipersiapkan
                $response = [
                    "status" => "error",
                    "message" => "Kesalahan pada server."
                ];
            }
        }
    } else {
        // Jika data tidak lengkap
        $response = [
            "status" => "error",
            "message" => "Nama, email, password, dan bank harus disertakan"
        ];
    }

    // Kembalikan respons dalam format JSON
    header('Content-Type: application/json');
    echo json_encode($response);
}

// Tutup koneksi jika terhubung
$koneksi->close();
?>
