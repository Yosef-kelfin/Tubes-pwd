<?php
require_once 'functions.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $teams = trim($_POST['teams'] ?? '');

    if (empty($name) || empty($email) || empty($password)) {
        $error = 'Semua field wajib diisi!';
    } else {
        $users = getUsers();
        foreach ($users as $u) {
            if ($u['email'] === $email) {
                $error = 'Email sudah terdaftar!';
                break;
            }
        }
        if (empty($error)) {
            $newUser = [
                'id' => uniqid(),
                'name' => $name,
                'email' => $email,
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'teams' => $teams,
                'created_at' => date('Y-m-d H:i:s')
            ];
            $users[] = $newUser;
            saveUsers($users);
            $success = 'Registrasi berhasil! Silakan login.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi - FindIt! UAJY</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="style.css">
</head>
<body class="min-h-screen flex items-center justify-center p-4">
    <div class="brut-card w-full max-w-md p-8">
        <div class="text-center mb-6">
            <h1 class="font-bebas text-3xl tracking-tight text-slate-900">FindIt!® UAJY</h1>
            <p class="text-slate-600 font-medium text-sm mt-1">Buat akun baru untuk melaporkan barang hilang / ditemukan</p>
        </div>

        <?php if ($error): ?>
            <div class="bg-red-50 border border-red-200 text-red-700 p-3 mb-4 rounded-xl font-semibold text-sm">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="bg-green-50 border border-green-200 text-green-700 p-3 mb-4 rounded-xl font-semibold text-sm">
                <?= htmlspecialchars($success) ?> <a href="login.php" class="underline text-slate-900 font-bold">Login sekarang</a>
            </div>
        <?php endif; ?>

        <form action="" method="POST" class="space-y-4">
            <div>
                <label class="block font-semibold text-xs mb-1 uppercase text-slate-600">Nama Lengkap</label>
                <input type="text" name="name" required class="brut-input w-full" placeholder="Nama Mahasiswa / CS" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false">
            </div>
            <div>
                <label class="block font-semibold text-xs mb-1 uppercase text-slate-600">Email Kampus / Pribadi</label>
                <input type="email" name="email" required class="brut-input w-full" placeholder="nama@uajy.ac.id" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false">
            </div>
            <div>
                <label class="block font-semibold text-xs mb-1 uppercase text-slate-600">Password</label>
                <input type="password" name="password" required class="brut-input w-full" placeholder="••••••••" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false">
            </div>
            <div>
                <label class="block font-semibold text-xs mb-1 uppercase text-slate-600">Username / Link Microsoft Teams</label>
                <input type="text" name="teams" class="brut-input w-full" placeholder="teams.microsoft.com/l/chat/... atau username" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false">
            </div>
            <button type="submit" class="brut-btn brut-btn-primary w-full py-3 text-base font-bebas">Daftar Akun</button>
        </form>

        <div class="mt-6 text-center text-sm font-medium text-slate-600">
            Sudah punya akun? <a href="login.php" class="text-slate-900 underline font-semibold">Login di sini</a>
        </div>
        <div class="mt-4 text-center">
            <a href="index.php" class="back-btn">&larr; Kembali ke Beranda</a>
        </div>
    </div>
    <script src="script.js"></script>
</body>
</html>
