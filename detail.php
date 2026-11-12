<?php
require_once 'functions.php';

$itemId = $_GET['id'] ?? '';
$items = getItems();
$item = null;

foreach ($items as $it) {
    if ($it['id'] === $itemId) {
        $item = $it;
        break;
    }
}

if (!$item) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><= htmlspecialchars($item['title']) ?> - FindIt! UAJY</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="style.css">
</head>
<body class="bg-slate-50 min-h-screen pb-12">
    <!-- Navbar -->
    <nav class="backdrop-blur-md bg-white/90 border border-slate-200 sticky top-0 z-50 px-6 py-4 flex justify-between items-center shadow-md">
        <a href="index.php" class="font-bebas text-2xl tracking-tight text-slate-900">FindIt!® <span class="text-slate-700 font-sans text-xs">UAJY</span></a>
        <div class="hidden md:flex items-center gap-6 font-medium text-sm text-slate-600">
            <a href="index.php" class="hover:text-slate-900 transition">Beranda</a>
            <a href="index.php#search-section" class="hover:text-slate-900 transition">Cari Barang</a>
            <a href="report.php" class="hover:text-slate-900 transition">Lapor Barang</a>
            <?php if (isLoggedIn()): ?>
                <a href="profile.php" class="hover:text-slate-900 transition">Dashboard Saya</a>
            <?php endif; ?>
        </div>
        <div class="flex items-center gap-4">
            <?php if (isLoggedIn()): ?>
                <a href="profile.php" class="brut-btn px-4 py-2 text-sm hidden sm:inline-flex">Dashboard</a>
                <a href="logout.php" class="brut-btn brut-btn-danger px-4 py-2 text-sm">Keluar</a>
            <?php else: ?>
                <a href="login.php" class="brut-btn px-4 py-2 text-sm">Masuk</a>
                <a href="register.php" class="brut-btn brut-btn-primary px-4 py-2 text-sm">Daftar</a>
            <?php endif; ?>
        </div>
    </nav>

    <div class="max-w-3xl mx-auto px-4 mt-8">
        <a href="index.php#search-section" class="back-btn mb-4">&larr; Kembali ke Daftar Barang</a>

        <div class="brut-card p-8 bg-white shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-3 mb-6 border-b border-slate-200 pb-4">
                <span class="px-3.5 py-1.5 text-xs font-bold rounded-xl border <?= $item['type'] === 'hilang' ? 'bg-red-50 text-red-700 border-red-200' : 'bg-green-50 text-green-700 border-green-200' ?>">
                    <?= $item['type'] === 'hilang' ? '🔴 BARANG HILANG' : '🟢 BARANG DITEMUKAN' ?>
                </span>
                <span class="px-3.5 py-1.5 text-xs font-bold rounded-xl border bg-amber-50 text-amber-800 border-amber-200">
                    Status: <?= htmlspecialchars($item['status']) ?>
                </span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                <div>
                    <?php if (!empty($item['image']) && file_exists('uploads/' . $item['image'])): ?>
                        <img src="uploads/<?= htmlspecialchars($item['image']) ?>" alt="Foto Barang" class="w-full h-80 object-cover rounded-2xl border border-slate-200 shadow-md">
                    <?php else: ?>
                        <div class="w-full h-80 bg-slate-100 border border-slate-200 rounded-2xl flex items-center justify-center font-bold text-slate-400 shadow-sm">
                            Tidak ada foto
                        </div>
                    <?php endif; ?>
                </div>

                <div class="flex flex-col justify-between space-y-4">
                    <div>
                        <h1 class="font-bebas text-4xl text-slate-900 leading-tight mb-4"><?= htmlspecialchars($item['title']) ?></h1>
                        
                        <div class="space-y-3 text-sm text-slate-600 border-t border-slate-100 pt-4">
                            <div class="flex justify-between">
                                <strong class="font-semibold text-slate-900">Kategori:</strong>
                                <span><?= htmlspecialchars($item['category']) ?></span>
                            </div>
                            <div class="flex justify-between">
                                <strong class="font-semibold text-slate-900">Tanggal:</strong>
                                <span><?= htmlspecialchars($item['date']) ?></span>
                            </div>
                            <div class="flex justify-between">
                                <strong class="font-semibold text-slate-900">Lokasi:</strong>
                                <span><?= htmlspecialchars($item['location']) ?></span>
                            </div>
                            <div class="flex justify-between items-center">
                                <strong class="font-semibold text-slate-900">Penyimpanan:</strong>
                                <span class="bg-blue-50 border border-blue-200 px-2.5 py-1 rounded-lg text-blue-800 text-xs font-medium"><?= htmlspecialchars($item['storage_location'] ?: 'Belum ditentukan') ?></span>
                            </div>
                            <div class="flex justify-between">
                                <strong class="font-semibold text-slate-900">Pelapor:</strong>
                                <span><?= htmlspecialchars($item['user_name']) ?></span>
                            </div>
                        </div>
                    </div>

                    <?php if (!empty($item['user_teams'])): ?>
                        <div>
                            <a href="<?= filter_var($item['user_teams'], FILTER_VALIDATE_URL) ? htmlspecialchars($item['user_teams']) : 'https://teams.microsoft.com/l/chat/0/0?users=' . urlencode($item['user_teams']) ?>" target="_blank" class="brut-btn brut-btn-primary block w-full text-center py-3.5 text-base font-bebas tracking-wide shadow-sm">
                                Hubungi via Microsoft Teams →
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="border-t border-slate-200 pt-6">
                <h3 class="font-bebas text-2xl mb-3 text-slate-900">Deskripsi & Ciri-Ciri Barang</h3>
                <p class="text-slate-700 whitespace-pre-line bg-slate-50 p-5 rounded-2xl border border-slate-200 leading-relaxed text-sm"><?= htmlspecialchars($item['description'] ?: 'Tidak ada deskripsi tambahan.') ?></p>
            </div>
        </div>
    </div>
    <script src="script.js"></script>
</body>
</html>
