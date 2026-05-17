<?php
// ================================================================
// controllers.php — Request handling + role-based logic
// Online Medicine Shop | Group 8 | No OOP
// ================================================================


// ================================================================
// HELPER
// ================================================================
function h($s) { return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }

function redirectTo($url) {
    header('Location: ' . $url);
    exit;
}

function isAdmin()    { return isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin'; }
function isCustomer() { return isset($_SESSION['user']) && $_SESSION['user']['role'] === 'customer'; }
function isLoggedIn() { return isset($_SESSION['user']); }

function handleImageUpload($fieldName, $uploadDir = 'uploads/medicines/') {
    if (empty($_FILES[$fieldName]['name'])) return null;
    $file = $_FILES[$fieldName];
    $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($file['type'], $allowed)) return null;
    if ($file['size'] > 2 * 1024 * 1024) return null; // 2MB max
    $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid('med_', true) . '.' . $ext;
    $dest     = $uploadDir . $filename;
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
    if (move_uploaded_file($file['tmp_name'], $dest)) return $dest;
    return null;
}


// ================================================================
// AUTH CONTROLLERS
// ================================================================

function loginCtrl($conn) {
    $error = '';
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($email === '' || $password === '') {
            $error = 'Please fill in all fields.';
        } else {
            $user = getUserByEmail($conn, $email);
            if ($user && password_verify($password, $user['password_hash'])) {
                $_SESSION['user'] = [
                    'id'    => $user['id'],
                    'name'  => $user['name'],
                    'email' => $user['email'],
                    'role'  => $user['role'],
                ];
                if ($user['role'] === 'admin') redirectTo('index.php?page=admin');
                else                           redirectTo('index.php?page=home');
            } else {
                $error = 'Invalid email or password.';
            }
        }
    }
    require 'views/login.php';
}

function registerCtrl($conn) {
    $error   = '';
    $success = '';
    $old     = ['name' => '', 'email' => '', 'phone' => '', 'address' => ''];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name     = trim($_POST['name']     ?? '');
        $email    = trim($_POST['email']    ?? '');
        $phone    = trim($_POST['phone']    ?? '');
        $address  = trim($_POST['address']  ?? '');
        $password = $_POST['password']      ?? '';
        $confirm  = $_POST['confirm']       ?? '';
        $old      = compact('name', 'email', 'phone', 'address');

        if ($name === '' || $email === '' || $password === '') {
            $error = 'Name, email and password are required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Invalid email address.';
        } elseif (strlen($password) < 6) {
            $error = 'Password must be at least 6 characters.';
        } elseif ($password !== $confirm) {
            $error = 'Passwords do not match.';
        } elseif (getUserByEmail($conn, $email)) {
            $error = 'Email is already registered.';
        } else {
            if (registerCustomer($conn, $name, $email, $password, $phone, $address)) {
                $success = 'Account created! You can now log in.';
                $old     = ['name' => '', 'email' => '', 'phone' => '', 'address' => ''];
            } else {
                $error = 'Registration failed. Please try again.';
            }
        }
    }
    require 'views/register.php';
}

function logoutCtrl() {
    $_SESSION = [];
    session_destroy();
    redirectTo('index.php?page=login');
}

function profileCtrl($conn) {
    if (!isLoggedIn()) redirectTo('index.php?page=login');

    $user  = getUserById($conn, $_SESSION['user']['id']);
    $error = $success = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name    = trim($_POST['name']    ?? '');
        $phone   = trim($_POST['phone']   ?? '');
        $address = trim($_POST['address'] ?? '');

        if ($name === '') {
            $error = 'Name is required.';
        } else {
            $picPath = handleImageUpload('profile_picture', 'uploads/profiles/');
            if (updateUserProfile($conn, $user['id'], $name, $phone, $address, $picPath)) {
                $_SESSION['user']['name'] = $name;
                $user = getUserById($conn, $user['id']); // refresh
                $success = 'Profile updated successfully.';
            } else {
                $error = 'Update failed.';
            }
        }
    }
    require 'views/profile.php';
}


