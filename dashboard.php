<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('location: login_pages.php');
    exit;
}
$user = $_SESSION['user'];

include('connection.php');

// --- PENGAMBILAN DATA UNTUK OPSI FILTER ---
try {
    $stmt_assets_opt = $conn->prepare("SELECT DISTINCT asset_name FROM assets ORDER BY asset_name ASC");
    $stmt_assets_opt->execute();
    $allAssets = $stmt_assets_opt->fetchAll(PDO::FETCH_COLUMN);

    $stmt_locations_opt = $conn->prepare("SELECT DISTINCT asset_location FROM assets ORDER BY asset_location ASC");
    $stmt_locations_opt->execute();
    $allLocations = $stmt_locations_opt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    die("Error fetching filter options: " . $e->getMessage());
}

// Inisialisasi variabel
$chart_data = [];
$total_data = 0;
$total_pages = 0;

// --- LOGIKA FILTER DAN PAGINATION DI SISI SERVER ---
try {
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = 8; // atau 5, sesuaikan dengan halaman Anda
    $offset = ($page - 1) * $limit;

    $selectedAssets = $_GET['assets'] ?? [];
    $selectedLocations = $_GET['locations'] ?? [];

    // --- BLOK VALIDASI BARU DENGAN PENGELOMPOKAN PESAN ---
    $warning_messages = [];
    if (!empty($selectedAssets) && !empty($selectedLocations)) {
        $missing_map = []; // Peta untuk menyimpan lokasi yg hilang per aset
        
        foreach ($selectedAssets as $assetName) {
            foreach ($selectedLocations as $locationName) {
                $checkStmt = $conn->prepare("SELECT COUNT(*) FROM assets WHERE asset_name = ? AND asset_location = ?");
                $checkStmt->execute([$assetName, $locationName]);
                if ($checkStmt->fetchColumn() == 0) {
                    // Kumpulkan lokasi yang tidak ditemukan untuk setiap aset
                    $missing_map[$assetName][] = "<b>" . htmlspecialchars($locationName) . "</b>";
                }
            }
        }
        
        // Buat kalimat notifikasi dari peta yang sudah dikumpulkan
        foreach ($missing_map as $assetName => $missingLocations) {
            if (count($missingLocations) > 0) {
                $locations_string = implode(', ', $missingLocations);
                $warning_messages[] = "<b>" . htmlspecialchars($assetName) . "</b> not found at " . $locations_string . " station.";
            }
        }
    }
    
    // Simpan pesan ke session untuk ditampilkan oleh JavaScript
    if(!empty($warning_messages)) {
        $_SESSION['filter_warnings'] = $warning_messages;
    }
    // --- AKHIR BLOK VALIDASI ---


    // Logika untuk mengambil data utama tetap sama
    $whereClauses = [];
    $params = [];

    if (!empty($selectedAssets)) {
        $placeholders = implode(',', array_fill(0, count($selectedAssets), '?'));
        $whereClauses[] = "asset_name IN ($placeholders)";
        $params = array_merge($params, $selectedAssets);
    }
    if (!empty($selectedLocations)) {
        $placeholders = implode(',', array_fill(0, count($selectedLocations), '?'));
        $whereClauses[] = "asset_location IN ($placeholders)";
        $params = array_merge($params, $selectedLocations);
    }

    $whereSql = '';
    if (!empty($whereClauses)) {
        $whereSql = 'WHERE ' . implode(' AND ', $whereClauses);
    }
    
    //SHOW DASHBOARD AKMAL
    $baseQuery = "SELECT asset_name, asset_location, SUM(stock) as stock FROM assets $whereSql GROUP BY asset_name, asset_location ORDER BY MIN(id) ASC";
    
    $stmt_total = $conn->prepare($baseQuery);
    $stmt_total->execute($params);
    $total_data = $stmt_total->rowCount();
    $total_pages = ceil($total_data / $limit);
    
    $dataQuery = $baseQuery . " LIMIT ? OFFSET ?";
    $stmt_data = $conn->prepare($dataQuery);
    $dataParams = array_merge($params, [$limit, $offset]);
    $i = 1;
    foreach ($params as $param) { $stmt_data->bindValue($i++, $param, PDO::PARAM_STR); }
    $stmt_data->bindValue($i++, $limit, PDO::PARAM_INT);
    $stmt_data->bindValue($i++, $offset, PDO::PARAM_INT);
    $stmt_data->execute();
    
    // Nama variabel hasil query disesuaikan dengan halaman
    // Jika ini dashboard.php, gunakan $chart_data
    $chart_data = $stmt_data->fetchAll(PDO::FETCH_ASSOC);
    // Jika ini asset-view.php, gunakan $paged_assets
    $paged_assets = $chart_data; 


} 
catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
    $chart_data = [];
    $total_data = 0;
    $total_pages = 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Inventory Management</title>
    <link rel="stylesheet" type="text/css" href="css/login.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> 
    <script src="https://use.fontawesome.com/0c7a3095b5.js"></script>
    <?php include('partials/app-header-scripts.php'); ?>
    
    </head>
