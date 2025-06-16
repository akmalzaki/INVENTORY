<?php
session_start();
include('connection.php'); // Ganti dengan koneksi database Anda

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $asset_id = $_POST['asset_id'];

    // Query untuk menghapus pengguna berdasarkan ID
    $stmt = $conn->prepare("DELETE FROM assets WHERE id = ?");
    $stmt->execute([$asset_id]);

    // Set pesan sukses atau gagal
    if ($stmt->rowCount() > 0) {
        $_SESSION['message'] = "Successfully deleted.";
        $_SESSION['msg_type'] = "success";
        $conn->exec("SET @count = 0; UPDATE assets SET id = (@count := @count + 1); ALTER TABLE assets AUTO_INCREMENT = 1;");
    } else {
        $_SESSION['message'] = "Failed to delete user.";
        $_SESSION['msg_type'] = "error";
    }

    // Redirect kembali ke halaman sebelumnya
    header("Location: asset-view.php"); // Ganti dengan halaman yang sesuai
    exit();
}
