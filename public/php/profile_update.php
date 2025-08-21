<?php
header('Content-Type: application/json');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['status'=>'error','message'=>'Method not allowed']); exit;
}

require __DIR__ . '/config.php';

$session  = $_POST['session']  ?? '';
$fullName = trim($_POST['fullName'] ?? '');
$dob      = $_POST['dob']      ?? '';
$age      = $_POST['age']      ?? '';
$phone    = trim($_POST['phone'] ?? '');
$about    = trim($_POST['about'] ?? '');

if ($session === '') {
  echo json_encode(['status'=>'error','code'=>'AUTH','message'=>'Missing session']); exit;
}
if (strlen($fullName) < 2) {
  echo json_encode(['status'=>'error','message'=>'Full name too short']); exit;
}

try {
  
  $r = getRedis();
  $uid = $r->get($session);
  if (!$uid) {
    echo json_encode(['status'=>'error','code'=>'AUTH','message'=>'Invalid session']); exit;
  }

  
  $ageNum = is_numeric($age) ? (int)$age : null;

  // Upserting 
  $mongo = getMongo();
  $db = $mongo->selectDatabase('guvi_app');         
  $col = $db->selectCollection('profiles');         

  $update = [
    'fullName' => $fullName,
    'dob'      => $dob,
    'age'      => $ageNum,
    'phone'    => $phone,
    'about'    => $about,
  ];

  $col->updateOne(
    ['userId' => (int)$uid],
    ['$set' => $update],
    ['upsert' => true]
  );

  echo json_encode(['status'=>'ok']); exit;

} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode([
    'status'=>'error',
    'message'=>'Server error while saving profile',
    //'debug'=>$e->getMessage() 
  ]);
  exit;
}
