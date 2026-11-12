<?php
require_once 'functions.php';

$items = getItems();

// Search and Filter parameters
$search = trim($_GET['search'] ?? '');
$category = $_GET['category'] ?? '';
$type = $_GET['type'] ?? '';
$status = $_GET['status'] ?? '';

$filteredItems = array_filter($items, function ($it) use ($search, $category, $type, $status) {
    if (!empty($search)) {
        $q = strtolower($search);
        $matchTitle = strpos(strtolower($it['title']), $q) !== false;
        $matchDesc = strpos(strtolower($it['description']), $q) !== false;
        $matchLoc = strpos(strtolower($it['location']), $q) !== false;
        if (!$matchTitle && !$matchDesc && !$matchLoc)
            return false;
    }
    if (!empty($category) && $it['category'] !== $category) {
        return false;
    }
    if (!empty($type) && $it['type'] !== $type) {
        return false;
    }
    if (!empty($status) && $it['status'] !== $status) {
        return false;
    }
    return true;
});

// Sort newest first
usort($filteredItems, function ($a, $b) {
    return strcmp($b['created_at'] ?? '', $a['created_at'] ?? '');
});
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FindIt! — Campus Lost & Found UAJY</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="style.css">
</head>

<body class="bg-slate-50 min-h-screen pb-16">

    <!-- Navbar -->
    <div class="max-w-5xl mx-auto px-4 pt-4 sticky top-4 z-50">
        <nav
            class="backdrop-blur-md bg-white/90 border border-slate-200 px-6 py-3 rounded-full flex justify-between items-center shadow-md">
            <a href="index.php" class="font-bebas text-2xl tracking-tight text-slate-900 flex items-center gap-2">
                <span>FindIt!®</span> <span
                    class="bg-slate-100 border border-slate-200 px-2 py-0.5 text-xs rounded-md text-slate-700 font-sans">UAJY</span>
            </a>
            <div class="hidden md:flex items-center gap-6 font-medium text-sm text-slate-600">
                <a href="index.php" class="hover:text-slate-900 transition text-slate-900 font-semibold">Beranda</a>
                <?php if (isLoggedIn()): ?>
                    <a href="index.php#search-section" class="hover:text-slate-900 transition">Cari Barang</a>
                    <a href="report.php" class="hover:text-slate-900 transition">Lapor Barang</a>
                    <a href="profile.php" class="hover:text-slate-900 transition">Dashboard Saya</a>
                <?php endif; ?>
            </div>
            <div class="flex items-center gap-3">
                <?php if (isLoggedIn()): ?>
                    <a href="profile.php" class="brut-btn px-4 py-2 text-sm hidden sm:inline-flex">Profil</a>
                    <a href="logout.php" class="brut-btn brut-btn-danger px-4 py-2 text-sm">Keluar</a>
                <?php else: ?>
                    <a href="login.php" class="brut-btn px-4 py-2 text-sm">Masuk</a>
                    <a href="register.php" class="brut-btn brut-btn-primary px-4 py-2 text-sm">Daftar</a>
                <?php endif; ?>
            </div>
        </nav>
    </div>

    <!-- Hero Section -->
    <div class="max-w-5xl mx-auto px-4 mt-12 mb-12 text-center">
        <h1 class="font-bebas text-5xl md:text-7xl text-slate-900 tracking-tight mb-4 leading-tight">
            Barang Hilang di Kampus?<br>Temukan Cepat di Sini.
        </h1>
        <p class="text-slate-600 font-medium text-lg mb-8 max-w-xl mx-auto">
            Platform Lost & Found UAJY paling praktis, terpercaya, dan bebas ribet.
        </p>
        <div class="flex items-center justify-center gap-4">
            <?php if (isLoggedIn()): ?>
                <a href="report.php" class="brut-btn brut-btn-primary px-8 py-3.5 text-base font-medium">Buat Laporan
                    Sekarang →</a>
                <a href="index.php#search-section" class="brut-btn px-8 py-3.5 text-base font-medium">Cari Barang Hilang</a>
            <?php else: ?>
                <a href="register.php" class="brut-btn brut-btn-primary px-8 py-3.5 text-base font-medium">Mulai Sekarang
                    →</a>
                <a href="index.php#search-section" class="brut-btn px-8 py-3.5 text-base font-medium">Cari Barang</a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Main Content & Filter -->
    <div class="max-w-6xl mx-auto px-4">
        <?php if (isLoggedIn()): ?>
            <!-- Filter Form -->
            <div id="search-section" class="brut-card p-6 mb-8">
                <h2 class="font-bebas text-2xl mb-4 text-slate-900">Cari & Filter Barang</h2>
                <form action="" method="GET" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block font-semibold text-xs mb-1 uppercase text-slate-600">Kata Kunci</label>
                        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>"
                            class="brut-input w-full text-sm" placeholder="Nama barang / lokasi..." autocomplete="off"
                            autocorrect="off" autocapitalize="off" spellcheck="false">
                    </div>
                    <div>
                        <label class="block font-semibold text-xs mb-1 uppercase text-slate-600">Kategori</label>
                        <select name="category" class="brut-input w-full text-sm">
                            <option value="">Semua Kategori</option>
                            <?php foreach (['Tumbler', 'Pakaian/Jaket', 'Barang Elektronik', 'Dompet/Kartu', 'Kunci', 'Lainnya'] as $cat): ?>
                                <option value="<?= $cat ?>" <?= $category === $cat ? 'selected' : '' ?>><?= $cat ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold text-xs mb-1 uppercase text-slate-600">Jenis Laporan</label>
                        <select name="type" class="brut-input w-full text-sm">
                            <option value="">Semua Jenis</option>
                            <option value="hilang" <?= $type === 'hilang' ? 'selected' : '' ?>>Barang Hilang</option>
                            <option value="ditemukan" <?= $type === 'ditemukan' ? 'selected' : '' ?>>Barang Ditemukan</option>
                        </select>
                    </div>
                    <div class="flex items-end gap-2">
                        <button type="submit"
                            class="brut-btn brut-btn-primary flex-1 py-2.5 font-bebas text-base">Cari</button>
                        <a href="index.php" class="brut-btn px-4 py-2.5 font-bebas text-base text-center">Atur Ulang</a>
                    </div>
                </form>
            </div>

            <!-- Items Grid -->
            <h2 class="font-bebas text-3xl mb-6 text-slate-900">Daftar Barang Hilang & Ditemukan
                (<?= count($filteredItems) ?>)</h2>

            <?php if (empty($filteredItems)): ?>
                <div class="brut-card p-12 text-center">
                    <p class="font-bebas text-2xl text-slate-800 mb-2">Tidak ada barang yang ditemukan.</p>
                    <p class="text-sm text-slate-600 font-medium">Coba ubah kata kunci pencarian atau filter kategori Anda.</p>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <?php foreach ($filteredItems as $it): ?>
                        <div class="brut-card flex flex-col justify-between overflow-hidden">
                            <div>
                                <?php if (!empty($it['image']) && file_exists('uploads/' . $it['image'])): ?>
                                    <img src="uploads/<?= htmlspecialchars($it['image']) ?>" alt="Foto"
                                        class="w-full h-48 object-cover border-b border-slate-200">
                                <?php else: ?>
                                    <div
                                        class="w-full h-48 bg-slate-100 border-b border-slate-200 flex items-center justify-center font-bold text-slate-400 text-sm">
                                        Tanpa Foto
                                    </div>
                                <?php endif; ?>

                                <div class="p-6">
                                    <div class="flex justify-between items-center mb-3">
                                        <span
                                            class="px-2.5 py-0.5 text-xs font-bold rounded-lg border <?= $it['type'] === 'hilang' ? 'bg-red-50 text-red-700 border-red-200' : 'bg-green-50 text-green-700 border-green-200' ?>">
                                            <?= strtoupper($it['type']) ?>
                                        </span>
                                        <span
                                            class="px-2.5 py-0.5 text-xs font-bold rounded-lg border bg-amber-50 text-amber-800 border-amber-200">
                                            <?= htmlspecialchars($it['status']) ?>
                                        </span>
                                    </div>

                                    <h3 class="font-bebas text-2xl text-slate-900 mb-1 leading-snug">
                                        <a href="detail.php?id=<?= $it['id'] ?>"
                                            class="hover:underline"><?= htmlspecialchars($it['title']) ?></a>
                                    </h3>

                                    <div class="text-xs text-slate-600 font-medium space-y-1 mb-4">
                                        <p><strong>Kategori:</strong> <?= htmlspecialchars($it['category']) ?></p>
                                        <p><strong>Lokasi:</strong> <?= htmlspecialchars($it['location']) ?></p>
                                        <p><strong>Penyimpanan:</strong>
                                            <?= htmlspecialchars($it['storage_location'] ?: 'Belum ditentukan') ?></p>
                                        <p><strong>Tanggal:</strong> <?= htmlspecialchars($it['date']) ?></p>
                                    </div>

                                    <p class="text-sm text-slate-700 line-clamp-2 mb-4"><?= htmlspecialchars($it['description']) ?>
                                    </p>
                                </div>
                            </div>

                            <div class="p-6 pt-0">
                                <a href="detail.php?id=<?= $it['id'] ?>"
                                    class="brut-btn block w-full py-2.5 text-center font-bebas text-base">Lihat Detail & Kontak
                                    Teams</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="brut-card p-12 text-center">
                <p class="font-bebas text-2xl text-slate-900 mb-2">Silakan Masuk Terlebih Dahulu</p>
                <p class="text-sm text-slate-600 font-medium mb-6">Anda harus masuk ke akun FindIt! UAJY untuk mencari dan
                    melihat daftar barang hilang atau ditemukan.</p>
                <div class="flex justify-center gap-4">
                    <a href="login.php" class="brut-btn brut-btn-primary px-6 py-2.5 font-bebas text-base">Masuk
                        Sekarang</a>
                    <a href="register.php" class="brut-btn px-6 py-2.5 font-bebas text-base">Daftar Akun</a>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="script.js"></script>
</body>

</html>