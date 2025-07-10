<?php
session_start();
include('connection.php');

// Alihkan jika pengguna tidak terautentikasi
if (!isset($_SESSION['user'])) {
    header('location: login_pages.php');
    exit();
}

// Ambil nama tabel dari POST
$table_name = $_POST['table'] ?? '';


if ($table_name === 'users') {
    $columns = ['first_name', 'last_name', 'email', 'password', 'created_at', 'updated_at'];
    $db_arr = [];

    foreach ($columns as $column) {
        if ($column === 'created_at' || $column === 'updated_at') {
            $value = date('Y-m-d H:i:s');
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
        $sql = "INSERT INTO $table_name ($table_properties) VALUES ($table_values)";
        $stmt = $conn->prepare($sql);
        $stmt->execute($db_arr);
        $_SESSION['message'] = "User successfully added!";
        $_SESSION['msg_type'] = "success";
    } catch (PDOException $e) {
        $_SESSION['message'] = "Error: " . $e->getMessage();
        $_SESSION['msg_type'] = "error";
    }

    header('location: ' . $_SESSION['redirect_to']);
    exit();
}

// BLOK BARU DENGAN PENCATATAN RIWAYAT
if ($table_name === 'assets') {
    // --- Validasi Duplikat (tetap ada) ---
    $asset_name = $_POST['asset_name'] ?? '';
    $asset_location = $_POST['asset_location'] ?? '';

    $stmt_check = $conn->prepare("SELECT COUNT(*) FROM assets WHERE asset_name = ? AND asset_location = ?");
    $stmt_check->execute([$asset_name, $asset_location]);
    $count = $stmt_check->fetchColumn();

    if ($count > 0) {
        $_SESSION['message'] = "Asset with this name already exists in the selected location.";
        $_SESSION['msg_type'] = "error";
        header('location: ' . $_SESSION['redirect_to']);
        exit();
    }
    
    // --- Proses penyimpanan data ---
    $columns = ['asset_name', 'asset_location', 'asset_info_detail', 'asset_type', 'stock', 'img', 'created_by', 'created_at', 'updated_at'];
    $db_arr = [];

    foreach ($columns as $column) {
        $value = $_POST[$column] ?? '';
        if (in_array($column, ['created_at', 'updated_at'])) {
            $value = date('Y-m-d H:i:s');
        } elseif ($column === 'created_by') {
            $value = $_SESSION['user']['id'];
        } elseif ($column === 'img') {
            $value = null;
            if (isset($_FILES['img']) && $_FILES['img']['error'] === UPLOAD_ERR_OK) {
                $target_dir = "uploads/products/";
                $file_name = time() . '_' . basename($_FILES['img']['name']);
                if (move_uploaded_file($_FILES['img']['tmp_name'], $target_dir . $file_name)) {
                    $value = $file_name;
                }
            }
        }
        $db_arr[$column] = $value;
    }

    $table_properties = implode(", ", array_keys($db_arr));
    $table_values = ":" . implode(", :", array_keys($db_arr));
    
    // DITAMBAHKAN: Memulai Transaksi Database
    $conn->beginTransaction();

    try {
        // 1. Simpan data aset baru ke tabel 'assets'
        $sql = "INSERT INTO $table_name ($table_properties) VALUES ($table_values)";
        $stmt = $conn->prepare($sql);
        $stmt->execute($db_arr);

        // Ambil ID dari aset yang baru saja dimasukkan
        $new_asset_id = $conn->lastInsertId();
        $initial_stock = (int)($_POST['stock'] ?? 0);
        $user_id = $_SESSION['user']['id'];

        // 2. DITAMBAHKAN: Simpan data ke tabel 'activity_history'
        $history_stmt = $conn->prepare(
    "INSERT INTO activity_history (asset_id, history_asset_name, history_asset_location, user_id, change_type, quantity_change, stock_before, stock_after, notes) 
     VALUES (?, ?, ?, ?, 'initial_stock', ?, 0, ?, 'Initial stock on asset creation')"
);
$history_stmt->execute([
    $new_asset_id,
    $_POST['asset_name'],     // Data baru
    $_POST['asset_location'], // Data baru
    $user_id,
    $initial_stock,
    $initial_stock
]);

        // DITAMBAHKAN: Commit transaksi jika semua berhasil
        $conn->commit();

        $_SESSION['message'] = "Asset successfully added!";
        $_SESSION['msg_type'] = "success";

    } catch (PDOException $e) {
        // DITAMBAHKAN: Rollback (batalkan) jika ada error
        $conn->rollBack();
        $_SESSION['message'] = "Error: " . $e->getMessage();
        $_SESSION['msg_type'] = "error";
    }

    header('location: ' . $_SESSION['redirect_to']);
    exit();
}
?>