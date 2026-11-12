<?php

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$host = "localhost";
$username = "ISI_USERNAME";
$password = "ISI_PASSWORD";
$database = "wallows";

try {
    $koneksi = new mysqli(
        $host,
        $username,
        $password,
        $database
    );

    $koneksi->set_charset("utf8mb4");

} catch (mysqli_sql_exception $e) {
    error_log($e->getMessage());
    http_response_code(500);
    exit("Koneksi database gagal. Periksa konfigurasi MySQL.");
}
