<?php
require_once 'functions.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$currentUser = getCurrentUser();
$error = '';
$success = '';

$editMode = false;
$itemType = $_GET['type'] ?? 'hilang';
$showSelector = !$editMode && !isset($_GET['type']);

$item = [
    'id' => '',
    'title' => '',
    'category' => 'Tumbler',
    'type' => $itemType,
    'location' => '',
    'storage_location' => ($itemType === 'ditemukan' ? 'Masih dibawa penemu' : ''),
    'date' => date('Y-m-d'),
    'description' => '',
    'image' => '',
    'status' => ($itemType === 'hilang' ? 'Masih Dicari' : 'Belum Dikembalikan')
];

if (isset($_GET['id'])) {
    $items = getItems();
    foreach ($items as $it) {
        if ($it['id'] === $_GET['id'] && $it['user_id'] === $currentUser['id']) {
            $item = $it;
            $editMode = true;
            break;
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $category = $_POST['category'] ?? 'Tumbler';
    $type = $_POST['type'] ?? 'hilang';
    $location = trim($_POST['location'] ?? '');
    $storage_location = trim($_POST['storage_location'] ?? ($type === 'hilang' ? 'Masih dicari' : 'Masih dibawa penemu'));
    $date = $_POST['date'] ?? date('Y-m-d');
    $description = trim($_POST['description'] ?? '');
    $status = $_POST['status'] ?? ($type === 'hilang' ? 'Masih Dicari' : 'Belum Dikembalikan');

    if (empty($title) || empty($location) || empty($date)) {
        $error = 'Judul, lokasi, dan tanggal wajib diisi!';
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

        $items = getItems();
        if ($editMode) {
            foreach ($items as &$it) {
                if ($it['id'] === $item['id']) {
                    $it['title'] = $title;
                    $it['category'] = $category;
                    $it['type'] = $type;
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
            $success = 'Laporan berhasil diperbarui!';
        } else {
            $newItem = [
                'id' => uniqid(),
                'user_id' => $currentUser['id'],
                'user_name' => $currentUser['name'],
                'user_teams' => $currentUser['teams'],
                'title' => $title,
                'category' => $category,
                'type' => $type,
                'location' => $location,
                'storage_location' => $storage_location,
                'date' => $date,
                'description' => $description,
                'image' => $imageName,
                'status' => $status,
                'created_at' => date('Y-m-d H:i:s')
            ];
            $items[] = $newItem;
            saveItems($items);
            $success = 'Laporan berhasil ditambahkan!';
        }
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
    <title><?= $editMode ? 'Edit' : 'Tambah' ?> Laporan - FindIt! UAJY</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="style.css">
</head>

<body class="bg-slate-50 min-h-screen pb-12">
    <!-- Navbar -->
    <nav
        class="backdrop-blur-md bg-white/90 border border-slate-200 sticky top-0 z-50 px-6 py-4 flex justify-between items-center shadow-md">
        <a href="index.php" class="font-bebas text-2xl tracking-tight text-slate-900">FindIt!® <span
                class="text-slate-700 font-sans text-xs">UAJY</span></a>
        <div class="hidden md:flex items-center gap-6 font-medium text-sm text-slate-600">
            <a href="index.php" class="hover:text-slate-900 transition">Beranda</a>
            <a href="index.php#search-section" class="hover:text-slate-900 transition">Cari Barang</a>
            <a href="report.php" class="hover:text-slate-900 transition text-slate-900 font-semibold">Lapor Barang</a>
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

    <div class="max-w-2xl mx-auto px-4 mt-8">
        <?php if ($showSelector): ?>
            <!-- Category / Type Selector Menu -->
            <div class="brut-card p-8 text-center">
                <h1 class="font-bebas text-4xl mb-3 text-slate-900">Pilih Jenis Laporan</h1>
                <p class="text-slate-600 text-sm mb-8">Pilih form yang sesuai dengan situasi Anda agar pengisian data lebih
                    cepat, logis, dan tepat.</p>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <a href="report.php?type=hilang"
                        class="brut-card p-6 text-left hover:border-slate-400 block transition group">
                        <div class="text-red-600 font-bold text-xs uppercase tracking-wider mb-1">Kehilangan Barang</div>
                        <h3 class="font-bebas text-2xl text-slate-900 mb-2 group-hover:underline">Lapor Barang Hilang</h3>
                        <p class="text-xs text-slate-600">Saya kehilangan barang pribadi di area kampus dan ingin meminta
                            bantuan pencarian.</p>
                    </a>

                    <a href="report.php?type=ditemukan"
                        class="brut-card p-6 text-left hover:border-slate-400 block transition group">
                        <div class="text-green-600 font-bold text-xs uppercase tracking-wider mb-1">Menemukan Barang</div>
                        <h3 class="font-bebas text-2xl text-slate-900 mb-2 group-hover:underline">Lapor Barang Ditemukan
                        </h3>
                        <p class="text-xs text-slate-600">Saya menemukan barang milik orang lain dan ingin melaporkan agar
                            pemilik atau CS mengetahuinya.</p>
                    </a>
                </div>
            </div>
        <?php else: ?>
            <!-- Dedicated Form -->
            <div class="brut-card p-8">
                <div class="flex justify-between items-center mb-6 border-b border-slate-200 pb-3">
                    <h1 class="font-bebas text-3xl text-slate-900">
                        <?= $editMode ? 'Edit Laporan Barang' : ($itemType === 'hilang' ? 'Form Laporan Barang Hilang' : 'Form Laporan Barang Ditemukan') ?>
                    </h1>
                    <?php if (!$editMode): ?>
                        <a href="report.php" class="back-btn" style="padding: 6px 14px; font-size: 0.75rem;">&larr; Ganti Jenis</a>
                    <?php endif; ?>
                </div>

                <?php if ($error): ?>
                    <div class="bg-red-50 border border-red-200 text-red-700 p-3 mb-4 rounded-xl font-semibold text-sm">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <form action="" method="POST" enctype="multipart/form-data" class="space-y-4">
                    <input type="hidden" name="type" value="<?= htmlspecialchars($itemType) ?>">

                    <div>
                        <label class="block font-semibold text-xs mb-1 uppercase text-slate-700">Nama Barang</label>
                        <input type="text" name="title" value="<?= htmlspecialchars($item['title']) ?>" required
                            class="brut-input w-full" placeholder="Contoh: Tumbler Tupperware Hitam / Dompet Coklat"
                            autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-semibold text-xs mb-1 uppercase text-slate-700">Kategori</label>
                            <select name="category" class="brut-input w-full">
                                <?php foreach (['Tumbler', 'Pakaian/Jaket', 'Barang Elektronik', 'Dompet/Kartu', 'Kunci', 'Lainnya'] as $cat): ?>
                                    <option value="<?= $cat ?>" <?= $item['category'] === $cat ? 'selected' : '' ?>><?= $cat ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block font-semibold text-xs mb-1 uppercase text-slate-700">
                                <?= $itemType === 'hilang' ? 'Tanggal Kehilangan' : 'Tanggal Ditemukan' ?>
                            </label>
                            <input type="date" name="date" value="<?= htmlspecialchars($item['date']) ?>" required
                                class="brut-input w-full">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-semibold text-xs mb-1 uppercase text-slate-700">
                                <?= $itemType === 'hilang' ? 'Lokasi Terakhir Hilang' : 'Lokasi Barang Ditemukan' ?>
                            </label>
                            <input type="text" name="location" value="<?= htmlspecialchars($item['location']) ?>" required
                                class="brut-input w-full" placeholder="Contoh: Kantin Kampus 2 / Parkiran Kampus 3"
                                autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false">
                        </div>
                        <div>
                            <?php if ($itemType === 'hilang'): ?>
                                <input type="hidden" name="storage_location" value="Masih dicari pemilik">
                                <label class="block font-semibold text-xs mb-1 uppercase text-slate-700">Status Awal</label>
                                <input type="text" disabled value="Masih Dicari"
                                    class="brut-input w-full bg-slate-100 text-slate-500 cursor-not-allowed">
                                <input type="hidden" name="status" value="Masih Dicari">
                            <?php else: ?>
                                <label class="block font-semibold text-xs mb-1 uppercase text-slate-700">Lokasi Penyimpanan
                                    Sekarang</label>
                                <input type="text" name="storage_location"
                                    value="<?= htmlspecialchars($item['storage_location']) ?>" required
                                    class="brut-input w-full"
                                    placeholder="Contoh: Masih dibawa penemu / CS Gedung Thomas Aquinas" autocomplete="off"
                                    autocorrect="off" autocapitalize="off" spellcheck="false">
                                <input type="hidden" name="status" value="Belum Dikembalikan">
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if ($editMode): ?>
                        <div>
                            <label class="block font-semibold text-xs mb-1 uppercase text-slate-700">Status Laporan</label>
                            <select name="status" class="brut-input w-full">
                                <?php if ($itemType === 'hilang'): ?>
                                    <option value="Masih Dicari" <?= $item['status'] === 'Masih Dicari' ? 'selected' : '' ?>>Masih
                                        Dicari</option>
                                    <option value="Sudah Ditemukan" <?= $item['status'] === 'Sudah Ditemukan' ? 'selected' : '' ?>>
                                        Sudah Ditemukan</option>
                                <?php else: ?>
                                    <option value="Belum Dikembalikan" <?= $item['status'] === 'Belum Dikembalikan' ? 'selected' : '' ?>>Belum Dikembalikan</option>
                                    <option value="Sudah Dikembalikan" <?= $item['status'] === 'Sudah Dikembalikan' ? 'selected' : '' ?>>Sudah Dikembalikan</option>
                                <?php endif; ?>
                            </select>
                        </div>
                    <?php endif; ?>

                    <div>
                        <label class="block font-semibold text-xs mb-1 uppercase text-slate-700">Foto Barang
                            (Opsional)</label>
                        <?php if (!empty($item['image'])): ?>
                            <div class="mb-2">
                                <img src="uploads/<?= htmlspecialchars($item['image']) ?>" alt="Foto"
                                    class="h-24 w-24 object-cover rounded-xl border border-slate-200">
                            </div>
                        <?php endif; ?>
                        <input type="file" name="image"
                            class="brut-input w-full text-sm file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-slate-900 file:text-white hover:file:bg-slate-800">
                    </div>

                    <div>
                        <label class="block font-semibold text-xs mb-1 uppercase text-slate-700">Deskripsi & Ciri-Ciri
                            Khusus</label>
                        <textarea name="description" rows="4" class="brut-input w-full"
                            placeholder="Tuliskan ciri-ciri khusus, warna, stiker, atau detail penting lainnya..."
                            autocomplete="off" autocorrect="off" autocapitalize="off"
                            spellcheck="false"><?= htmlspecialchars($item['description']) ?></textarea>
                    </div>

                    <div class="flex gap-4 pt-4">
                        <button type="submit" class="brut-btn brut-btn-primary flex-1 py-3 text-base font-bebas">Simpan
                            Laporan</button>
                        <a href="profile.php" class="brut-btn px-6 py-3 text-base font-bebas text-center">Batal</a>
                    </div>
                </form>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function updateStatusOptions() {
            const type = document.getElementById('reportType').value;
            const statusSelect = document.getElementById('reportStatus');
            statusSelect.innerHTML = '';
            if (type === 'hilang') {
                statusSelect.innerHTML = `
                <option value="Masih Dicari">Masih Dicari</option>
                <option value="Sudah Ditemukan">Sudah Ditemukan</option>
            `;
            } else {
                statusSelect.innerHTML = `
                <option value="Belum Dikembalikan">Belum Dikembalikan</option>
                <option value="Sudah Dikembalikan">Sudah Dikembalikan</option>
            `;
            }
        }
    </script>
    <script src="script.js"></script>
</body>

</html>