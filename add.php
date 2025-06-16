<?php
session_start();
include('connection.php'); // Ensure connection to the database is included

// Redirect if the user is not authenticated
if (!isset($_SESSION['user'])) {
    header('location: login_pages.php');
    exit();
}

// Ambil nama tabel dari session
$table_name = $_POST['table'] ?? '';

// Tangani data untuk tabel users
if ($table_name === 'users') {
    $columns = ['first_name', 'last_name', 'email', 'password', 'created_at', 'updated_at'];
    $db_arr = [];

    foreach ($columns as $column) {
        if ($column === 'created_at' || $column === 'updated_at') {
            $value = date('Y-m-d');
        } elseif ($column === 'password') {
            $value = password_hash($_POST[$column], PASSWORD_DEFAULT);
        } else {
            $value = $_POST[$column] ?? '';
        }
        $db_arr[$column] = $value;
    }

    $table_properties = implode(", ", array_keys($db_arr));
    $table_values = ":" . implode(", :", array_keys($db_arr));

    try {
        // Insert data
        $sql = "INSERT INTO $table_name ($table_properties) VALUES ($table_values)";
        $stmt = $conn->prepare($sql);
        $stmt->execute($db_arr);

        $_SESSION['message'] = "User berhasil ditambahkan!";
        $_SESSION['msg_type'] = "success";
    } catch (PDOException $e) {
        $_SESSION['message'] = "Error: " . $e->getMessage();
        $_SESSION['msg_type'] = "error";
    }

    header('location: ' . $_SESSION['redirect_to']);
    exit();
}

// Tangani data untuk tabel assets (tetap sama seperti sebelumnya)
if ($table_name === 'assets') {
    $columns = [ 'asset_name', 'asset_info_detail', 'asset_type', 'stock', 'img', 'created_by', 'created_at', 'updated_at'];
    $db_arr = [];

    foreach ($columns as $column) {
        if (in_array($column, ['created_at', 'updated_at'])) {
            $value = date('Y-m-d');
        } elseif ($column === 'created_by') {
            $value = $_SESSION['user']['id'];
        } elseif ($column === 'password') {
            $value = password_hash($_POST[$column], PASSWORD_DEFAULT);
        } elseif ($column === 'asset_type') {
            $value = $_POST[$column] ?? null;
        } elseif ($column === 'stock') {
            $value = (int) ($_POST[$column] ?? 1);
        } elseif ($column === 'img') {
            $target_dir = "uploads/products/";
            $file_data = $_FILES[$column] ?? null;

            if ($file_data && $file_data['tmp_name']) {
                $file_name = $file_data['name'];
                $check = getimagesize($file_data['tmp_name']);
                if ($check) {
                    if (move_uploaded_file($file_data['tmp_name'], $target_dir . $file_name)) {
                        $value = $file_name;
                    }
                }
            } else {
                $value = null;
            }
        } else {
            $value = $_POST[$column] ?? '';
        }

        $db_arr[$column] = $value;
    }

    $table_properties = implode(", ", array_keys($db_arr));
    $table_values = ":" . implode(", :", array_keys($db_arr));

    try {
        // Insert data
        $sql = "INSERT INTO $table_name ($table_properties) VALUES ($table_values)";
        $stmt = $conn->prepare($sql);
        $stmt->execute($db_arr);

        $_SESSION['message'] = "Asset berhasil ditambahkan!";
        $_SESSION['msg_type'] = "success";
    } catch (PDOException $e) {
        $_SESSION['message'] = "Error: " . $e->getMessage();
        $_SESSION['msg_type'] = "error";
    }

    header('location: ' . $_SESSION['redirect_to']);
    exit();
}
?>