<?php
header('Content-Type: application/json');
require_once '../functions.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $items = getItems();
    
    $search = trim($_GET['search'] ?? '');
    $category = $_GET['category'] ?? '';
    $type = $_GET['type'] ?? '';
    $status = $_GET['status'] ?? '';
    $user_id = $_GET['user_id'] ?? '';

    $filteredItems = array_filter($items, function($it) use ($search, $category, $type, $status, $user_id) {
        if (!empty($user_id) && $it['user_id'] !== $user_id) return false;
        if (!empty($search)) {
            $q = strtolower($search);
            $matchTitle = strpos(strtolower($it['title']), $q) !== false;
            $matchDesc = strpos(strtolower($it['description']), $q) !== false;
            $matchLoc = strpos(strtolower($it['location']), $q) !== false;
            if (!$matchTitle && !$matchDesc && !$matchLoc) return false;
        }
        if (!empty($category) && $it['category'] !== $category) return false;
        if (!empty($type) && $it['type'] !== $type) return false;
        if (!empty($status) && $it['status'] !== $status) return false;
        return true;
    });

    usort($filteredItems, function($a, $b) {
        return strcmp($b['created_at'] ?? '', $a['created_at'] ?? '');
    });

    echo json_encode(['status' => 'success', 'data' => array_values($filteredItems)]);
    exit;
}

if ($method === 'POST') {
    if (!isLoggedIn()) {
        http_response_code(401);
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
        exit;
    }

    $currentUser = getCurrentUser();
    $input = $_POST;
    
    $id = $input['id'] ?? '';
    $title = trim($input['title'] ?? '');
    $category = $input['category'] ?? 'Tumbler';
    $type = $input['type'] ?? 'hilang';
    $location = trim($input['location'] ?? '');
    $storage_location = trim($input['storage_location'] ?? ($type === 'hilang' ? 'Masih dicari' : 'Masih dibawa penemu'));
    $date = $input['date'] ?? date('Y-m-d');
    $description = trim($input['description'] ?? '');
    $status = $input['status'] ?? ($type === 'hilang' ? 'Masih Dicari' : 'Belum Dikembalikan');

    if (empty($title) || empty($location) || empty($date)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Nama barang, lokasi, dan tanggal wajib diisi']);
        exit;
    }

    $items = getItems();
    $imageName = '';

    // Handle file upload if present
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $imageName = 'item_' . time() . '_' . mt_rand(1000, 9999) . '.' . $ext;
        $uploadDir = '../uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $imageName);
    }

    if (!empty($id)) {
        // Update
        $updated = false;
        foreach ($items as &$it) {
            if ($it['id'] === $id && $it['user_id'] === $currentUser['id']) {
                $it['title'] = $title;
                $it['category'] = $category;
                $it['type'] = $type;
                $it['location'] = $location;
                $it['storage_location'] = $storage_location;
                $it['date'] = $date;
                $it['description'] = $description;
                if (!empty($imageName)) {
                    $it['image'] = $imageName;
                }
                $it['status'] = $status;
                $updated = true;
                break;
            }
        }
        unset($it);
        if ($updated) {
            saveItems($items);
            echo json_encode(['status' => 'success', 'message' => 'Laporan berhasil diperbarui']);
        } else {
            http_response_code(403);
            echo json_encode(['status' => 'error', 'message' => 'Forbidden or not found']);
        }
    } else {
        // Create
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
        echo json_encode(['status' => 'success', 'message' => 'Laporan berhasil ditambahkan', 'data' => $newItem]);
    }
    exit;
}

if ($method === 'DELETE') {
    if (!isLoggedIn()) {
        http_response_code(401);
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
        exit;
    }

    $currentUser = getCurrentUser();
    parse_str(file_get_contents("php://input"), $_DELETE);
    $deleteId = $_GET['id'] ?? $_DELETE['id'] ?? '';

    if (empty($deleteId)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'ID tidak valid']);
        exit;
    }

    $items = getItems();
    $initialCount = count($items);
    $items = array_filter($items, function($it) use ($deleteId, $currentUser) {
        return !($it['id'] === $deleteId && $it['user_id'] === $currentUser['id']);
    });

    if (count($items) < $initialCount) {
        saveItems(array_values($items));
        echo json_encode(['status' => 'success', 'message' => 'Laporan berhasil dihapus']);
    } else {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Item tidak ditemukan atau tidak diizinkan']);
    }
    exit;
}
?>