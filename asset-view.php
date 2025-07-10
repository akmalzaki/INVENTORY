<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('location: login_pages.php');
    exit;
}
$user_role = $_SESSION['user']['role'] ?? '';

include('connection.php');

// --- DITAMBAHKAN: Mengambil data untuk opsi filter ---
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

// --- DIMODIFIKASI: Logika filter dan pagination ---
// --- LOGIKA FILTER, VALIDASI, DAN PAGINATION ---
try {
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = 5;
    $offset = ($page - 1) * $limit;

    $selectedAssets = $_GET['assets'] ?? [];
    $selectedLocations = $_GET['locations'] ?? [];

    // --- BLOK UNTUK MENGUMPULKAN PESAN PERINGATAN ---
    $warning_messages = [];
    if (!empty($selectedAssets) && !empty($selectedLocations)) {
        $missing_map = []; 
        foreach ($selectedAssets as $assetName) {
            foreach ($selectedLocations as $locationName) {
                $checkStmt = $conn->prepare("SELECT COUNT(*) FROM assets WHERE asset_name = ? AND asset_location = ?");
                $checkStmt->execute([$assetName, $locationName]);
                if ($checkStmt->fetchColumn() == 0) {
                    $missing_map[$assetName][] = "<b>" . htmlspecialchars($locationName) . "</b>";
                }
            }
        }
        foreach ($missing_map as $assetName => $missingLocations) {
            if (count($missingLocations) > 0) {
                $locations_string = implode(', ', $missingLocations);
                $warning_messages[] = " <b>" . htmlspecialchars($assetName) . "</b> not found at " . $locations_string . " station.";
            }
        }
    }
    if(!empty($warning_messages)) {
        $_SESSION['filter_warnings'] = $warning_messages;
    }

    // --- LOGIKA PENGAMBILAN DATA UTAMA ---
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

    $countQuery = "SELECT COUNT(*) as total FROM assets $whereSql";
    $stmt_total = $conn->prepare($countQuery);
    $stmt_total->execute($params);
    $total_data = $stmt_total->fetch(PDO::FETCH_ASSOC)['total'];
    $total_pages = ceil($total_data / $limit);

    $dataQuery = "SELECT * FROM assets $whereSql ORDER BY id ASC LIMIT ? OFFSET ?";
    $stmt_data = $conn->prepare($dataQuery);
    
    $dataParams = array_merge($params, [$limit, $offset]);
    $i = 1;
    foreach ($params as $param) { $stmt_data->bindValue($i++, $param, PDO::PARAM_STR); }
    $stmt_data->bindValue($i++, $limit, PDO::PARAM_INT);
    $stmt_data->bindValue($i++, $offset, PDO::PARAM_INT);
    $stmt_data->execute();
    $paged_assets = $stmt_data->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
    $paged_assets = [];
    $total_data = 0;
    $total_pages = 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>View Assets - Inventory Management</title>
    <link rel="stylesheet" type="text/css" href="css/login.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <?php include('partials/app-header-scripts.php'); ?>
</head>
<body>
    <div id="dashboardMainContainer">
        <?php include('partials/app-sidebar.php'); ?>
        <div class="dashboard_content_container" id="dashboard_content_container">
            <?php include('partials/app-topnav.php'); ?>
            <div class="dashboard_content">
                <div class="assetViewCont">
                    <div class="section_content">
                        <div class="users">
                            <table>
                                <thead>
                             <tr>
    <th colspan="12" class="table-header">
        <form action="asset-view.php" method="GET" class="filter-form">
            <div class="table-header-container">
                <h1 class="section_header"><i class="fa fa-list"></i> List of Assets</h1>
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
                                                    <a href="asset-view.php" class="clear-filter-button">Clear All</a>
                                                    <button type="submit" class="apply-filter-button">Apply Filter</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
            </div>
        </form>
    </th>
</tr>
                                    <tr>
                                        <th>NO</th>
                                        <th>Asset Name</th>
                                        <th>Image</th>
                                        <th>Location</th>
                                        <th>Asset Type</th>
                                        <th>Asset Info</th>
                                        <th>Stock</th>
                                        <th>Created By</th>
                                        <th>Created At</th>
                                        <th>Updated At</th>
                                        <?php if ($user_role == 'admin' || $user_role == 'staff') { ?>
                                            <th>Edit</th>
                                            <th>Delete</th>
                                        <?php } ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    
                                    $nomor = $offset + 1; 
                                
                                    foreach ($paged_assets as $asset) { 
                                    ?>
                                    <tr>
                                        <td><?= $nomor++ ?></td>
                                        <td><?= htmlspecialchars($asset['asset_name']) ?></td>
                                        <td><img class="productImg" src="uploads/products/<?= htmlspecialchars($asset['img']) ?>" alt=""></td>
                                        <td><?= htmlspecialchars($asset['asset_location']) ?></td>
                                        <td><?= htmlspecialchars($asset['asset_type']) ?></td>
                                        <td><?= htmlspecialchars($asset['asset_info_detail']) ?></td>
                                        <td class="stock"><?= htmlspecialchars($asset['stock']) ?></td>
                                        <td>
                                            <?php
                                            $stmt_user = $conn->prepare("SELECT first_name, last_name FROM users WHERE id = ?");
                                            $stmt_user->execute([$asset['created_by']]);
                                            $creator = $stmt_user->fetch(PDO::FETCH_ASSOC);
                                            echo $creator ? htmlspecialchars($creator['first_name'] . ' ' . $creator['last_name']) : 'Unknown';
                                            ?>
                                        </td>
                                        <td><?= date('M d, Y', strtotime($asset['created_at'])) ?></td>
                                        <td><?= date('M d, Y', strtotime($asset['updated_at'])) ?></td>
                                        
                                        <?php if ($user_role == 'admin' || $user_role == 'staff') { ?>
                                        <td>
                                            <button type="button" class="edit-button" onclick="openEditModal(<?= htmlspecialchars(json_encode($asset), ENT_QUOTES, 'UTF-8') ?>)">
                                                <i class="fa fa-edit"></i> Edit
                                            </button>
                                        </td>
                                        <td>
                                            <form action="asset-delete.php" method="POST" onsubmit="return confirm('Are you sure to delete <?= htmlspecialchars($asset['asset_name']) ?>?');">
                                                <input type="hidden" name="asset_id" value="<?= $asset['id'] ?>">
                                                <button type="submit" class="delete-button"><i class="fa fa-trash"></i> Delete</button>
                                            </form>
                                        </td>
                                        <?php } ?>
                                    </tr>
                                    <?php } ?>
                                </tbody>
                            </table>

                            <div class="pagination_controls">
    <?php
    // Membuat link pagination yang mengingat filter
    $queryParams = $_GET;
    unset($queryParams['page']); // Hapus parameter 'page' agar tidak duplikat
    $queryString = http_build_query($queryParams);

    if ($page > 1) {
        echo '<a href="?page='.($page - 1).'&'.$queryString.'" class="pagination_button">Previous</a>';
    }
    
    if ($total_pages > 0) {
        echo '<a href="?page=' . $page . '&' . $queryString . '" class="pagination_button active">' . $page . '</a>';
    }

    if ($page < $total_pages) {
        echo '<a href="?page='.($page + 1).'&'.$queryString.'" class="pagination_button">Next</a>';
    }
    ?>
</div>                  
                            <p class="userCount"><?= $total_data ?> assets</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="editAssetModal" class="modal-overlay">
        <div class="modal-content appForm">
            <span id="closeModalButton" class="close-button">&times;</span>
            <h1>Edit Asset</h1>
            <form id="editAssetForm" action="asset-edit.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="asset_id" id="editAssetId">
                <div>
                    <label for="editAssetName">Asset Name</label>
                    <input type="text" id="editAssetName" name="asset_name" required class="appFormInput" />
                </div>
                <div>
                    <label for="editAssetLocation">Asset Location</label>
                    <select id="editAssetLocation" name="asset_location" required class="appFormInput">
                        
                        <option value="Halim">Halim</option>
                        <option value="Karawang">Karawang</option>
                        <option value="Padalarang">Padalarang</option>
                        <option value="Tegalluar">Tegalluar</option>
                    </select>
                </div>
                <div>
                    <label for="editAssetType">Asset Type</label>
                    <select id="editAssetType" name="asset_type" required class="appFormInput">
                        
                        <option value="fast moving">Fast Moving</option>
                        <option value="slow moving">Slow Moving</option>
                    </select>
                </div>
                <div>
                    <label for="quantity_add">Quantity to Add</label>
                    <input type="number" class="appFormInput" id="quantity_add" name="quantity_add" value="0" />
                </div>
                <div>
                    <label for="quantity_rmv">Remove Quantity</label>
                    <input type="number" class="appFormInput" id="quantity_rmv" name="quantity_rmv" value="0" />
                </div>
                <div>
                    <label for="editAssetInfo">Asset Info</label>
                    <textarea id="editAssetInfo" name="assetInfo" class="appFormInput"></textarea>
                </div>
                <div>
                    <label for="editAssetImage">Current Image</label>
                    <p><img id="editAssetImage" src="" width="100"></p>
                    <input type="file" id="img" name="img" class="appFormInput" />
                </div>
                <div class="button-container">
                    <button type="submit" name="update_asset"><i class="fa fa-save"></i> Save Changes</button>
                </div>
            </form>
        </div>
    </div>

<script>
    // --- KELOLA DROPDOWN FILTER ---
    document.getElementById('filterBtn').addEventListener('click', function (event) {
        event.stopPropagation();
        document.getElementById('assetFilterDropdown').classList.toggle('show');
    });
    
    // --- KELOLA MODAL EDIT & MENUTUP DROPDOWN ---
    const editModal = document.getElementById('editAssetModal');
    const closeModalButton = document.getElementById('closeModalButton');
    
    window.addEventListener('click', function(event) {
        // Menutup dropdown jika klik di luar
        const dropdown = document.getElementById('assetFilterDropdown');
        if (dropdown && dropdown.classList.contains('show') && !event.target.closest('.dropdown-filter')) {
            dropdown.classList.remove('show');
        }

        // Menutup modal jika klik di luar
        if (event.target == editModal) {
            editModal.style.display = "none";
        }
    });

    if(closeModalButton) {
        closeModalButton.onclick = function() {
            if(editModal) editModal.style.display = 'none';
        }
    }

    // Fungsi untuk membuka modal (biarkan seperti semula)
    function openEditModal(asset) {
        document.getElementById("editAssetId").value = asset.id;
        document.getElementById("editAssetName").value = asset.asset_name;
        document.getElementById("editAssetLocation").value = asset.asset_location;
        document.getElementById("editAssetType").value = asset.asset_type;
        document.getElementById("editAssetInfo").value = asset.asset_info_detail;
        document.getElementById("editAssetImage").src = "uploads/products/" + asset.img;
        if(editModal) editModal.style.display = "flex";
    }
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
<script>
    // Pastikan SweetAlert2 sudah dimuat di <head>
    const warningPopupData = <?= json_encode($warning_popup_message) ?>;
    if (warningPopupData) {
        Swal.fire({
            title: 'Filter Information',
            html: warningPopupData.message,
            icon: warningPopupData.type,
            confirmButtonText: 'OK'
        });
    }
</script>

<?php include('partials/app-scripts.php'); ?>
</body>
</html>