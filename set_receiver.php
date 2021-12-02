<?php

// ----- デフォルト設定
require_once './__default.php';


// ----- DBへ登録
$link = @mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if (!$link) {
    show_errors($json_list, 'データベースからの応答がありません。しばらくたってからアクセスしてください。');
}
mysqli_set_charset($link, 'utf8');

// ----- トークンチェック
$secret_token = create_uuid();
$token = '';
$token_changed = true;
if (isset($_POST['token']) && strlen($_POST['token']) === 64 && isset($_POST['secret_token']) && strlen($_POST['secret_token']) === 64){
    // DBからのリスト取得
    $token_check_sql = 'SELECT * FROM receiver WHERE recv_token = "'. esc($link, $_POST['token']). '" AND recv_secret_token = "'. esc($link, $_POST['secret_token']) .'" AND recv_flag = 1';
    $token_list = get_allrows($link, $token_check_sql);

    if (count($token_list) > 0){
        $token = $_POST['token'];
        $secret_token = $_POST['secret_token'];
        $token_changed = false;
    }
    else{
        $token = create_uuid();
    }
}
else{
    $token = create_uuid();
}

// トークン追加処理
if ($token_changed){
    // SQL生成
    $token_sql = create_insert_sql($link, 'receiver', [
        'recv_token' => $token,
        'recv_agent' => $_SERVER['HTTP_USER_AGENT'],
        'recv_ipaddr' => $_SERVER["REMOTE_ADDR"],
        'recv_secret_token' => $secret_token,
        'recv_flag' => 1
    ]);
    $result = mysqli_query($link, $token_sql);
    if (!$result)
        show_errors($json_list, 'データーベースへの登録に失敗しました。もう一度お試しください。', 'DB');
}

// ----- json出力
$json_list['data'] = ['token' => $token, 'secret_token' => $secret_token, 'token_changed' => $token_changed];
show_success($json_list, 'トークン生成に成功しました。');