// ================================================================
// HOME / BROWSE (Public)
// ================================================================

function homeCtrl($conn) {
    $search      = trim($_GET['q']      ?? '');
    $vendorFlt   = trim($_GET['vendor'] ?? '');
    $typeFlt     = trim($_GET['type']   ?? '');
    $medicines   = searchMedicines($conn, $search, $vendorFlt, $typeFlt);
    $categories  = getAllCategories($conn);

    // Distinct vendor list for filter dropdown
    $vr = mysqli_query($conn, "SELECT DISTINCT vendor_name FROM medicines ORDER BY vendor_name ASC");
    $vendors = mysqli_fetch_all($vr, MYSQLI_ASSOC);

    require 'views/home.php';
}

function medicineDetailCtrl($conn) {
    $id  = intval($_GET['id'] ?? 0);
    $med = getMedicineById($conn, $id);
    if (!$med) redirectTo('index.php?page=home');
    require 'views/medicine_detail.php';
}


// ================================================================
// CART CONTROLLERS (Customer)
// ================================================================

function cartCtrl($conn) {
    if (!isCustomer()) redirectTo('index.php?page=login');
    $items = getCartItems($conn, $_SESSION['user']['id']);
    $total = array_sum(array_map(function($i){ return $i['price'] * $i['quantity']; }, $items));
    require 'views/cart.php';
}

function checkoutCtrl($conn) {
    if (!isCustomer()) redirectTo('index.php?page=login');

    $userId = $_SESSION['user']['id'];
    $items  = getCartItems($conn, $userId);
    if (empty($items)) redirectTo('index.php?page=cart');

    $total = array_sum(array_map(function($i){ return $i['price'] * $i['quantity']; }, $items));
    $user  = getUserById($conn, $userId);
    $error = '';
    $step  = $_GET['step'] ?? 'address'; // address | invoice | payment

    if ($step === 'address' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $addr = trim($_POST['shipping_address'] ?? '');
        if ($addr === '') {
            $error = 'Shipping address is required.';
        } else {
            $_SESSION['checkout_address'] = $addr;
            redirectTo('index.php?page=checkout&step=invoice');
        }
    }

    if ($step === 'invoice' && !isset($_SESSION['checkout_address'])) {
        redirectTo('index.php?page=checkout&step=address');
    }

    if ($step === 'payment' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $addr   = $_SESSION['checkout_address'] ?? '';
        $method = trim($_POST['payment_method'] ?? '');

        if ($addr === '') redirectTo('index.php?page=checkout&step=address');
        if ($method === '') { $error = 'Please select a payment method.'; $step = 'payment'; }
        else {
            // Validate stock
            foreach ($items as $item) {
                if ($item['quantity'] > $item['availability']) {
                    $error = "'{$item['name']}' has only {$item['availability']} units in stock.";
                    $step  = 'address';
                    require 'views/checkout.php';
                    return;
                }
            }
            // Create order
            $orderId = createOrder($conn, $userId, $total, $addr, $method);
            if ($orderId) {
                foreach ($items as $item) {
                    addOrderItem($conn, $orderId, $item['medicine_id'], $item['quantity'], $item['price']);
                    reduceStock($conn, $item['medicine_id'], $item['quantity']);
                }
                createPayment($conn, $orderId, $total, $method);
                clearCart($conn, $userId);
                unset($_SESSION['checkout_address']);
                redirectTo('index.php?page=order_success&order_id=' . $orderId);
            } else {
                $error = 'Order placement failed. Please try again.';
            }
        }
    }

    require 'views/checkout.php';
}

function orderSuccessCtrl($conn) {
    if (!isCustomer()) redirectTo('index.php?page=login');
    $orderId = intval($_GET['order_id'] ?? 0);
    $order   = getOrderById($conn, $orderId);
    if (!$order || $order['user_id'] != $_SESSION['user']['id']) redirectTo('index.php?page=home');
    $orderItems = getOrderItems($conn, $orderId);
    require 'views/order_success.php';
}

