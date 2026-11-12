<?php
require_once 'functions.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Email dan password wajib diisi!';
    } else {
        $users = getUsers();
        $found = false;
        foreach ($users as $u) {
            if ($u['email'] === $email && password_verify($password, $u['password'])) {
                $_SESSION['user'] = $u;
                $found = true;
                break;
            }
        }
        if ($found) {
            header('Location: index.php');
            exit;
        } else {
            $error = 'Email atau password salah!';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - FindIt! UAJY</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="style.css">
</head>
<body class="min-h-screen flex items-center justify-center p-4">
    <div class="brut-card w-full max-w-md p-8">
        <div class="text-center mb-6">
            <h1 class="font-bebas text-3xl tracking-tight text-slate-900">FindIt!® UAJY</h1>
            <p class="text-slate-600 font-medium text-sm mt-1">Masuk untuk melaporkan & mengelola barang hilang / ditemukan</p>
        </div>

        <?php if ($error): ?>
            <div class="bg-red-50 border border-red-200 text-red-700 p-3 mb-4 rounded-xl font-semibold text-sm">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST" class="space-y-4">
            <div>
                <label class="block font-semibold text-xs mb-1 uppercase text-slate-600">Email</label>
                <input type="email" name="email" required class="brut-input w-full" placeholder="nama@uajy.ac.id" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false">
            </div>
            <div>
                <label class="block font-semibold text-xs mb-1 uppercase text-slate-600">Password</label>
                <input type="password" name="password" required class="brut-input w-full" placeholder="••••••••" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false">
            </div>
            <button type="submit" class="brut-btn brut-btn-primary w-full py-3 text-base font-bebas">Login</button>
        </form>

        <div class="mt-6 text-center text-sm font-medium text-slate-600">
            Belum punya akun? <a href="register.php" class="text-slate-900 underline font-semibold">Daftar sekarang</a>
        </div>
        <div class="mt-4 text-center">
            <a href="index.php" class="back-btn">&larr; Kembali ke Beranda</a>
        </div>
    </div>
    <script src="script.js"></script>
</body>
</html>
