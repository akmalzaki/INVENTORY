<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('location: login_pages.php');
    exit();
}

include('connection.php');
date_default_timezone_set('Asia/Jakarta');

try {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Ambil data dari form
        $asset_ids = $_POST['asset_id'];
        $asset_names = $_POST['asset_name'];
        $quantities = $_POST['quantity'];
        $asset_locations = $_POST['asset_location']; // DITAMBAHKAN: Mengambil data lokasi

        // Data tambahan
        $checkout_by = $_SESSION['user']['first_name'] . ' ' . $_SESSION['user']['last_name'];
        $checkout_at = date('Y-m-d H:i:s');

        // Persiapkan query SQL (dengan kolom asset_location)
        $stmt = $conn->prepare("
            INSERT INTO checkout (asset_name, asset_location, quantity_ordered, quantity_remaining, checkout_by, checkout_at) 
            VALUES (:asset_name, :asset_location,  :quantity_ordered, :quantity_remaining, :checkout_by, :checkout_at)
        ");

        $conn->beginTransaction();

        for ($i = 0; $i < count($asset_ids); $i++) {
            $asset_id = $asset_ids[$i];
            $asset_name = $asset_names[$i];
            $quantity_ordered = $quantities[$i];
            
            $asset_location = $asset_locations[$i]; // DITAMBAHKAN: Ambil lokasi untuk baris saat ini

            // Ambil stok terkini
            $stock_stmt = $conn->prepare("SELECT stock FROM assets WHERE id = :id");
            $stock_stmt->execute([':id' => $asset_id]);
            $current_stock = $stock_stmt->fetchColumn();

            // Cek stok
            if ($quantity_ordered > $current_stock) {
                // Batalkan semua transaksi jika satu saja gagal
                $conn->rollBack();
                $_SESSION['message'] = "Checkout failed for asset '$asset_name'. Not enough stock.";
                $_SESSION['msg_type'] = "error";
                header('location: asset-checkout.php');
                exit();
            }

            $quantity_remaining = $current_stock - $quantity_ordered;

            // Simpan data checkout (dengan data lokasi)
            $stmt->execute([
                ':asset_name' => $asset_name,
                ':asset_location' => $asset_location, // DITAMBAHKAN: Bind data lokasi
                
                ':quantity_ordered' => $quantity_ordered,
                ':quantity_remaining' => $quantity_remaining,
                ':checkout_by' => $checkout_by,
                ':checkout_at' => $checkout_at
            ]);

            // Update stok di assets
            $update_stmt = $conn->prepare("UPDATE assets SET stock = stock - :quantity WHERE id = :id");
            $update_stmt->execute([
                ':quantity' => $quantity_ordered,
                ':id' => $asset_id
            ]);
        }

        // Commit transaksi jika semua berhasil
        $conn->commit();

        $_SESSION['message'] = "Assets checked out successfully!";
        $_SESSION['msg_type'] = "success";
        
        header('location: asset-checkout.php');
        exit();
    } else {
        header('location: asset-checkout.php');
        exit();
    }
} catch (PDOException $e) {
    // Rollback jika terjadi error SQL
    $conn->rollBack();
    $_SESSION['message'] = "An error occurred: " . $e->getMessage();
    $_SESSION['msg_type'] = "error";
    header('location: asset-checkout.php');
    exit();
}
?>