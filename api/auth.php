<?php
header('Content-Type: application/json');
require_once '../functions.php';

$action = $_GET['action'] ?? '';

if ($action === 'session') {
    if (isLoggedIn()) {
        echo json_encode(['status' => 'success', 'logged_in' => true, 'user' => getCurrentUser()]);
    } else {
        echo json_encode(['status' => 'success', 'logged_in' => false]);
    }
    exit;
}

if ($action === 'logout') {
    session_destroy();
    echo json_encode(['status' => 'success', 'message' => 'Berhasil keluar']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

    if ($action === 'login') {
        $email = trim($input['email'] ?? '');
        $password = $input['password'] ?? '';

        if (empty($email) || empty($password)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Email dan password wajib diisi']);
            exit;
        }

        $users = getUsers();
        foreach ($users as $u) {
            if ($u['email'] === $email && password_verify($password, $u['password'])) {
                $_SESSION['user'] = $u;
                echo json_encode(['status' => 'success', 'message' => 'Login berhasil', 'user' => $u]);
                exit;
            }
        }

        http_response_code(401);
        echo json_encode(['status' => 'error', 'message' => 'Email atau password salah']);
        exit;
    }

    if ($action === 'register') {
        $name = trim($input['name'] ?? '');
        $email = trim($input['email'] ?? '');
        $password = $input['password'] ?? '';
        $teams = trim($input['teams'] ?? '');

        if (empty($name) || empty($email) || empty($password)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Semua field wajib diisi']);
            exit;
        }

        $users = getUsers();
        foreach ($users as $u) {
            if ($u['email'] === $email) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Email sudah terdaftar']);
                exit;
            }
        }

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

        echo json_encode(['status' => 'success', 'message' => 'Registrasi berhasil', 'user' => $newUser]);
        exit;
    }
}

http_response_code(400);
echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
?>