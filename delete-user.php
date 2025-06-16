<?php
session_start();
include('connection.php'); // Ganti dengan koneksi database Anda

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'];

    // Query untuk menghapus pengguna berdasarkan ID
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$user_id]);

    // Set pesan sukses atau gagal
    if ($stmt->rowCount() > 0) {
        $_SESSION['message'] = "User deleted successfully.";
        $_SESSION['msg_type'] = "success";
        $conn->exec("SET @count = 0; UPDATE users SET id = (@count := @count + 1); ALTER TABLE users AUTO_INCREMENT = 1;");
    } else {
        $_SESSION['message'] = "Failed to delete user.";
        $_SESSION['msg_type'] = "error";
    }

    // Redirect kembali ke halaman sebelumnya
    header("Location: view-users.php"); // Ganti dengan halaman yang sesuai
    exit();
}
?>