<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('location: login_pages.php');
    exit();
}

include('connection.php'); // Include koneksi ke database
date_default_timezone_set('Asia/Jakarta'); // Zona waktu lokal

try {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Ambil data dari form
        $asset_ids = $_POST['asset_id'];
        $asset_names = $_POST['asset_name'];
        $quantities = $_POST['quantity'];

        // Data tambahan
        $checkout_by = $_SESSION['user']['first_name'] . ' ' . $_SESSION['user']['last_name'];
        $checkout_at = date('Y-m-d H:i:s');

        // Persiapkan query SQL
        $stmt = $conn->prepare("
            INSERT INTO checkout (asset_name, quantity_received, quantity_ordered, quantity_remaining, checkout_by, checkout_at) 
            VALUES (:asset_name, :quantity_received, :quantity_ordered, :quantity_remaining, :checkout_by, :checkout_at)
        ");

        $conn->beginTransaction();

        for ($i = 0; $i < count($asset_ids); $i++) {
            $asset_id = $asset_ids[$i];
            $asset_name = $asset_names[$i];
            $quantity_ordered = $quantities[$i];
            $quantity_received = $quantity_ordered;

            // Ambil stok terkini
            $stock_stmt = $conn->prepare("SELECT stock FROM assets WHERE id = :id");
            $stock_stmt->execute([':id' => $asset_id]);
            $current_stock = $stock_stmt->fetchColumn();

            // Cek stok
            if ($current_stock <= 0) {
                $_SESSION['message'] = "Checkout gagal untuk asset $asset_name karena stok habis.";
                $_SESSION['msg_type'] = "error";
                continue;
            }

            if ($quantity_ordered > $current_stock) {
                $_SESSION['message'] = "Checkout gagal untuk asset $asset_name karena stok tidak mencukupi.";
                $_SESSION['msg_type'] = "error";
                continue;
            }

            $quantity_remaining = $current_stock - $quantity_ordered;

            // Simpan data checkout
            $stmt->execute([
                ':asset_name' => $asset_name,
                ':quantity_received' => $quantity_received,
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

        // Commit transaksi
        $conn->commit();

        // Tutup koneksi
        $conn = null;

        // Redirect setelah berhasil
        header('location: asset-checkout.php?status=success');
        exit();
    } else {
        header('location: asset-checkout.php');
        exit();
    }
} catch (PDOException $e) {
    // Rollback jika error
    $conn->rollBack();
    echo "Terjadi kesalahan: " . $e->getMessage();
}
?>