function myOrdersCtrl($conn) {
    if (!isCustomer()) redirectTo('index.php?page=login');
    $orders = getOrdersByUser($conn, $_SESSION['user']['id']);
    require 'views/my_orders.php';
}

function orderDetailCtrl($conn) {
    if (!isLoggedIn()) redirectTo('index.php?page=login');
    $orderId = intval($_GET['id'] ?? 0);
    $order   = getOrderById($conn, $orderId);

    // Customer can only see own orders; admin can see any
    if (!$order) redirectTo('index.php?page=home');
    if (isCustomer() && $order['user_id'] != $_SESSION['user']['id']) redirectTo('index.php?page=my_orders');

    $orderItems = getOrderItems($conn, $orderId);
    $customer   = getUserById($conn, $order['user_id']);
    require 'views/order_detail.php';
}


// ================================================================
// AJAX ENDPOINTS
// ================================================================

function ajaxCtrl($conn) {
    header('Content-Type: application/json');
    if (!isLoggedIn()) { http_response_code(403); echo json_encode(['error' => 'Unauthorized']); exit; }

    $type = $_GET['type'] ?? '';

    // ---------- ADMIN AJAX ----------
    if ($type === 'medicines' && isAdmin()) {
        $q = trim($_GET['q'] ?? '');
        echo json_encode($q === '' ? getAllMedicines($conn) : searchMedicines($conn, $q));
        exit;
    }
    if ($type === 'customers' && isAdmin()) {
        $q = trim($_GET['q'] ?? '');
        echo json_encode($q === '' ? getAllCustomers($conn) : searchCustomers($conn, $q));
        exit;
    }
    if ($type === 'orders' && isAdmin()) {
        $q = trim($_GET['q'] ?? '');
        echo json_encode($q === '' ? getAllOrders($conn) : searchOrders($conn, $q));
        exit;
    }

    // ---------- CUSTOMER AJAX ----------
    if ($type === 'search_medicines') {
        $q      = trim($_GET['q']      ?? '');
        $vendor = trim($_GET['vendor'] ?? '');
        $ftype  = trim($_GET['ftype']  ?? '');
        echo json_encode(searchMedicines($conn, $q, $vendor, $ftype));
        exit;
    }

    if ($type === 'add_to_cart' && isCustomer() && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        $medId = intval($data['medicine_id'] ?? 0);
        $qty   = intval($data['quantity']    ?? 1);
        $med   = getMedicineById($conn, $medId);
        if (!$med)         { echo json_encode(['error' => 'Medicine not found']);        exit; }
        if ($qty < 1)      { echo json_encode(['error' => 'Invalid quantity']);          exit; }
        if ($qty > $med['availability']) {
            echo json_encode(['error' => 'Quantity exceeds stock (' . $med['availability'] . ')']); exit;
        }
        addToCart($conn, $_SESSION['user']['id'], $medId, $qty);
        echo json_encode(['success' => true, 'cart_count' => getCartCount($conn, $_SESSION['user']['id'])]);
        exit;
    }

    if ($type === 'update_cart' && isCustomer() && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $data  = json_decode(file_get_contents('php://input'), true);
        $cartId = intval($data['cart_id']  ?? 0);
        $qty    = intval($data['quantity'] ?? 0);
        $userId = $_SESSION['user']['id'];

        // Fetch cart row to verify stock
        $cartItems = getCartItems($conn, $userId);
        $cartRow   = null;
        foreach ($cartItems as $ci) { if ($ci['id'] == $cartId) { $cartRow = $ci; break; } }
        if (!$cartRow) { echo json_encode(['error' => 'Cart item not found']); exit; }
        if ($qty < 1)  { echo json_encode(['error' => 'Quantity must be at least 1']); exit; }
        if ($qty > $cartRow['availability']) {
            echo json_encode(['error' => 'Only ' . $cartRow['availability'] . ' in stock']); exit;
        }
        updateCartQuantity($conn, $cartId, $userId, $qty);
        // Recalculate totals
        $items  = getCartItems($conn, $userId);
        $total  = array_sum(array_map(function($i){ return $i['price'] * $i['quantity']; }, $items));
        $count  = getCartCount($conn, $userId);
        echo json_encode(['success' => true, 'new_subtotal' => number_format($cartRow['price'] * $qty, 2),
                          'cart_total' => number_format($total, 2), 'cart_count' => $count]);
        exit;
    }

    if ($type === 'remove_from_cart' && isCustomer() && $_SERVER['REQUEST_METHOD'] === 'DELETE') {
        parse_str(file_get_contents('php://input'), $data);
        $cartId = intval($data['cart_id'] ?? 0);
        $userId = $_SESSION['user']['id'];
        removeFromCart($conn, $cartId, $userId);
        $items  = getCartItems($conn, $userId);
        $total  = array_sum(array_map(function($i){ return $i['price'] * $i['quantity']; }, $items));
        $count  = getCartCount($conn, $userId);
        echo json_encode(['success' => true, 'cart_total' => number_format($total, 2), 'cart_count' => $count]);
        exit;
    }

    http_response_code(400);
    echo json_encode(['error' => 'Invalid request']);
    exit;
}


