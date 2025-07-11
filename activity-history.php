<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('location: login_pages.php');
    exit();
}
$user_role = $_SESSION['user']['role'] ?? '';
include('connection.php');


try {
    // mengambil data lokasi dari tabel assets
    $stmt_locations = $conn->prepare("SELECT DISTINCT asset_location FROM assets ORDER BY asset_location ASC");
    $stmt_locations->execute();
    $allLocations = $stmt_locations->fetchAll(PDO::FETCH_COLUMN);

    // mengambil riwayat berdasarkan tahun di tabel activity history
    $stmt_years = $conn->prepare("SELECT DISTINCT YEAR(created_at) as history_year FROM activity_history ORDER BY history_year DESC");
    $stmt_years->execute();
    $allYears = $stmt_years->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    die("Error fetching filter options: " . $e->getMessage());
}

//untuk filter dan pagination
try {
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = 10; 
    $offset = ($page - 1) * $limit;

    $selected_location = $_GET['location'] ?? '';
    $selected_month = $_GET['month'] ?? '';
    $selected_year = $_GET['year'] ?? '';

    $whereClauses = [];
    $params = [];

    // kolom filtering
    if (!empty($selected_location)) {
        // filter langsung ke kolom history_asset_location
        $whereClauses[] = "sh.history_asset_location = ?";
        $params[] = $selected_location;
    }
    if (!empty($selected_month)) {
        $whereClauses[] = "MONTH(sh.created_at) = ?";
        $params[] = $selected_month;
    }
    if (!empty($selected_year)) {
        $whereClauses[] = "YEAR(sh.created_at) = ?";
        $params[] = $selected_year;
    }

    $whereSql = '';
    if (!empty($whereClauses)) {
        $whereSql = 'WHERE ' . implode(' AND ', $whereClauses);
    }
    
    $baseQuery = "FROM activity_history sh 
                  LEFT JOIN users u ON sh.user_id = u.id
                  $whereSql";

    $countQuery = "SELECT COUNT(*) as total " . $baseQuery;
    $stmt_total = $conn->prepare($countQuery);
    $stmt_total->execute($params);
    $total_data = $stmt_total->fetch(PDO::FETCH_ASSOC)['total'];
    $total_pages = ceil($total_data / $limit);

    // menggunakan COALESCE agar nama aset tetap tampil jika aset induk dihapus
    $dataQuery = "SELECT 
                    sh.*, 
                    sh.history_asset_name as asset_name, 
                    sh.history_asset_location as asset_location, 
                    u.first_name, 
                    u.last_name 
                  $baseQuery 
                  ORDER BY sh.created_at DESC 
                  LIMIT ? OFFSET ?";
    
    $stmt_data = $conn->prepare($dataQuery);
    
    $dataParams = array_merge($params, [$limit, $offset]);
    $i = 1;
    foreach ($params as $param) { $stmt_data->bindValue($i++, $param); }
    $stmt_data->bindValue($i++, $limit, PDO::PARAM_INT);
    $stmt_data->bindValue($i++, $offset, PDO::PARAM_INT);
    
    $stmt_data->execute();
    $history_data = $stmt_data->fetchAll(PDO::FETCH_ASSOC);

    $grouped_data = [];
    foreach ($history_data as $data) {
        $history_month_year = date('F Y', strtotime($data['created_at']));
        $grouped_data[$history_month_year][] = $data;
    }

} catch (PDOException $e) {
    // page berdasarkan filtering
    $history_data = [];
    $grouped_data = [];
    $total_data = 0;
    $total_pages = 0;

    // Baru tampilkan pesan error dan hentikan skrip setelahnya
    die("Terjadi kesalahan: " . $e->getMessage());
}

$months = [1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Activity History - Inventory Management</title>
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
                        <h1 class="section_header"><i class="fa fa-history"></i> Activity History</h1>

                        <form action="activity-history.php" method="GET" class="filter-container">
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
                                <a href="activity-history.php" class="clearActivity">Clear Filter</a>
                            </div>
                        </form>

                        <?php if (empty($grouped_data)): ?>
                            <p class="activityNotFound">Data not found for the selected filter.</p>
                        <?php else: ?>
                            <?php foreach ($grouped_data as $month_year => $data_group): ?>
                                <div class="checkout_table_container">
                                   
                                    <table class="checkout_table">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Asset Name</th>
                                                <th>Location</th>
                                                <th>Action</th>
                                                <th>Qty</th>
                                                <th>Before</th>
                                                <th>After</th>
                                                <th>By</th>
                                                <th>Notes</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($data_group as $data): ?>
                                                <tr>
                                                    <td><?= date('d M Y, H:i', strtotime($data['created_at'])) ?></td>
                                                    <td><?= htmlspecialchars($data['asset_name'] ?? 'Asset Deleted') ?></td>
                                                    <td><?= htmlspecialchars($data['asset_location'] ?? '-') ?></td>
                                                    <td><?= htmlspecialchars($data['change_type']) ?></td>
                                                    <td><?= htmlspecialchars($data['quantity_change']) ?></td>
                                                    <td><?= htmlspecialchars($data['stock_before']) ?></td>
                                                    <td><?= htmlspecialchars($data['stock_after']) ?></td>
                                                    <td><?= htmlspecialchars(isset($data['first_name']) ? $data['first_name'] . ' ' . $data['last_name'] : 'User Deleted') ?></td>
                                                    <td><?= htmlspecialchars($data['notes']) ?></td>
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