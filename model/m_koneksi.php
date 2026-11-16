<?php
$server   = "127.0.0.1";
$username = "root";
$password = "root"; // kalau tanpa password isi ""
$database = "parkir";

$conn = mysqli_connect($server, $username, $password, $database);

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
// else {
//     echo "Koneksi berhasil";
// }
?>