// ================================================================
// ADMIN CONTROLLERS
// ================================================================

function adminDashCtrl($conn) {
    if (!isAdmin()) redirectTo('index.php?page=login');

    $totalMedicines  = (int)(mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM medicines"))['c'] ?? 0);
    $totalCustomers  = (int)(mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM users WHERE role='customer'"))['c'] ?? 0);
    $totalOrders     = (int)(mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM orders"))['c'] ?? 0);
    $pendingOrders   = (int)(mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM orders WHERE status='pending'"))['c'] ?? 0);
    $recentOrders    = getAllOrders($conn);
    $recentOrders    = array_slice($recentOrders, 0, 5);

    require 'views/admin_dashboard.php';
}

function adminMedicinesCtrl($conn) {
    if (!isAdmin()) redirectTo('index.php?page=login');

    $action     = $_GET['action'] ?? 'list';
    $error      = '';
    $editing    = null;
    $categories = getAllCategories($conn);

    if ($action === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $name    = trim($_POST['name']        ?? '');
        $catId   = intval($_POST['category_id'] ?? 0);
        $vendor  = trim($_POST['vendor_name'] ?? '');
        $price   = trim($_POST['price']       ?? '');
        $stock   = trim($_POST['availability'] ?? '');
        $desc    = trim($_POST['description'] ?? '');

        if ($name === '' || $vendor === '' || $price === '' || $stock === '' || $catId === 0) {
            $error = 'All fields except image and description are required.';
        } elseif (!is_numeric($price) || floatval($price) < 0) {
            $error = 'Price must be a valid non-negative number.';
        } elseif (!ctype_digit($stock) || intval($stock) < 0) {
            $error = 'Stock must be a non-negative whole number.';
        } else {
            $imgPath = handleImageUpload('image') ?? '';
            if (addMedicine($conn, $name, $catId, $vendor, floatval($price), intval($stock), $desc, $imgPath)) {
                redirectTo('index.php?page=admin_medicines&msg=added');
            } else {
                $error = 'Failed to add medicine.';
            }
        }
    }

    if ($action === 'edit') {
        $editing = getMedicineById($conn, intval($_GET['id'] ?? 0));
        if (!$editing) redirectTo('index.php?page=admin_medicines');
    }

    if ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $id      = intval($_GET['id']          ?? 0);
        $name    = trim($_POST['name']        ?? '');
        $catId   = intval($_POST['category_id'] ?? 0);
        $vendor  = trim($_POST['vendor_name'] ?? '');
        $price   = trim($_POST['price']       ?? '');
        $stock   = trim($_POST['availability'] ?? '');
        $desc    = trim($_POST['description'] ?? '');

        if ($name === '' || $vendor === '' || $price === '' || $stock === '' || $catId === 0) {
            $error   = 'All required fields must be filled.';
            $editing = getMedicineById($conn, $id);
        } elseif (!is_numeric($price) || floatval($price) < 0) {
            $error   = 'Invalid price.';
            $editing = getMedicineById($conn, $id);
        } elseif (!ctype_digit($stock) || intval($stock) < 0) {
            $error   = 'Invalid stock.';
            $editing = getMedicineById($conn, $id);
        } else {
            $imgPath = handleImageUpload('image');
            if (updateMedicine($conn, $id, $name, $catId, $vendor, floatval($price), intval($stock), $desc, $imgPath)) {
                redirectTo('index.php?page=admin_medicines&msg=updated');
            } else {
                $error   = 'Update failed.';
                $editing = getMedicineById($conn, $id);
            }
        }
    }

    if ($action === 'delete') {
        $id  = intval($_GET['id'] ?? 0);
        $med = getMedicineById($conn, $id);
        if ($med && $med['image_path'] && file_exists($med['image_path'])) unlink($med['image_path']);
        if ($id > 0) deleteMedicine($conn, $id);
        redirectTo('index.php?page=admin_medicines&msg=deleted');
    }

    $medicines = getAllMedicines($conn);
    require 'views/admin_medicines.php';
}

function adminCategoriesCtrl($conn) {
    if (!isAdmin()) redirectTo('index.php?page=login');

    $action  = $_GET['action'] ?? 'list';
    $error   = '';
    $editing = null;

    if ($action === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = trim($_POST['name'] ?? '');
        $type = trim($_POST['category_type'] ?? '');
        if ($name === '' || !in_array($type, ['liquid','solid'])) {
            $error = 'Category name and valid type (liquid/solid) are required.';
        } else {
            if (addCategory($conn, $name, $type)) redirectTo('index.php?page=admin_categories&msg=added');
            else $error = 'Failed to add category.';
        }
    }

    if ($action === 'edit') {
        $editing = getCategoryById($conn, intval($_GET['id'] ?? 0));
        if (!$editing) redirectTo('index.php?page=admin_categories');
    }

    if ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $id   = intval($_GET['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $type = trim($_POST['category_type'] ?? '');
        if ($name === '' || !in_array($type, ['liquid','solid'])) {
            $error   = 'All fields are required and type must be liquid or solid.';
            $editing = getCategoryById($conn, $id);
        } else {
            if (updateCategory($conn, $id, $name, $type)) redirectTo('index.php?page=admin_categories&msg=updated');
            else { $error = 'Update failed.'; $editing = getCategoryById($conn, $id); }
        }
    }

    if ($action === 'delete') {
        $id = intval($_GET['id'] ?? 0);
        if ($id > 0) deleteCategory($conn, $id);
        redirectTo('index.php?page=admin_categories&msg=deleted');
    }

    $categories = getAllCategories($conn);
    require 'views/admin_categories.php';
}

function adminCustomersCtrl($conn) {
    if (!isAdmin()) redirectTo('index.php?page=login');

    if ($_GET['action'] ?? '' === 'delete') {
        $id = intval($_GET['id'] ?? 0);
        if ($id > 0) deleteUser($conn, $id);
        redirectTo('index.php?page=admin_customers&msg=deleted');
    }

    $customers = getAllCustomers($conn);
    require 'views/admin_customers.php';
}

function adminOrdersCtrl($conn) {
    if (!isAdmin()) redirectTo('index.php?page=login');

    $action = $_GET['action'] ?? '';
    if (in_array($action, ['accept','reject'])) {
        $id     = intval($_GET['id'] ?? 0);
        $status = $action === 'accept' ? 'accepted' : 'rejected';
        if ($id > 0) updateOrderStatus($conn, $id, $status);
        redirectTo('index.php?page=admin_orders&msg=' . $status);
    }

    $orders = getAllOrders($conn);
    require 'views/admin_orders.php';
}
?>
