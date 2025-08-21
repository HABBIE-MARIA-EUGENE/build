<?php

header('Content-Type: application/json');


const DEBUG = true;

require __DIR__ . '/config.php';


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['status'=>'error','message'=>'Method not allowed']);
  exit;
}


$email = trim($_POST['email']?? '');  
$pwd = $_POST['password']?? '';



//validation checking block will be added later



try {
    $q = $pdo->prepare("SELECT 1 FROM users WHERE email = ?");
    $q->execute([$email]);
    if($q->fetch()) {
        echo json_encode([
            'status'=>'error',
            'message'=>'Email exists'
        ]);
        exit;
    }


    $hash = password_hash($pwd, PASSWORD_DEFAULT);

    //PREP STMT to insert

    $ins = $pdo->prepare("INSERT INTO users (email, password)VALUES(?,?)");
    $ins->execute([$email, $hash]);


    echo json_encode([
        'status'=>'ok',
        'message'=>'Registered successfully'
    ]);


} catch (Throwable $e) {
    
    http_response_code(500);

    echo json_encode([
        'status'=>'error',
        'message'=> DEBUG ? $e->getMessage() : 'Server error !!!'
    ]);
}