<body>
    <div id="dashboardMainContainer">
        <?php include('partials/app-sidebar.php') ?>
        <div class="dashboard_content_container">
            <?php include('partials/app-topnav.php') ?>
            <div class="dashboard_content">
                <div class="dashboard_content_main">
                    <div class="chart-container">
                        <div class="chart-box">
                            <form action="dashboard.php" method="GET" class="filter-form">
                                <div class="table-header-container">
                                    <h1 class="section_header">Assets Stock</h1>
                                    <div class="filter-controls">
                                        <div class="dropdown-filter">
                                            <button type="button" id="filterBtn" class="filter-button"><i class="fa fa-filter"></i> Filter &#9662;</button>
                                            <div id="assetFilterDropdown" class="dropdown-content">
                                                <div class="filter-columns-container">
                                                    <div class="filter-column">
                                                        <h4 class="filter-header">Assets</h4>
                                                        <?php foreach ($allAssets as $assetName): ?>
                                                            <label><input type="checkbox" name="assets[]" value="<?= htmlspecialchars($assetName) ?>" <?= in_array($assetName, $selectedAssets) ? 'checked' : '' ?>> <?= htmlspecialchars($assetName) ?></label>
                                                        <?php endforeach; ?>
                                                    </div>
                                                    <div class="filter-column">
                                                        <h4 class="filter-header">Location</h4>
                                                        <?php foreach ($allLocations as $location): ?>
                                                            <label><input type="checkbox" name="locations[]" value="<?= htmlspecialchars($location) ?>" <?= in_array($location, $selectedLocations) ? 'checked' : '' ?>> <?= htmlspecialchars($location) ?></label>
                                                        <?php endforeach; ?>
                                                    </div>
                                                </div>
                                                <div class="filter-actions">
                                                    <a href="dashboard.php" class="clear-filter-button">Clear All</a>
                                                    <button type="submit" class="apply-filter-button">Apply Filter</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                            <canvas id="myChart"></canvas>
                            <div class="pagination_controls" id="paginationControls">
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
    </div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // --- BAGIAN 1: RENDER CHART ATAU PESAN "TIDAK DITEMUKAN" ---
    const chartCanvas = document.getElementById('myChart');
    const paginationControls = document.getElementById('paginationControls');
    const chartData = <?= json_encode($chart_data) ?>;

    if (chartCanvas) { // Pastikan canvas ada
        const chartBox = chartCanvas.parentElement;
        if (chartData.length === 0 && (<?= !empty($selectedAssets) || !empty($selectedLocations) ? 'true' : 'false' ?>)) {
            chartCanvas.style.display = 'none';
            if (paginationControls) paginationControls.style.display = 'none';

            const noDataMessage = document.createElement('p');
            noDataMessage.innerHTML = 'Data tidak ditemukan untuk filter yang dipilih.';
            noDataMessage.className = 'no-data-message';
            if (chartBox) chartBox.insertBefore(noDataMessage, paginationControls);

        } else if (chartData.length > 0) {
            const labels = chartData.map(item => [item.asset_name, `(${item.asset_location})`]);
            const values = chartData.map(item => item.stock);
            const adjustedBackgroundColors = labels.map((_, i) => i % 2 === 0 ? 'rgba(205, 188, 185)' : 'rgba(102, 0, 0)');
            
            const ctx = chartCanvas.getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: { labels: labels, datasets: [{ label: 'Stock', data: values, backgroundColor: adjustedBackgroundColors, borderWidth: 1, borderRadius: 20 }] },
                options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true }, x: { grid: { display: false } } }, plugins: { legend: { display: false }, tooltip: { enabled: true, backgroundColor: 'rgba(0,0,0,0.7)', bodyFont: { size: 14 } } } }
            });
        }
    }
    
    // --- BAGIAN 2: KELOLA DROPDOWN FILTER ---
    const filterBtn = document.getElementById('filterBtn');
    const dropdown = document.getElementById('assetFilterDropdown');

    if (filterBtn && dropdown) {
        filterBtn.addEventListener('click', function (event) {
            event.stopPropagation();
            dropdown.classList.toggle('show');
        });
    }

    // --- BAGIAN 3: KELOLA NOTIFIKASI WARNING (SweetAlert2) ---
    <?php
        $warning_popup_message = null;
        if (isset($_SESSION['filter_warnings'])) {
            $warning_html = '<ul style="text-align: left; margin: 0; padding-left: 20px;">';
            foreach($_SESSION['filter_warnings'] as $msg) {
                $warning_html .= '<li>' . $msg . '</li>';
            }
            $warning_html .= '</ul>';
            
            $warning_popup_message = ['message' => $warning_html, 'type' => 'warning'];
            unset($_SESSION['filter_warnings']); // Hapus session di sini
        }
    ?>
    const warningPopupData = <?= json_encode($warning_popup_message) ?>;
    if (warningPopupData) {
        Swal.fire({
            title: 'Filter Information',
            html: warningPopupData.message,
            icon: warningPopupData.type,
            confirmButtonText: 'OK'
        });
    }
});

// --- LISTENER GLOBAL TUNGGAL UNTUK MENUTUP DROPDOWN ---
window.addEventListener('click', function(event) {
    const dropdown = document.getElementById('assetFilterDropdown');
    if (dropdown && dropdown.classList.contains('show') && !event.target.closest('.dropdown-filter')) {
        dropdown.classList.remove('show');
    }
});
</script>


<?php
    $warning_popup_message = null;
    if (isset($_SESSION['filter_warnings'])) {
        // Gabungkan semua pesan warning menjadi satu blok HTML
        $warning_html = '<ul style="text-align: left; margin: 0; padding-left: 20px;">';
        foreach($_SESSION['filter_warnings'] as $msg) {
            $warning_html .= '<li>' . $msg . '</li>';
        }
        $warning_html .= '</ul>';
        
        $warning_popup_message = [
            'message' => $warning_html,
            'type' => 'warning'
        ];
        unset($_SESSION['filter_warnings']);
    }
?>

<?php include('partials/app-scripts.php'); ?>
</body>
</html>