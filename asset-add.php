<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('location: login_pages.php');
    exit;
}

$_SESSION['table'] = 'assets';
$_SESSION['redirect_to'] = 'asset-add.php';
$user = $_SESSION['user'];
$users = include('show-users.php');
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Add Assets - Inventory Management</title>
    <?php include('partials/app-header-scripts.php'); ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <div id="dashboardMainContainer">
        <?php include('partials/app-sidebar.php'); ?>

        <div class="dashboard_content_container" id="dashboard_content_container">
            <?php include('partials/app-topnav.php'); ?>

            <div class="dashboard_content">
                <div class="addContainer">
                    <div class="userAddFormContainer" id="userAddFormContainer">
                        <h1 class="section_header"><i class="fa fa-plus"></i> Add Asset</h1>

                        <form action="add.php" method="POST" class="appForm" enctype="multipart/form-data">
                            
                            <div>
                                <label for="asset_name">Asset Name</label>
                                <input type="text" id="asset_name" name="asset_name" required class="appFormInput" />
                            </div>
                            <div>
                                <label for="asset_location">Asset Location</label>
                                <select id="asset_location" name="asset_location" required class="appFormInput">
                                    <option value="Halim">Halim</option>
                                    <option value="Karawang">Karawang</option>
                                    <option value="Padalarang">Padalarang</option>
                                    <option value="Tegalluar">Tegalluar</option>
                                </select>
                            </div>
                            <div>
                                <label for="asset_info_detail">Asset Information</label>
                                <textarea id="asset_info_detail" name="asset_info_detail"
                                    class="appFormInput"></textarea>
                            </div>
                            <div>
                                <label for="asset_type">Asset Type</label>
                                <select id="asset_type" name="asset_type" required class="appFormInput">
                                    <option value="fast moving">Fast Moving</option>
                                    <option value="slow moving">Slow Moving</option>
                                </select>
                            </div>
                            <div>
                                <label for="stock">Quantity</label>
                                <input type="number" id="stock" name="stock" value="1" min="1" required
                                    class="appFormInput" />
                            </div>
                            
                            <div>
                                <label for="img">Product Image</label>
                                <input type="file" id="img" name="img" required class="appFormInput" />
                            </div>
                            <input type="hidden" name="table" value="assets" />
                            <div class="button-container">
                                <button type="submit"><i class="fa fa-plus"></i> Add Asset</button>
                            </div>
                  
                        </form>
                        
                    </div>
                </div>

            </div>
        </div>
    </div>

    <?php include('partials/app-scripts.php'); ?>
    <?php
    // Ambil pesan dari session jika ada
    $popup_message = null;
    if (isset($_SESSION['message'])) {
        // Siapkan data untuk JavaScript
        $popup_message = [
            'message' => $_SESSION['message'],
            'type' => $_SESSION['msg_type'] // 'success' atau 'error'
        ];

        // PENTING: HAPUS pesan dari session setelah diambil
        // Ini menyelesaikan masalah harus refresh
        unset($_SESSION['message']);
        unset($_SESSION['msg_type']);
    }
    ?>
</body>

</html>