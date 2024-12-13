<?php
// Allow CORS
header("Access-Control-Allow-Origin: *"); // Mengizinkan semua origin
header("Access-Control-Allow-Methods: POST, OPTIONS"); // Izinkan POST dan OPTIONS
header("Access-Control-Allow-Headers: Content-Type"); // Izinkan header Content-Type

// CORS preflight request untuk browser
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit; // Exit jika ini adalah preflight request
}

// Koneksi ke database
$servername = "localhost:3307";
$username = "root";
$password = "";
$dbname = "bp2q";

$conn = new mysqli($servername, $username, $password, $dbname);

// Periksa koneksi
if ($conn->connect_error) {
    die("Koneksi database gagal: " . $conn->connect_error);
} 

// Definisikan direktori penyimpanan file
$upload_dir = 'upload/';

// Periksa apakah ada file yang diunggah
if(isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
    $file = $_FILES['image'];
    $file_name = $file['name'];
    $file_tmp = $file['tmp_name'];
    $file_size = $file['size'];
    $file_type = $file['type'];

    // Buat path lengkap untuk file
    $file_path = $upload_dir . date('Ymd_His') . '_' . str_replace(' ', '_', basename($file_name));

    // Pindahkan file ke direktori penyimpanan
    if(move_uploaded_file($file_tmp, $file_path)) {
        // Simpan informasi file ke database
        $sql = "INSERT INTO storage_image (descs, url, size, type, created_at, updated_at)
                VALUES ('$file_name', '$file_path', $file_size, '$file_type', NOW(), NOW())";

        if ($conn->query($sql) === TRUE) {
            $response = array(
                'status' => 'success',
                'file_name' => $file_name,
                'file_path' => $file_path,
                'file_size' => $file_size,
                'file_type' => $file_type,
                'url' => 'http://localhost:8080/bp2q-pict-up/'.$file_path
            );
            echo json_encode($response);
        } else {
            $response = array(
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat menyimpan file ke database: ' . $conn->error
            );
            echo json_encode($response);
        }
    } else {
        $response = array(
            'status' => 'error',
            'message' => 'Terjadi kesalahan saat mengunggah file.'
        );
        echo json_encode($response);
    }
} else {
    $response = array(
        'status' => 'error',
        'message' => 'Tidak ada file yang diunggah.'
    );
    echo json_encode($response);
}

$conn->close();
?>