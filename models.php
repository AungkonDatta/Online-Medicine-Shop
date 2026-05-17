<?php
// ================================================================
// models.php — All DB access using procedural mysqli + prepared stmts
// Online Medicine Shop | Group 8 | No OOP
// ================================================================


// ================================================================
// USER MODELS
// ================================================================

function getUserByEmail($conn, $email) {
    $st = mysqli_prepare($conn, "SELECT * FROM users WHERE email = ? LIMIT 1");
    mysqli_stmt_bind_param($st, 's', $email);
    mysqli_stmt_execute($st);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($st));
    mysqli_stmt_close($st);
    return $row;
}

function getUserById($conn, $id) {
    $st = mysqli_prepare($conn, "SELECT * FROM users WHERE id = ? LIMIT 1");
    mysqli_stmt_bind_param($st, 'i', $id);
    mysqli_stmt_execute($st);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($st));
    mysqli_stmt_close($st);
    return $row;
}

function registerCustomer($conn, $name, $email, $password, $phone, $address) {
    if (getUserByEmail($conn, $email)) return false; // duplicate email
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $st = mysqli_prepare($conn,
        "INSERT INTO users (name, email, password_hash, role, phone, address) VALUES (?, ?, ?, 'customer', ?, ?)");
    mysqli_stmt_bind_param($st, 'sssss', $name, $email, $hash, $phone, $address);
    $ok = mysqli_stmt_execute($st);
    mysqli_stmt_close($st);
    return $ok;
}

function updateUserProfile($conn, $id, $name, $phone, $address, $picturePath = null) {
    if ($picturePath !== null) {
        $st = mysqli_prepare($conn,
            "UPDATE users SET name=?, phone=?, address=?, profile_picture=? WHERE id=?");
        mysqli_stmt_bind_param($st, 'ssssi', $name, $phone, $address, $picturePath, $id);
    } else {
        $st = mysqli_prepare($conn,
            "UPDATE users SET name=?, phone=?, address=? WHERE id=?");
        mysqli_stmt_bind_param($st, 'sssi', $name, $phone, $address, $id);
    }
    $ok = mysqli_stmt_execute($st);
    mysqli_stmt_close($st);
    return $ok;
}

function getAllCustomers($conn) {
    $r = mysqli_query($conn, "SELECT id, name, email, phone, address, created_at FROM users WHERE role='customer' ORDER BY id DESC");
    return mysqli_fetch_all($r, MYSQLI_ASSOC);
}

function deleteUser($conn, $id) {
    $st = mysqli_prepare($conn, "DELETE FROM users WHERE id=? AND role='customer'");
    mysqli_stmt_bind_param($st, 'i', $id);
    $ok = mysqli_stmt_execute($st);
    mysqli_stmt_close($st);
    return $ok;
}

function searchCustomers($conn, $term) {
    $like = '%' . $term . '%';
    $st = mysqli_prepare($conn,
        "SELECT id, name, email, phone, address, created_at FROM users
         WHERE role='customer' AND (name LIKE ? OR email LIKE ? OR phone LIKE ?)
         ORDER BY id DESC");
    mysqli_stmt_bind_param($st, 'sss', $like, $like, $like);
    mysqli_stmt_execute($st);
    $rows = mysqli_fetch_all(mysqli_stmt_get_result($st), MYSQLI_ASSOC);
    mysqli_stmt_close($st);
    return $rows;
}


// ================================================================
// CATEGORY MODELS
// ================================================================

function getAllCategories($conn) {
    $r = mysqli_query($conn, "SELECT * FROM categories ORDER BY id DESC");
    return mysqli_fetch_all($r, MYSQLI_ASSOC);
}

function getCategoryById($conn, $id) {
    $st = mysqli_prepare($conn, "SELECT * FROM categories WHERE id=?");
    mysqli_stmt_bind_param($st, 'i', $id);
    mysqli_stmt_execute($st);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($st));
    mysqli_stmt_close($st);
    return $row;
}

