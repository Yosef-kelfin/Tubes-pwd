<?php
require_once 'functions.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$currentUser = getCurrentUser();
$error = '';
$success = '';

$itemId = $_GET['id'] ?? '';
$items = getItems();
$item = null;

foreach ($items as $it) {
    if ($it['id'] === $itemId && $it['user_id'] === $currentUser['id']) {
        $item = $it;
        break;
    }
}

if (!$item) {
    header('Location: profile.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $category = $_POST['category'] ?? 'Tumbler';
    $location = trim($_POST['location'] ?? '');
    $storage_location = trim($_POST['storage_location'] ?? ($item['type'] === 'hilang' ? 'Masih dicari' : 'Masih dibawa penemu'));
    $date = $_POST['date'] ?? date('Y-m-d');
    $description = trim($_POST['description'] ?? '');
    $status = $_POST['status'] ?? $item['status'];

    if (empty($title) || empty($location) || empty($date)) {
        $error = 'Nama barang, lokasi, dan tanggal wajib diisi!';
    } else {
        $imageName = $item['image'];
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $imageName = 'item_' . time() . '_' . mt_rand(1000, 9999) . '.' . $ext;
            $uploadDir = 'uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $imageName);
        }

        foreach ($items as &$it) {
            if ($it['id'] === $item['id']) {
                $it['title'] = $title;
                $it['category'] = $category;
                $it['location'] = $location;
                $it['storage_location'] = $storage_location;
                $it['date'] = $date;
                $it['description'] = $description;
                $it['image'] = $imageName;
                $it['status'] = $status;
            }
        }
        unset($it);
        saveItems($items);
        header('Location: profile.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Laporan - FindIt! UAJY</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="style.css">
</head>
<body class="bg-slate-50 min-h-screen pb-16">
    <!-- Navbar -->
    <div class="max-w-5xl mx-auto px-4 pt-4 sticky top-4 z-50">
        <nav class="backdrop-blur-md bg-white/90 border border-slate-200 px-6 py-3 rounded-full flex justify-between items-center shadow-md">
            <a href="index.php" class="font-bebas text-2xl tracking-tight text-slate-900 flex items-center gap-2">
                <span>FindIt!®</span> <span class="bg-slate-100 border border-slate-200 px-2 py-0.5 text-xs rounded-md text-slate-700 font-sans">UAJY</span>
            </a>
            <div class="hidden md:flex items-center gap-6 font-medium text-sm text-slate-600">
                <a href="index.php" class="hover:text-slate-900 transition">Beranda</a>
                <?php if (isLoggedIn()): ?>
                    <a href="index.php#search-section" class="hover:text-slate-900 transition">Cari Barang</a>
                    <a href="report.php" class="hover:text-slate-900 transition">Lapor Barang</a>
                    <a href="profile.php" class="hover:text-slate-900 transition text-slate-900 font-semibold">Dashboard Saya</a>
                <?php endif; ?>
            </div>
            <div class="flex items-center gap-3">
                <a href="profile.php" class="brut-btn px-4 py-2 text-sm hidden sm:inline-flex">Dashboard</a>
                <a href="logout.php" class="brut-btn brut-btn-danger px-4 py-2 text-sm">Keluar</a>
            </div>
        </nav>
    </div>

    <div class="max-w-2xl mx-auto px-4 mt-8">
        <a href="profile.php" class="back-btn mb-4">&larr; Kembali ke Dashboard</a>

        <div class="brut-card p-8 bg-white shadow-sm">
            <div class="flex justify-between items-center mb-6 border-b border-slate-200 pb-3">
                <h1 class="font-bebas text-3xl text-slate-900">Edit Laporan Barang</h1>
                <span class="px-3 py-1 text-xs font-bold rounded-xl border <?= $item['type'] === 'hilang' ? 'bg-red-50 text-red-700 border-red-200' : 'bg-green-50 text-green-700 border-green-200' ?>">
                    <?= strtoupper($item['type']) ?>
                </span>
            </div>

            <?php if ($error): ?>
                <div class="bg-red-50 border border-red-200 text-red-700 p-3 mb-4 rounded-xl font-semibold text-sm">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form action="" method="POST" enctype="multipart/form-data" class="space-y-4">
                <div>
                    <label class="block font-semibold text-xs mb-1 uppercase text-slate-700">Nama Barang</label>
                    <input type="text" name="title" value="<?= htmlspecialchars($item['title']) ?>" required class="brut-input w-full" placeholder="Contoh: Tumbler Tupperware Hitam" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-semibold text-xs mb-1 uppercase text-slate-700">Kategori</label>
                        <select name="category" class="brut-input w-full">
                            <?php foreach(['Tumbler', 'Pakaian/Jaket', 'Barang Elektronik', 'Dompet/Kartu', 'Kunci', 'Lainnya'] as $cat): ?>
                                <option value="<?= $cat ?>" <?= $item['category'] === $cat ? 'selected' : '' ?>><?= $cat ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold text-xs mb-1 uppercase text-slate-700">Tanggal Kejadian</label>
                        <input type="date" name="date" value="<?= htmlspecialchars($item['date']) ?>" required class="brut-input w-full">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-semibold text-xs mb-1 uppercase text-slate-700">Lokasi Kejadian</label>
                        <input type="text" name="location" value="<?= htmlspecialchars($item['location']) ?>" required class="brut-input w-full" placeholder="Contoh: Kantin Kampus 2" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false">
                    </div>
                    <div>
                        <label class="block font-semibold text-xs mb-1 uppercase text-slate-700">Lokasi Penyimpanan</label>
                        <input type="text" name="storage_location" value="<?= htmlspecialchars($item['storage_location']) ?>" required class="brut-input w-full" placeholder="Contoh: CS Gedung Thomas Aquinas" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false">
                    </div>
                </div>

                <div>
                    <label class="block font-semibold text-xs mb-1 uppercase text-slate-700">Status Laporan</label>
                    <select name="status" class="brut-input w-full">
                        <?php if ($item['type'] === 'hilang'): ?>
                            <option value="Masih Dicari" <?= $item['status'] === 'Masih Dicari' ? 'selected' : '' ?>>Masih Dicari</option>
                            <option value="Sudah Ditemukan" <?= $item['status'] === 'Sudah Ditemukan' ? 'selected' : '' ?>>Sudah Ditemukan</option>
                        <?php else: ?>
                            <option value="Belum Dikembalikan" <?= $item['status'] === 'Belum Dikembalikan' ? 'selected' : '' ?>>Belum Dikembalikan</option>
                            <option value="Sudah Dikembalikan" <?= $item['status'] === 'Sudah Dikembalikan' ? 'selected' : '' ?>>Sudah Dikembalikan</option>
                        <?php endif; ?>
                    </select>
                </div>

                <div>
                    <label class="block font-semibold text-xs mb-1 uppercase text-slate-700">Foto Barang (Opsional)</label>
                    <?php if (!empty($item['image'])): ?>
                        <div class="mb-2">
                            <img src="uploads/<?= htmlspecialchars($item['image']) ?>" alt="Foto" class="h-24 w-24 object-cover rounded-xl border border-slate-200">
                        </div>
                    <?php endif; ?>
                    <input type="file" name="image" class="brut-input w-full text-sm file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-slate-900 file:text-white hover:file:bg-slate-800">
                </div>

                <div>
                    <label class="block font-semibold text-xs mb-1 uppercase text-slate-700">Deskripsi & Ciri-Ciri Khusus</label>
                    <textarea name="description" rows="4" class="brut-input w-full" placeholder="Tuliskan ciri-ciri khusus..." autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false"><?= htmlspecialchars($item['description']) ?></textarea>
                </div>

                <div class="flex gap-4 pt-4">
                    <button type="submit" class="brut-btn brut-btn-primary flex-1 py-3 text-base font-bebas">Simpan Perubahan</button>
                    <a href="profile.php" class="brut-btn px-6 py-3 text-base font-bebas text-center">Batal</a>
                </div>
            </form>
        </div>
    </div>
    <script src="script.js"></script>
</body>
</html>
