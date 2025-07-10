<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('location: login_pages.php');
    exit();
}

include('connection.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $asset_id = $_POST['asset_id'];
    $user_id = $_SESSION['user']['id'];

    // 1. Memulai Transaksi Database
    $conn->beginTransaction();

    try {
        // 2. Ambil data aset yang akan dihapus SEBELUM benar-benar dihapus
        $stmt_get = $conn->prepare("SELECT * FROM assets WHERE id = ?");
        $stmt_get->execute([$asset_id]);
        $asset_to_delete = $stmt_get->fetch(PDO::FETCH_ASSOC);

        if ($asset_to_delete) {
            // Data yang akan dicatat
            $stock_before = $asset_to_delete['stock'];
            $asset_name_deleted = $asset_to_delete['asset_name'];

            // 3. Catat aktivitas penghapusan ke dalam tabel riwayat
           // Di dalam file asset-delete.php
$history_stmt = $conn->prepare(
    "INSERT INTO activity_history (asset_id, history_asset_name, history_asset_location, user_id, change_type, quantity_change, stock_before, stock_after, notes) 
     VALUES (?, ?, ?, ?, 'asset_deleted', ?, ?, 0, ?)"
);
$history_stmt->execute([
    $asset_id,
    $asset_to_delete['asset_name'],     // Data baru dari aset yang akan dihapus
    $asset_to_delete['asset_location'], // Data baru dari aset yang akan dihapus
    $user_id,
    -$stock_before,
    $stock_before,
    "Asset '" . $asset_to_delete['asset_name'] . "' was permanently deleted."
]);

            // 4. Hapus aset dari tabel utama
            $stmt_delete = $conn->prepare("DELETE FROM assets WHERE id = ?");
            $stmt_delete->execute([$asset_id]);

            // 5. Commit transaksi jika semua query berhasil
            $conn->commit();

            $_SESSION['message'] = "Asset successfully deleted.";
            $_SESSION['msg_type'] = "success";

        } else {
            // Jika aset tidak ditemukan, batalkan transaksi
            $conn->rollBack();
            $_SESSION['message'] = "Failed to delete: Asset not found.";
            $_SESSION['msg_type'] = "error";
        }

    } catch (PDOException $e) {
        // 6. Rollback (batalkan semua) jika terjadi error
        $conn->rollBack();
        $_SESSION['message'] = "An error occurred: " . $e->getMessage();
        $_SESSION['msg_type'] = "error";
    }

    // Redirect kembali ke halaman view asset
    header("Location: asset-view.php");
    exit();
}
?>