function addCategory($conn, $name, $type) {
    $st = mysqli_prepare($conn, "INSERT INTO categories (name, category_type) VALUES (?, ?)");
    mysqli_stmt_bind_param($st, 'ss', $name, $type);
    $ok = mysqli_stmt_execute($st);
    mysqli_stmt_close($st);
    return $ok;
}

function updateCategory($conn, $id, $name, $type) {
    $st = mysqli_prepare($conn, "UPDATE categories SET name=?, category_type=? WHERE id=?");
    mysqli_stmt_bind_param($st, 'ssi', $name, $type, $id);
    $ok = mysqli_stmt_execute($st);
    mysqli_stmt_close($st);
    return $ok;
}

function deleteCategory($conn, $id) {
    $st = mysqli_prepare($conn, "DELETE FROM categories WHERE id=?");
    mysqli_stmt_bind_param($st, 'i', $id);
    $ok = mysqli_stmt_execute($st);
    mysqli_stmt_close($st);
    return $ok;
}


// ================================================================
// MEDICINE MODELS
// ================================================================

function getAllMedicines($conn) {
    $r = mysqli_query($conn,
        "SELECT m.*, c.name AS category_name, c.category_type
         FROM medicines m
         JOIN categories c ON c.id = m.category_id
         ORDER BY m.id DESC");
    return mysqli_fetch_all($r, MYSQLI_ASSOC);
}

function getMedicineById($conn, $id) {
    $st = mysqli_prepare($conn,
        "SELECT m.*, c.name AS category_name, c.category_type
         FROM medicines m
         JOIN categories c ON c.id = m.category_id
         WHERE m.id = ?");
    mysqli_stmt_bind_param($st, 'i', $id);
    mysqli_stmt_execute($st);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($st));
    mysqli_stmt_close($st);
    return $row;
}

function addMedicine($conn, $name, $catId, $vendor, $price, $stock, $desc, $imagePath) {
    $st = mysqli_prepare($conn,
        "INSERT INTO medicines (name, category_id, vendor_name, price, availability, description, image_path)
         VALUES (?, ?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($st, 'sisdiss', $name, $catId, $vendor, $price, $stock, $desc, $imagePath);
    $ok = mysqli_stmt_execute($st);
    mysqli_stmt_close($st);
    return $ok;
}

function updateMedicine($conn, $id, $name, $catId, $vendor, $price, $stock, $desc, $imagePath = null) {
    if ($imagePath !== null) {
        $st = mysqli_prepare($conn,
            "UPDATE medicines SET name=?, category_id=?, vendor_name=?, price=?, availability=?, description=?, image_path=? WHERE id=?");
        mysqli_stmt_bind_param($st, 'sisdissi', $name, $catId, $vendor, $price, $stock, $desc, $imagePath, $id);
    } else {
        $st = mysqli_prepare($conn,
            "UPDATE medicines SET name=?, category_id=?, vendor_name=?, price=?, availability=?, description=? WHERE id=?");
        mysqli_stmt_bind_param($st, 'sisdisi', $name, $catId, $vendor, $price, $stock, $desc, $id);
    }
    $ok = mysqli_stmt_execute($st);
    mysqli_stmt_close($st);
    return $ok;
}

function deleteMedicine($conn, $id) {
    $st = mysqli_prepare($conn, "DELETE FROM medicines WHERE id=?");
    mysqli_stmt_bind_param($st, 'i', $id);
    $ok = mysqli_stmt_execute($st);
    mysqli_stmt_close($st);
    return $ok;
}

function searchMedicines($conn, $term, $vendorFilter = '', $typeFilter = '') {
    $like = '%' . $term . '%';
    $vendorLike = '%' . $vendorFilter . '%';

    $sql = "SELECT m.*, c.name AS category_name, c.category_type
            FROM medicines m
            JOIN categories c ON c.id = m.category_id
            WHERE (m.name LIKE ? OR m.description LIKE ?)";
    $params = 'ss';
    $binds  = [$like, $like];

    if ($vendorFilter !== '') {
        $sql .= " AND m.vendor_name LIKE ?";
        $params .= 's';
        $binds[] = $vendorLike;
    }
    if ($typeFilter !== '') {
        $sql .= " AND c.category_type = ?";
        $params .= 's';
        $binds[] = $typeFilter;
    }
    $sql .= " ORDER BY m.id DESC";

    $st = mysqli_prepare($conn, $sql);
    // Build refs array for bind_param (compatible with PHP 5.6+)
    $bindArgs = array($st, $params);
    foreach ($binds as $k => $v) { $bindArgs[] = &$binds[$k]; }
    call_user_func_array('mysqli_stmt_bind_param', $bindArgs);
    mysqli_stmt_execute($st);
    $rows = mysqli_fetch_all(mysqli_stmt_get_result($st), MYSQLI_ASSOC);
    mysqli_stmt_close($st);
    return $rows;
}


// ================================================================
// CART MODELS
// ================================================================

function getCartItems($conn, $userId) {
    $st = mysqli_prepare($conn,
        "SELECT c.id, c.quantity, m.id AS medicine_id, m.name, m.vendor_name, m.price, m.availability, m.image_path
         FROM cart c
         JOIN medicines m ON m.id = c.medicine_id
         WHERE c.user_id = ?
         ORDER BY c.added_at DESC");
    mysqli_stmt_bind_param($st, 'i', $userId);
    mysqli_stmt_execute($st);
    $rows = mysqli_fetch_all(mysqli_stmt_get_result($st), MYSQLI_ASSOC);
    mysqli_stmt_close($st);
    return $rows;
}

function getCartCount($conn, $userId) {
    $st = mysqli_prepare($conn, "SELECT SUM(quantity) AS cnt FROM cart WHERE user_id=?");
    mysqli_stmt_bind_param($st, 'i', $userId);
    mysqli_stmt_execute($st);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($st));
    mysqli_stmt_close($st);
    return (int)($row['cnt'] ?? 0);
}

function addToCart($conn, $userId, $medicineId, $quantity) {
    // If already in cart, increase quantity
    $st = mysqli_prepare($conn,
        "INSERT INTO cart (user_id, medicine_id, quantity) VALUES (?, ?, ?)
         ON DUPLICATE KEY UPDATE quantity = quantity + VALUES(quantity)");
    mysqli_stmt_bind_param($st, 'iii', $userId, $medicineId, $quantity);
    $ok = mysqli_stmt_execute($st);
    mysqli_stmt_close($st);
    return $ok;
}

function updateCartQuantity($conn, $cartId, $userId, $quantity) {
    $st = mysqli_prepare($conn,
        "UPDATE cart SET quantity=? WHERE id=? AND user_id=?");
    mysqli_stmt_bind_param($st, 'iii', $quantity, $cartId, $userId);
    $ok = mysqli_stmt_execute($st);
    mysqli_stmt_close($st);
    return $ok;
}

function removeFromCart($conn, $cartId, $userId) {
    $st = mysqli_prepare($conn, "DELETE FROM cart WHERE id=? AND user_id=?");
    mysqli_stmt_bind_param($st, 'ii', $cartId, $userId);
    $ok = mysqli_stmt_execute($st);
    mysqli_stmt_close($st);
    return $ok;
}

function clearCart($conn, $userId) {
    $st = mysqli_prepare($conn, "DELETE FROM cart WHERE user_id=?");
    mysqli_stmt_bind_param($st, 'i', $userId);
    $ok = mysqli_stmt_execute($st);
    mysqli_stmt_close($st);
    return $ok;
}


// ================================================================
// ORDER MODELS
// ================================================================

function createOrder($conn, $userId, $totalAmount, $shippingAddress, $paymentMethod) {
    $st = mysqli_prepare($conn,
        "INSERT INTO orders (user_id, total_amount, shipping_address, payment_method, status)
         VALUES (?, ?, ?, ?, 'pending')");
    mysqli_stmt_bind_param($st, 'idss', $userId, $totalAmount, $shippingAddress, $paymentMethod);
    $ok = mysqli_stmt_execute($st);
    $orderId = mysqli_stmt_insert_id($st);
    mysqli_stmt_close($st);
    return $ok ? $orderId : false;
}

function addOrderItem($conn, $orderId, $medicineId, $quantity, $unitPrice) {
    $st = mysqli_prepare($conn,
        "INSERT INTO order_items (order_id, medicine_id, quantity, unit_price) VALUES (?, ?, ?, ?)");
    mysqli_stmt_bind_param($st, 'iiid', $orderId, $medicineId, $quantity, $unitPrice);
    $ok = mysqli_stmt_execute($st);
    mysqli_stmt_close($st);
    return $ok;
}

function createPayment($conn, $orderId, $amount, $paymentMethod) {
    $txn = strtoupper(bin2hex(random_bytes(6)));
    $st = mysqli_prepare($conn,
        "INSERT INTO payments (order_id, amount, payment_method, transaction_id) VALUES (?, ?, ?, ?)");
    mysqli_stmt_bind_param($st, 'idss', $orderId, $amount, $paymentMethod, $txn);
    mysqli_stmt_execute($st);
    mysqli_stmt_close($st);
    return $txn;
}

function reduceStock($conn, $medicineId, $quantity) {
    $st = mysqli_prepare($conn,
        "UPDATE medicines SET availability = availability - ? WHERE id = ? AND availability >= ?");
    mysqli_stmt_bind_param($st, 'iii', $quantity, $medicineId, $quantity);
    $ok = mysqli_stmt_execute($st);
    mysqli_stmt_close($st);
    return $ok;
}

function getOrdersByUser($conn, $userId) {
    $st = mysqli_prepare($conn,
        "SELECT * FROM orders WHERE user_id=? ORDER BY order_date DESC");
    mysqli_stmt_bind_param($st, 'i', $userId);
    mysqli_stmt_execute($st);
    $rows = mysqli_fetch_all(mysqli_stmt_get_result($st), MYSQLI_ASSOC);
    mysqli_stmt_close($st);
    return $rows;
}

function getOrderById($conn, $orderId) {
    $st = mysqli_prepare($conn, "SELECT * FROM orders WHERE id=?");
    mysqli_stmt_bind_param($st, 'i', $orderId);
    mysqli_stmt_execute($st);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($st));
    mysqli_stmt_close($st);
    return $row;
}

function getOrderItems($conn, $orderId) {
    $st = mysqli_prepare($conn,
        "SELECT oi.*, m.name AS medicine_name, m.vendor_name
         FROM order_items oi
         JOIN medicines m ON m.id = oi.medicine_id
         WHERE oi.order_id=?");
    mysqli_stmt_bind_param($st, 'i', $orderId);
    mysqli_stmt_execute($st);
    $rows = mysqli_fetch_all(mysqli_stmt_get_result($st), MYSQLI_ASSOC);
    mysqli_stmt_close($st);
    return $rows;
}

function getAllOrders($conn) {
    $r = mysqli_query($conn,
        "SELECT o.*, u.name AS customer_name, u.email AS customer_email
         FROM orders o
         JOIN users u ON u.id = o.user_id
         ORDER BY o.order_date DESC");
    return mysqli_fetch_all($r, MYSQLI_ASSOC);
}

function updateOrderStatus($conn, $orderId, $status) {
    $st = mysqli_prepare($conn, "UPDATE orders SET status=? WHERE id=?");
    mysqli_stmt_bind_param($st, 'si', $status, $orderId);
    $ok = mysqli_stmt_execute($st);
    mysqli_stmt_close($st);
    return $ok;
}

function searchOrders($conn, $term) {
    $like = '%' . $term . '%';
    $st = mysqli_prepare($conn,
        "SELECT o.*, u.name AS customer_name, u.email AS customer_email
         FROM orders o
         JOIN users u ON u.id = o.user_id
         WHERE u.name LIKE ? OR u.email LIKE ? OR o.status LIKE ? OR CAST(o.id AS CHAR) LIKE ?
         ORDER BY o.order_date DESC");
    mysqli_stmt_bind_param($st, 'ssss', $like, $like, $like, $like);
    mysqli_stmt_execute($st);
    $rows = mysqli_fetch_all(mysqli_stmt_get_result($st), MYSQLI_ASSOC);
    mysqli_stmt_close($st);
    return $rows;
}
?>
