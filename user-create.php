<?php
session_start();
include('connection.php'); // Pastikan file ini berisi koneksi ke database

// Redirect jika user tidak login
if (!isset($_SESSION['user'])) {
    header('location: login_pages.php');
    exit();
}

// Ambil nama tabel dari POST
$table_name = $_POST['table'] ?? '';

// Tangani data untuk tabel users
if ($table_name === 'users') {
    // Tambahkan 'role' ke dalam kolom yang akan disimpan
    $columns = ['first_name', 'last_name', 'email', 'password', 'role', 'created_at'];
    $db_arr = [];

    foreach ($columns as $column) {
        if ($column === 'created_at') {
            $value = date('Y-m-d');
        } elseif ($column === 'password') {
            $value = ($_POST[$column]);
        } elseif ($column === 'role') {
            $value = $_POST[$column] ?? ''; // Ambil role yang dipilih dari form
        } else {
            $value = $_POST[$column] ?? '';
        }
        $db_arr[$column] = $value;
    }

    // Siapkan query untuk insert
    $table_properties = implode(", ", array_keys($db_arr));
    $table_values = ":" . implode(", :", array_keys($db_arr));

    try {
        // Insert data ke tabel users
        $sql = "INSERT INTO $table_name ($table_properties) VALUES ($table_values)";
        $stmt = $conn->prepare($sql);
        $stmt->execute($db_arr);

        $_SESSION['message'] = "User berhasil ditambahkan!";
        $_SESSION['msg_type'] = "success";
    } catch (PDOException $e) {
        $_SESSION['message'] = "Error: " . $e->getMessage();
        $_SESSION['msg_type'] = "error";
    }

    // Redirect ke halaman yang sesuai setelah insert
    header('location: ' . $_SESSION['redirect_to']);
    exit();
}
