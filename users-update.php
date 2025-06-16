<?php
session_start();
include('connection.php'); // Database connection

try {
    // Cek jika user_id ada
    if (isset($_POST['user_id'])) {
        $user_id = $_POST['user_id'];

        // Ambil data pengguna
        $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Jika pengguna tidak ditemukan, redirect
        if (!$user) {
            $_SESSION['message'] = "User not found!";
            $_SESSION['msg_type'] = "error";
            header("location: view-users.php");
            exit;
        }
    } else {
        // Redirect jika tidak ada user_id
        header("location: view-users.php");
        exit;
    }

    // Proses update pengguna setelah form disubmit
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_user'])) {
        $first_name = $_POST['first_name'];
        $last_name = $_POST['last_name'];
        $email = $_POST['email'];
        $role = $_POST['role'];

        // Validasi input
        if (empty($first_name) || empty($last_name) || empty($email) || empty($role)) {
            $_SESSION['message'] = "All fields are required!";
            $_SESSION['msg_type'] = "error";
            header("location: user-edit.php?user_id=" . $user_id);
            exit;
        }

        // Update data pengguna di database
        $query = "UPDATE users 
                  SET first_name = ?, 
                      last_name = ?, 
                      email = ?, 
                      role = ? 
                  WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->execute([
            $first_name,
            $last_name,
            $email,
            $role,
            $user_id
        ]);

        $_SESSION['message'] = "User updated successfully!";
        $_SESSION['msg_type'] = "success";
        header('location: view-users.php');
        exit;
    }
} catch (PDOException $e) {
    $_SESSION['message'] = "Database error: " . $e->getMessage();
    $_SESSION['msg_type'] = "error";
    header("location: view-users.php");
    exit;
}
?>