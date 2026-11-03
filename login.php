
<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

// Jika sudah login, langsung ke dashboard
if (is_logged_in()) {
    redirect('index.php');
}

// Inisialisasi pesan error
$error = '';

// Proses login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare(
        "SELECT id, name, username, password
         FROM users
         WHERE username = ?
         LIMIT 1"
    );

    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        session_regenerate_id(true);

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];

        redirect('index.php');
    } else {
        $error = 'Username atau password tidak sesuai.';
    }
}
?>

<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Login | CRM 6012 Street Coffee</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <!-- CSS Utama -->
    <link href="assets/style.css" rel="stylesheet">
</head>

<body class="login-body">

    <div class="login-card">

        <!-- Logo dan Brand -->
        <div class="login-brand login-brand-center">

            <img src="assets/img/logo_6012.png"
                 alt="Logo 6012 Street Coffee"
                 class="login-logo">

            <span>STREET COFFEE</span>

        </div>

        <!-- Judul -->
        <div class="login-heading">

            <h1>Selamat datang</h1>

            <p class="muted">
                Masuk untuk mengelola hubungan pelanggan.
            </p>

        </div>

        <!-- Pesan Error -->
        <?php if (!empty($error)): ?>

            <div class="alert alert-danger">
                <?= e($error) ?>
            </div>

        <?php endif; ?>

        <!-- Form Login -->
        <form method="post">

            <input type="hidden"
                   name="csrf_token"
                   value="<?= e(csrf_token()) ?>">

            <label class="form-label">Username</label>

            <input
                class="form-control mb-3"
                name="username"
                required
                autofocus
                placeholder="Masukkan username">

            <label class="form-label">Password</label>

            <input
                class="form-control mb-4"
                type="password"
                name="password"
                required
                placeholder="Masukkan password">

            <button type="submit" class="btn btn-coffee w-100">
                Masuk ke Dashboard
            </button>

        </form>

    </div>

</body>
</html>
