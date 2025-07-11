<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('location: login_pages.php');
    exit();
}

include('connection.php');

// --- PENGAMBILAN DATA UNTUK OPSI FILTER ---
try {
    // Ambil daftar lokasi/stasiun unik dari tabel checkout
    $stmt_locations = $conn->prepare("SELECT DISTINCT asset_location FROM checkout WHERE asset_location IS NOT NULL AND asset_location != '' ORDER BY asset_location ASC");
    $stmt_locations->execute();
    $allLocations = $stmt_locations->fetchAll(PDO::FETCH_COLUMN);

    // Ambil daftar tahun unik dari data checkout
    $stmt_years = $conn->prepare("SELECT DISTINCT YEAR(checkout_at) as checkout_year FROM checkout ORDER BY checkout_year DESC");
    $stmt_years->execute();
    $allYears = $stmt_years->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    die("Error fetching filter options: " . $e->getMessage());
}

// --- LOGIKA FILTER DAN PAGINATION ---
try {
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = 4; // Jumlah data per halaman
    $offset = ($page - 1) * $limit;

    // Baca parameter filter dari URL
    $selected_location = $_GET['location'] ?? '';
    $selected_month = $_GET['month'] ?? '';
    $selected_year = $_GET['year'] ?? '';

    $whereClauses = [];
    $params = [];

    // Bangun klausa WHERE dinamis
    if (!empty($selected_location)) {
        $whereClauses[] = "asset_location = ?";
        $params[] = $selected_location;
    }
    if (!empty($selected_month)) {
        $whereClauses[] = "MONTH(checkout_at) = ?";
        $params[] = $selected_month;
    }
    if (!empty($selected_year)) {
        $whereClauses[] = "YEAR(checkout_at) = ?";
        $params[] = $selected_year;
    }

    $whereSql = '';
    if (!empty($whereClauses)) {
        $whereSql = 'WHERE ' . implode(' AND ', $whereClauses);
    }

    // Hitung total data DENGAN filter
    $countQuery = "SELECT COUNT(*) as total FROM checkout $whereSql";
    $stmt_total = $conn->prepare($countQuery);
    $stmt_total->execute($params);
    $total_data = $stmt_total->fetch(PDO::FETCH_ASSOC)['total'];
    $total_pages = ceil($total_data / $limit);

    // Ambil data DENGAN filter dan pagination
    $dataQuery = "SELECT * FROM checkout $whereSql ORDER BY checkout_at DESC LIMIT ? OFFSET ?";
    $stmt_data = $conn->prepare($dataQuery);
    
    $dataParams = array_merge($params, [$limit, $offset]);
    $i = 1;
    foreach ($params as $param) { $stmt_data->bindValue($i++, $param); }
    $stmt_data->bindValue($i++, $limit, PDO::PARAM_INT);
    $stmt_data->bindValue($i++, $offset, PDO::PARAM_INT);
    
    $stmt_data->execute();
    $checkout_data = $stmt_data->fetchAll(PDO::FETCH_ASSOC);

    // Kelompokkan data untuk ditampilkan
    $grouped_data = [];
    foreach ($checkout_data as $data) {
        $checkout_month_year = date('F Y', strtotime($data['checkout_at']));
        $grouped_data[$checkout_month_year][] = $data;
    }

} catch (PDOException $e) {
    echo "Terjadi kesalahan: " . $e->getMessage();
    $checkout_data = [];
    $grouped_data = [];
    $total_data = 0;
    $total_pages = 0;
}

$months = [1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'];
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
                        <h1 class="section_header"><i class="fa fa-eye"></i> View Checkout Asset </h1>

                        <form action="asset-co-view.php" method="GET" class="filter-container">
                            <div class="filter-group">
                                <label for="location">Station</label>
                                <select name="location" id="location">
                                    <option value="">All</option>
                                    <?php foreach($allLocations as $location): ?>
                                        <option value="<?= htmlspecialchars($location) ?>" <?= ($selected_location == $location) ? 'selected' : '' ?>><?= htmlspecialchars($location) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="filter-group">
                                <label for="month">Month</label>
                                <select name="month" id="month">
                                    <option value="">All</option>
                                    <?php foreach($months as $num => $name): ?>
                                        <option value="<?= $num ?>" <?= ($selected_month == $num) ? 'selected' : '' ?>><?= $name ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="filter-group">
                                <label for="year">Year</label>
                                <select name="year" id="year">
                                    <option value="">All</option>
                                    <?php foreach($allYears as $year): ?>
                                        <option value="<?= $year ?>" <?= ($selected_year == $year) ? 'selected' : '' ?>><?= $year ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="filter-group">
                                <label>&nbsp;</label>
                                <button type="submit">Apply Filter</button>
                            </div>
                            <div class="filter-group">
                                <label>&nbsp;</label>
                                <a href="asset-co-view.php" class="clearActivity">Clear Filter</a>
                            </div>
                        </form>

                        <?php if (empty($grouped_data)): ?>
                            <p style="text-align: center; margin-top: 20px; font-style: italic; color: #888;">Data not found for the selected filter.</p>
                        <?php else: ?>
                            <?php foreach ($grouped_data as $month_year => $data_group): ?>
                                <div class="checkout_table_container">
                                    <h4>Checkout for: <?= htmlspecialchars($month_year) ?></h4>
                                    <table class="checkout_table">
                                        <thead>
                                            <tr>
                                                <th>Asset Name</th>
                                                <th>Location</th>
                                                <th>Qty Checked Out</th>
                                                <th>Qty Remaining</th>
                                                <th>Checked Out By</th>
                                                <th>Checkout Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($data_group as $data): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($data['asset_name']) ?></td>
                                                    <td><?= htmlspecialchars($data['asset_location']) ?></td>
                                                    <td><?= htmlspecialchars($data['quantity_ordered']) ?></td>
                                                    <td><?= htmlspecialchars($data['quantity_remaining']) ?></td>
                                                    <td><?= htmlspecialchars($data['checkout_by']) ?></td>
                                                    <td><?= date('d M Y, H:i', strtotime($data['checkout_at'])) ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        
                        <div class="pagination_controls">
                            <?php
                            $queryParams = $_GET;
                            unset($queryParams['page']);
                            $queryString = http_build_query($queryParams);
                            if ($page > 1) { echo '<a href="?page='.($page - 1).'&'.$queryString.'" class="pagination_button">Previous</a>'; }
                            if ($total_pages > 0) { echo '<a href="?page=' . $page . '&' . $queryString . '" class="pagination_button active">' . $page . '</a>'; }
                            if ($page < $total_pages) { echo '<a href="?page='.($page + 1).'&'.$queryString.'" class="pagination_button">Next</a>'; }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
      <?php include('partials/app-scripts.php'); ?>
</body>
</html>