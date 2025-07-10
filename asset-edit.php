<?php
session_start();
include('connection.php');

// Alihkan jika tidak login
if (!isset($_SESSION['user'])) {
    header('location: login_pages.php');
    exit();
}

try {
    // Ambil ID aset dari POST atau GET
    $asset_id = $_POST['asset_id'] ?? $_GET['asset_id'] ?? null;

    if (!$asset_id) {
        // Redirect jika tidak ada ID aset
        header("location: asset-view.php");
        exit;
    }

    // Tangani form submission untuk update aset
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_asset'])) {
        
        // Memulai Transaksi Database
        $conn->beginTransaction();

        try {
            // Ambil data dari form
            $asset_name = $_POST['asset_name'];
            $asset_location = $_POST['asset_location'];
            $asset_type = $_POST['asset_type'];
            $asset_info_detail = $_POST['assetInfo'];
            $quantity_to_add = isset($_POST['quantity_add']) ? (int)$_POST['quantity_add'] : 0;
            $quantity_to_remove = isset($_POST['quantity_rmv']) ? (int)$_POST['quantity_rmv'] : 0;

            // Ambil stok saat ini dari database untuk validasi
            $stmt_stock = $conn->prepare("SELECT stock, img FROM assets WHERE id = ?");
            $stmt_stock->execute([$asset_id]);
            $current_asset_data = $stmt_stock->fetch(PDO::FETCH_ASSOC);
            $stock_before = (int)$current_asset_data['stock'];

            // Hitung perubahan stok
            $quantity_change = $quantity_to_add - $quantity_to_remove;
            $new_stock = $stock_before + $quantity_change;

            if ($new_stock < 0) {
                throw new Exception("Stock cannot be negative!");
            }

            // Logika Upload Gambar
            $img_name = $current_asset_data['img']; // Default ke gambar yang ada
            if (isset($_FILES['img']) && $_FILES['img']['error'] === UPLOAD_ERR_OK) {
                $target_dir = "uploads/products/";
                $img_name = time() . '_' . basename($_FILES['img']['name']);
                if (!move_uploaded_file($_FILES['img']['tmp_name'], $target_dir . $img_name)) {
                    throw new Exception("Failed to upload new image.");
                }
            }

            // 1. UPDATE data aset di tabel 'assets'
            $query = "UPDATE assets 
                      SET asset_name = ?, asset_location = ?, asset_type = ?, 
                          asset_info_detail = ?, stock = ?, img = ?, updated_at = NOW() 
                      WHERE id = ?";
            $stmt_update = $conn->prepare($query);
            $stmt_update->execute([
                $asset_name, $asset_location, $asset_type, $asset_info_detail, 
                $new_stock, $img_name, $asset_id
            ]);

            // 2. Simpan ke riwayat HANYA JIKA ada perubahan stok
            if ($quantity_change != 0) {
                $change_type = $quantity_change > 0 ? 'add stock' : 'remove stock';
                $notes = 'Stock changed via edit form';

                // Di dalam file asset-edit.php
$history_stmt = $conn->prepare(
    "INSERT INTO activity_history (asset_id, history_asset_name, history_asset_location, user_id, change_type, quantity_change, stock_before, stock_after, notes) 
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
);
$history_stmt->execute([
    $asset_id,
    $asset_name,              // Data baru dari form
    $asset_location,          // Data baru dari form
    $_SESSION['user']['id'],
    $change_type,
    $quantity_change,
    $stock_before,
    $new_stock,
    'Stock changed via edit form'
]);
            }
            
            // 3. Commit transaksi jika semua berhasil
            $conn->commit();

            $_SESSION['message'] = "Asset updated successfully!";
            $_SESSION['msg_type'] = "success";
            header('location: asset-view.php');
            exit;

        } catch (Exception $e) {
            // Rollback (batalkan) semua query jika ada error
            $conn->rollBack();
            $_SESSION['message'] = "An error occurred: " . $e->getMessage();
            $_SESSION['msg_type'] = "error";
            header("location: asset-view.php");
            exit;
        }
    }

} catch (PDOException $e) {
    $_SESSION['message'] = "Database error: " . $e->getMessage();
    $_SESSION['msg_type'] = "error";
    header("location: asset-view.php");
    exit;
}
?>