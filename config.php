<?php
// ================================================================
// config.php — Database connection (procedural mysqli)
// Online Medicine Shop | Group 8
// ================================================================
$conn = mysqli_connect('localhost', 'root', '', 'online_medicine_shop');
if (!$conn) {
    die('Database connection failed: ' . mysqli_connect_error());
}
mysqli_set_charset($conn, 'utf8mb4');

// Auto-seed default admin on first run (email: admin@medicine.com / password: admin123)
$chk = mysqli_query($conn, "SELECT id FROM users WHERE role = 'admin' LIMIT 1");
if ($chk && mysqli_num_rows($chk) === 0) {
    $hash = password_hash('admin123', PASSWORD_DEFAULT);
    $st   = mysqli_prepare($conn,
        "INSERT INTO users (name, email, password_hash, role) VALUES ('Administrator', 'admin@medicine.com', ?, 'admin')");
    mysqli_stmt_bind_param($st, 's', $hash);
    mysqli_stmt_execute($st);
    mysqli_stmt_close($st);
}
?>
