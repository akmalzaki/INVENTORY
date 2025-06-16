<?php
session_start();
if (!isset($_SESSION['user']))
    header('location: login_pages.php');
$_SESSION['table'] = 'assets';
$assets = include('asset-show.php'); // Ambil data aset

// Ambil hanya id dan asset_name
$asset_options = array_map(function ($asset) {
    return ['id' => $asset['id'], 'asset_name' => $asset['asset_name']];
}, $assets);

// Konversi ke JSON untuk digunakan dalam JavaScript
$asset_options_json = json_encode($asset_options);
include('connection.php');
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

                        <!-- Menampilkan pesan error jika ada -->
                        <?php if (isset($_SESSION['message'])): ?>
                            <div class="alert <?= $_SESSION['msg_type'] ?>">
                                <?= $_SESSION['message']; ?>
                                <?php unset($_SESSION['message']); ?>
                            </div>
                        <?php endif; ?>

                        <div class="button-order">
                            <button type="button" id="addNewBtn"><i class="fa fa-plus"></i> Add New</button>
                        </div>
                        <form action="asset-save-co.php" method="post">
                            <div class="checkoutList" id="checkoutList">
                                <div class="column column-12">
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label for="asset_name_1">Asset Name</label>
                                            <select id="asset_name_1" name="asset_id[]" required class="appFormInput"
                                                onchange="updateAssetName(1)">
                                                <option value="">Select Asset</option>
                                                <?php foreach ($asset_options as $asset) { ?>
                                                    <option value="<?= $asset['id'] ?>"><?= $asset['asset_name'] ?></option>
                                                <?php } ?>
                                            </select>
                                            <!-- Input tersembunyi untuk nama aset -->
                                            <input type="hidden" name="asset_name[]" id="hidden_asset_name_1">
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
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    var assetOptions = <?= $asset_options_json ?>; // JSON data asset

    // Fungsi untuk memperbarui nama asset di row
    function updateAssetName(row) {
        var selectElement = document.getElementById("asset_name_" + row);
        var selectedIndex = selectElement.selectedIndex;
        var selectedOption = selectElement.options[selectedIndex];

        document.getElementById("hidden_asset_name_" + row).value = selectedOption.text;
    }

    // Menyimpan jumlah klik
    let clickCount = 0;
    const addNewBtn = document.getElementById('addNewBtn');

    // Fungsi untuk menangani klik tombol "Add New"
    addNewBtn.addEventListener('click', function () {
        // Jika sudah mencapai  klik, tampilkan alert dan nonaktifkan tombol
        if (clickCount >= 4) {
            // Nonaktifkan tombol setelah 5 kali klik
            addNewBtn.disabled = true;

            // Menampilkan alert dan menghentikan penambahan item
            alert('Hanya dapat melakukan 5 checkout secara bersamaan.');
            return; // Menghentikan proses penambahan item
        }

        // Increment jumlah klik
        clickCount++;

        // Proses untuk menambahkan item jika belum mencapai batas klik
        var rowCount = document.querySelectorAll('.checkoutList .form-row').length + 1;

        var newRow = document.createElement('div');
        newRow.className = 'form-row';
        newRow.innerHTML =
            `<div class="form-group">
                <label for="asset_name_${rowCount}">Asset Name</label>
                <select id="asset_name_${rowCount}" name="asset_id[]" required class="appFormInput" onchange="updateAssetName(${rowCount})">
                    <option value="">Select Asset</option>
                    ${assetOptions.map(asset => `<option value="${asset.id}">${asset.asset_name}</option>`).join('')}
                </select>
                <input type="hidden" name="asset_name[]" id="hidden_asset_name_${rowCount}">
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

        // Menambahkan baris baru ke dalam daftar
        document.getElementById('checkoutList').appendChild(newRow);
    });

    // Fungsi untuk menghapus asset row
    document.getElementById('checkoutList').addEventListener('click', function (event) {
        if (event.target.classList.contains('button-delete') || event.target.closest('.button-delete')) {
            var row = event.target.closest('.form-row');
            row.remove();
        }
    });
</script>


    <?php include('partials/app-scripts.php'); ?>
</body>

</html>