<?php
header('Content-Type: application/json');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['status'=>'error','message'=>'Method not allowed']); exit;
}

require __DIR__ . '/config.php';

$session = $_POST['session'] ?? '';
if ($session === '') {
  echo json_encode(['status'=>'error','code'=>'AUTH','message'=>'Missing session']); exit;
}

try {
  
  $r = getRedis();
  $uid = $r->get($session);
  if (!$uid) {
    echo json_encode(['status'=>'error','code'=>'AUTH','message'=>'Invalid session']); exit;
  }

  //  fetching profile using userId frm mdb
  $mongo = getMongo();
  $db = $mongo->selectDatabase('guvi_app');        
  $col = $db->selectCollection('profiles');        

  $doc = $col->findOne(['userId' => (int)$uid], [
    'projection' => ['_id'=>0,'userId'=>0]
  ]);

  $profile = $doc ?: [
    'fullName'=>'','dob'=>'','age'=>'','phone'=>'','about'=>''
  ];

  echo json_encode(['status'=>'ok','profile'=>$profile]); exit;

} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode([
    'status'=>'error',
    'message'=>'Server error while fetching profile',
    //'debug'=>$e->getMessage()       
  ]);
  exit;
}
