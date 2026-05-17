<?php
// ================================================================
// index.php — Front Controller (Router)
// Online Medicine Shop | Group 8
// ================================================================
session_start();

require 'config.php';
require 'models.php';
require 'controllers.php';

$page = $_GET['page'] ?? 'home';

/* ---------- Logout ---------- */
if ($page === 'logout') {
    logoutCtrl();
    exit;
}

/* ---------- AJAX Endpoint ---------- */
if ($page === 'ajax') {
    ajaxCtrl($conn);
    exit;
}

/* ---------- Redirect if already logged in ---------- */
if (in_array($page, ['login', 'register']) && isLoggedIn()) {
    if (isAdmin()) redirectTo('index.php?page=admin');
    else           redirectTo('index.php?page=home');
}

/* ---------- Dispatch ---------- */
switch ($page) {

    /* Public pages */
    case 'home':             homeCtrl($conn);           break;
    case 'medicine_detail':  medicineDetailCtrl($conn); break;
    case 'login':            loginCtrl($conn);          break;
    case 'register':         registerCtrl($conn);       break;

    /* Shared (logged-in) */
    case 'profile':          profileCtrl($conn);        break;
    case 'order_detail':     orderDetailCtrl($conn);    break;

    /* Customer pages */
    case 'cart':             cartCtrl($conn);           break;
    case 'checkout':         checkoutCtrl($conn);       break;
    case 'order_success':    orderSuccessCtrl($conn);   break;
    case 'my_orders':        myOrdersCtrl($conn);       break;

    /* Admin pages */
    case 'admin':
    case 'admin_dashboard':  adminDashCtrl($conn);      break;
    case 'admin_medicines':  adminMedicinesCtrl($conn); break;
    case 'admin_categories': adminCategoriesCtrl($conn);break;
    case 'admin_customers':  adminCustomersCtrl($conn); break;
    case 'admin_orders':     adminOrdersCtrl($conn);    break;

    default:
        redirectTo('index.php?page=home');
}

mysqli_close($conn);
?>
