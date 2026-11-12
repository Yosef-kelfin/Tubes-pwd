<?php
require_once 'functions.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$currentUser = getCurrentUser();
$items = getItems();

// Handle Delete
if (isset($_GET['delete'])) {
    $deleteId = $_GET['delete'];
    $items = array_filter($items, function($it) use ($deleteId, $currentUser) {
        return !($it['id'] === $deleteId && $it['user_id'] === $currentUser['id']);
    });
    saveItems(array_values($items));
    header('Location: profile.php');
    exit;
}

// Filter user's items
$myItems = array_filter($items, function($it) use ($currentUser) {
    return $it['user_id'] === $currentUser['id'];
});

$totalHilang = count(array_filter($myItems, fn($it) => $it['type'] === 'hilang'));
$totalDitemukan = count(array_filter($myItems, fn($it) => $it['type'] === 'ditemukan'));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Profil - FindIt! UAJY</title>
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
            <a href="profile.php" class="hover:text-slate-900 transition text-slate-900 font-semibold">Dashboard Saya</a>
        </div>
        <div class="flex items-center gap-4">
            <a href="report.php" class="brut-btn px-4 py-2 text-sm hidden sm:inline-flex">+ Lapor</a>
            <a href="logout.php" class="brut-btn brut-btn-danger px-4 py-2 text-sm">Keluar</a>
        </div>
    </nav>

    <div class="max-w-5xl mx-auto px-4 mt-8">
        <!-- Profile Card -->
        <div class="brut-card p-8 mb-8 flex flex-col md:flex-row justify-between items-start md:items-center gap-6 bg-white/90">
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 rounded-full bg-slate-900 text-white font-bebas text-2xl flex items-center justify-center shadow-md">
                    <?= strtoupper(substr($currentUser['name'], 0, 2)) ?>
                </div>
                <div>
                    <h1 class="font-bebas text-3xl text-slate-900 tracking-tight"><?= htmlspecialchars($currentUser['name']) ?></h1>
                    <p class="text-slate-600 text-sm font-medium"><?= htmlspecialchars($currentUser['email']) ?></p>
                    <p class="text-xs text-slate-500 font-semibold mt-1">Microsoft Teams: <span class="text-slate-900"><?= htmlspecialchars($currentUser['teams'] ?: 'Belum diatur') ?></span></p>
                </div>
            </div>
            <a href="report.php" class="brut-btn brut-btn-primary px-6 py-3 font-bebas text-base shadow-sm">+ Buat Laporan Baru</a>
        </div>

        <!-- Statistics Overview -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
            <div class="brut-card p-6 bg-white/80">
                <p class="text-xs font-bold uppercase text-slate-500 mb-1">Total Laporan Saya</p>
                <p class="font-bebas text-4xl text-slate-900"><?= count($myItems) ?></p>
            </div>
            <div class="brut-card p-6 bg-white/80">
                <p class="text-xs font-bold uppercase text-red-600 mb-1">Barang Hilang</p>
                <p class="font-bebas text-4xl text-red-700"><?= $totalHilang ?></p>
            </div>
            <div class="brut-card p-6 bg-white/80">
                <p class="text-xs font-bold uppercase text-green-600 mb-1">Barang Ditemukan</p>
                <p class="font-bebas text-4xl text-green-700"><?= $totalDitemukan ?></p>
            </div>
        </div>

        <!-- My Reports -->
        <h2 class="font-bebas text-3xl mb-4 text-slate-900">Riwayat Laporan Saya</h2>

        <?php if (empty($myItems)): ?>
            <div class="brut-card p-8 text-center">
                <p class="text-slate-600 font-medium mb-4">Anda belum membuat laporan barang hilang atau ditemukan.</p>
                <a href="report.php" class="brut-btn brut-btn-primary px-6 py-2 font-bebas">Buat Laporan Sekarang</a>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <?php foreach ($myItems as $it): ?>
                    <div class="brut-card p-6 flex flex-col justify-between">
                        <div>
                            <div class="flex justify-between items-center mb-3">
                                <span class="px-2.5 py-0.5 text-xs font-bold rounded-lg border <?= $it['type'] === 'hilang' ? 'bg-red-50 text-red-700 border-red-200' : 'bg-green-50 text-green-700 border-green-200' ?>">
                                    <?= strtoupper($it['type']) ?>
                                </span>
                                <span class="px-2.5 py-0.5 text-xs font-bold rounded-lg border bg-amber-50 text-amber-800 border-amber-200">
                                    <?= htmlspecialchars($it['status']) ?>
                                </span>
                            </div>

                            <h3 class="font-bebas text-2xl text-slate-900 mb-1">
                                <a href="detail.php?id=<?= $it['id'] ?>" class="hover:underline"><?= htmlspecialchars($it['title']) ?></a>
                            </h3>
                            <p class="text-xs text-slate-600 font-semibold mb-2">Kategori: <?= htmlspecialchars($it['category']) ?> | Lokasi: <?= htmlspecialchars($it['location']) ?></p>
                            <p class="text-sm text-slate-700 mb-4 line-clamp-2"><?= htmlspecialchars($it['description']) ?></p>
                        </div>

                        <div class="flex items-center gap-2 pt-4 border-t border-slate-200">
                            <a href="detail.php?id=<?= $it['id'] ?>" class="brut-btn flex-1 py-2 text-center text-sm font-bebas">Detail</a>
                            <a href="edit.php?id=<?= $it['id'] ?>" class="brut-btn flex-1 py-2 text-center text-sm font-bebas brut-btn-primary">Edit</a>
                            <a href="profile.php?delete=<?= $it['id'] ?>" onclick="return confirm('Yakin ingin menghapus laporan ini?')" class="brut-btn py-2 px-3 text-center text-sm font-bebas brut-btn-danger">Hapus</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    <script src="script.js"></script>
</body>
</html>
