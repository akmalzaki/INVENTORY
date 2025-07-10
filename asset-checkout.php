<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('location: login_pages.php');
    exit;
}
$_SESSION['table'] = 'assets';

// Ganti include dengan query langsung untuk mengambil data yang dibutuhkan
include('connection.php');
try {
    $stmt = $conn->prepare("SELECT id, asset_name, asset_location FROM assets ORDER BY id ASC");
    $stmt->execute();
    $assets = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching assets: " . $e->getMessage());
}

// Konversi ke JSON untuk digunakan dalam JavaScript
$asset_options_json = json_encode($assets);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Checkout Assets - Inventory Management</title>
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
                        <h1 class="section_header"><i class="fa fa-cart-plus"></i> Checkout</h1>

                      

                        <div class="button-order">
                            <button type="button" id="addNewBtn"><i class="fa fa-plus"></i> Add New</button>
                        </div>
                        <form action="asset-save-co.php" method="post">
                            <div class="checkoutList" id="checkoutList">
                                <div class="column column-12">
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label for="asset_name_1">Asset Name</label>
                                          <select id="asset_name_1" name="asset_id[]" required class="appFormInput" onchange="updateAssetName(1)">
    <option value="">Select Asset</option>
    <?php foreach ($assets as $asset) { ?>
        <option 
            value="<?= $asset['id'] ?>" 
            data-location="<?= htmlspecialchars($asset['asset_location']) ?>">
            <?= htmlspecialchars($asset['asset_name']) ?> (<?= htmlspecialchars($asset['asset_location']) ?>)
        </option>
    <?php } ?>
</select>
                                            <!-- Input tersembunyi untuk nama aset -->
                                            
                                            <input type="hidden" name="asset_name[]" id="hidden_asset_name_1">
<input type="hidden" name="asset_location[]" id="hidden_asset_location_1">
                                        </div>
                                        <div class="form-group">
                                            <label for="quantity_1">Quantity</label>
                                            <input type="number" class="appFormInput" id="quantity_1" name="quantity[]"
                                                value="1" min="1" />
                                        </div>

                                        <div class="form-group">
                                            <div class="button-delete">
                                                <button type="button" class="fa fa-trash"></button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="button-checkout">
                                <button type="submit"><i class="fa fa-cart-plus"></i> Checkout</button>
                            </div>
                        </form>
                          <!-- Menampilkan pesan error jika ada -->
                        <?php if (isset($_SESSION['message'])): ?>
                            <div class="alert <?= $_SESSION['msg_type'] ?>">
                                <?= $_SESSION['message']; ?>
                                <?php unset($_SESSION['message']); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    // Data aset sekarang berisi id, asset_name, dan asset_location
    var assetOptions = <?= $asset_options_json ?>;

   function updateAssetName(row) {
    var selectElement = document.getElementById("asset_name_" + row);
    var selectedIndex = selectElement.selectedIndex;
    var selectedOption = selectElement.options[selectedIndex];

    // Ambil nama aset (sudah ada)
    var assetNameOnly = selectedOption.text.split(' (')[0];
    document.getElementById("hidden_asset_name_" + row).value = assetNameOnly;

    // TAMBAHKAN INI: Ambil dan set lokasi
    var selectedLocation = selectedOption.dataset.location;
    document.getElementById("hidden_asset_location_" + row).value = selectedLocation;
}

    let clickCount = 0;
    const addNewBtn = document.getElementById('addNewBtn');

    addNewBtn.addEventListener('click', function () {
        if (clickCount >= 4) {
            addNewBtn.disabled = true;
            alert('Hanya dapat melakukan 5 checkout secara bersamaan.');
            return;
        }
        clickCount++;
        var rowCount = document.querySelectorAll('.checkoutList .form-row').length + 1;
        var newRow = document.createElement('div');
        newRow.className = 'form-row';

        // --- INI BAGIAN YANG DIUBAH ---
        // Membuat string options dengan format "Nama Aset (Lokasi)"
        let optionsHtml = assetOptions.map(asset => 
            `<option value="${asset.id}">${asset.asset_name} (${asset.asset_location})</option>`
        ).join('');

        newRow.innerHTML = `
    <div class="form-group">
        <label for="asset_name_${rowCount}">Asset Name</label>
        <select id="asset_name_${rowCount}" name="asset_id[]" required class="appFormInput" onchange="updateAssetName(${rowCount})">
            <option value="">Select Asset</option>
            ${assetOptions.map(asset => `<option value="${asset.id}" data-location="${asset.asset_location}">${asset.asset_name} (${asset.asset_location})</option>`).join('')}
        </select>
        <input type="hidden" name="asset_name[]" id="hidden_asset_name_${rowCount}">
        <input type="hidden" name="asset_location[]" id="hidden_asset_location_${rowCount}">
    </div>
    <div class="form-group">
        <label for="quantity_${rowCount}">Quantity</label>
        <input type="number" class="appFormInput" id="quantity_${rowCount}" name="quantity[]" value="1" min="1" />
    </div>
    <div class="form-group">
        <div class="button-delete">
            <button type="button" class="fa fa-trash"></button>
        </div>
    </div>`;
        
        document.getElementById('checkoutList').appendChild(newRow);
    });

    document.getElementById('checkoutList').addEventListener('click', function (event) {
        if (event.target.classList.contains('fa-trash')) {
            var row = event.target.closest('.form-row');
            if (row) {
                row.remove();
                clickCount--; // Kurangi hitungan saat baris dihapus
                if(clickCount < 5) addNewBtn.disabled = false;
            }
        }
    });
</script>



    <?php include('partials/app-scripts.php'); ?>
</body>

</html>