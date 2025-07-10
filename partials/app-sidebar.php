<?php
// Periksa apakah sesi sudah dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Periksa apakah user sudah login dan sesi memiliki key 'user'
$user = $_SESSION['user'] ?? null;

// Periksa apakah role diset, jika tidak, beri nilai default 'guest'
$role = $user['role'] ?? 'guest';
?>

<div class="dashboard_sidebar" id="dashboard_sidebar">
    <h3 class="dashboard_logo" id="dashboard_logo">IMS</h3>
    <div class="dashboard_sidebar_user">
        <img src="assets/user-regular.svg" alt="user image" id="userImage">

        <span class="dashboard_name"><?= $user['first_name'] . ' ' . $user['last_name'] ?></span>
    </div>
    <div class="dashboard_sidebar_menus">
        <ul class="dashboard_menu_lists">

            <!-- Dashboard -->
            <li class="liMainMenu">
                <a href="./dashboard.php"> <i class="fa fa-dashboard"></i> <span class="menuText"> Dashboard </span>
                </a>
            </li>

            <!-- Report -->
            <?php if ($role === 'admin' || $role === 'staff'): ?>
                <li class="liMainMenu">
                    <a href="./report.php"> <i class="fa fa-file-text"></i> <span class="menuText"> Report </span> </a>
                </li>
            <?php endif; ?>

            <!-- Asset Management -->
            <li class="liMainMenu">
                <a href="javascript:void(0);" class="showHideSubMenu">
                    <i class="fa fa-truck showHideSubMenu"></i>
                    <span class="menuText showHideSubMenu">Asset</span>
                    <i class="fa fa-angle-left iconArrow showHideSubMenu"></i>
                </a>
                <ul class="subMenus">
                    <?php if ($role === 'admin' || $role === 'staff'): ?>
                        <li class="subMenuContainer">
                            <a class="subMenuLink" href="./asset-add.php"><i class="fa fa-circle-o"></i>Add Asset</a>
                        </li>
                    <?php endif; ?>

                    <li class="subMenuContainer">
                        <a class="subMenuLink" href="./asset-view.php"><i class="fa fa-circle-o"></i>View Assets</a>
                    </li>

                    <?php if ($role === 'admin' || $role === 'staff'): ?>
                        <li class="subMenuContainer">
                            <a class="subMenuLink" href="./asset-checkout.php"><i class="fa fa-circle-o"></i>Checkout
                                Asset</a>
                        </li>
                        <?php endif; ?>
                        <li class="subMenuContainer">
                            <a class="subMenuLink" href="./asset-co-view.php"><i class="fa fa-circle-o"></i>View Checkout
                                Asset</a>
                        </li>
                        <li class="subMenuContainer">
                            <a class="subMenuLink" href="./activity-history.php"><i class="fa fa-circle-o"></i>Activity History</a>
                        </li>
                    
                </ul>
            </li>

            <!-- User Management -->
            <?php if ($role === 'admin'): ?>
                <li class="liMainMenu showHideSubMenu">
                    <a href="javascript:void(0);" class="showHideSubMenu">
                        <i class="fa fa-user-plus showHideSubMenu"></i>
                        <span class="menuText showHideSubMenu">User Management</span>
                        <i class="fa fa-angle-left iconArrow showHideSubMenu"></i>
                    </a>
                    <ul class="subMenus">
                        <li class="subMenuContainer">
                            <a class="subMenuLink" href="./view-users.php"><i class="fa fa-circle-o"></i>View Users</a>
                        </li>
                        <li class="subMenuContainer">
                            <a class="subMenuLink" href="./users-add.php"><i class="fa fa-circle-o"></i>Add User</a>
                        </li>
                    </ul>
                </li>
            <?php endif; ?>

        </ul>
    </div>
</div>