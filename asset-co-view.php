<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('location: login_pages.php');
    exit();
}

include('connection.php'); // Koneksi ke database

try {
    // Pagination logic
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1; // Halaman saat ini
    $limit = 4; // Jumlah data per halaman
    $offset = ($page - 1) * $limit;

    // Hitung total data
    $stmt_total = $conn->prepare("SELECT COUNT(*) as total FROM checkout");
    $stmt_total->execute();
    $total_data = $stmt_total->fetch(PDO::FETCH_ASSOC)['total'];

    $total_pages = ceil($total_data / $limit); // Total halaman

    // Ambil data sesuai limit dan offset
    $stmt = $conn->prepare("SELECT * FROM checkout ORDER BY checkout_at DESC LIMIT :limit OFFSET :offset");
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $checkout_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Terjadi kesalahan: " . $e->getMessage();
}

$grouped_data = []; // Menyimpan data yang dikelompokkan berdasarkan tanggal

// Kelompokkan data berdasarkan bulan dan tahun checkout
foreach ($checkout_data as $data) {
    $checkout_month = date('m - Y', strtotime($data['checkout_at'])); // Ambil bulan dan tahun
    $grouped_data[$checkout_month][] = $data; // Tambahkan data ke grup yang sesuai
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>View Checkout Assets - Inventory Management</title>
    <?php include('partials/app-header-scripts.php'); ?>
    
</head>

<body>

    <div id="dashboardMainContainer">
        <?php include('partials/app-sidebar.php'); ?>

        <div class="dashboard_content_container" id="dashboard_content_container">
            <?php include('partials/app-topnav.php'); ?>

            <div class="dashboard_content">
                <div class="addContainer">
                    <div class="userAddFormContainer" id="userAddFormContainer">
                        <h1 class="section_header"><i class="fa fa-eye"></i> View Checkout Assets</h1>

                        <!-- Tampilkan tabel untuk setiap grup tanggal -->
                        <?php foreach ($grouped_data as $checkout_month => $data_group): ?>
                            <div class="checkout_table_container">
                                <h4>Checkout Month: <?= htmlspecialchars($checkout_month) ?></h4>
                                <table class="checkout_table">
                                    <thead>
                                        <tr>
                                            <th>Asset Name</th>
                                            <th>Quantity Ordered</th>
                                            <th>Quantity Received</th>
                                            <th>Quantity Remaining</th>
                                            <th>Checked Out By</th>
                                            <th>Checkout Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($data_group as $data): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($data['asset_name']) ?></td>
                                                <td><?= htmlspecialchars($data['quantity_ordered']) ?></td>
                                                <td><?= htmlspecialchars($data['quantity_received']) ?></td>
                                                <td><?= htmlspecialchars($data['quantity_remaining']) ?></td>
                                                <td><?= htmlspecialchars($data['checkout_by']) ?></td>
                                                <td><?= htmlspecialchars($data['checkout_at']) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endforeach; ?>

                        <!-- Navigasi Pagination -->
                        <div class="pagination_controls">
    <?php if ($page > 1): ?>
        <a href="?page=<?= $page - 1 ?>" class="pagination_button">Previous</a>
    <?php endif; ?>

    <?php
    // Menampilkan halaman aktif saja
    echo '<a href="?page=' . $page . '" class="pagination_button active">' . $page . '</a>';
    ?>

    <?php if ($page < $total_pages): ?>
        <a href="?page=<?= $page + 1 ?>" class="pagination_button">Next</a>
    <?php endif; ?>
</div>


    <?php include('partials/app-scripts.php'); ?>
</body>

</html>
