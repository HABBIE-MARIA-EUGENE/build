<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

require __DIR__ . '/config.php';


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['status'=>'error','message'=>'Method not allowed']);
  exit;
}

$email = trim($_POST['email']?? '');
$pwd = $_POST['password']?? '';

// need to add validation

try {
    $stmt = $pdo->prepare('SELECT id, password FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($pwd, $user['password'])) {
        echo json_encode([
            'status'=>'error',
            'message'=>'invalid creds'
        ]);
        exit;
    }

    $sessionId = 'sess_' . bin2hex(random_bytes(16));

    $redis = getRedis();

    $redis->setex($sessionId, 3600, (string)$user['id']);

    echo json_encode([
        'status' => 'ok',
        'session' => $sessionId,
        'userId' => (int)$user['id']
    ]);
    exit;

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Server problem during login'
    ]);
    exit;
}

