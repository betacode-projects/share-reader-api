<?php

// ----- デフォルト設定
require_once './__default.php';


// ----- パラメータチェック
if (!isset($_POST['status']) || $_POST['status'] !== 'reciever'){
    $json_list['post'] = $_POST;
    show_errors($json_list, '無効なステータスです。');
}


// ----- DBへ登録
$link = @mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if (!$link) {
    show_errors($json_list, 'データベースからの応答がありません。しばらくたってからアクセスしてください。');
}
mysqli_set_charset($link, 'utf8');

// ----- トークンチェック
$token = '';
$token_changed = true;
if (isset($_POST['token']) && strlen($_POST['token']) === 64){
    // DBからのリスト取得
    $token_check_sql = 'SELECT * FROM reciever WHERE recv_token = "'. esc($link, $_POST['token']). '" AND recv_flag = 1';
    $token_list = get_allrows($link, $token_check_sql);

    if (count($token_list) > 0){
        $token = $_POST['token'];
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
    $token_sql = create_insert_sql($link, 'reciever', [
        'recv_token' => $token,
        'recv_agent' => $_SERVER['HTTP_USER_AGENT'],
        'recv_ipaddr' => $_SERVER["REMOTE_ADDR"],
        'recv_flag' => 1
    ]);
    $result = mysqli_query($link, $token_sql);
    if (!$result)
        show_errors($json_list, 'データーベースへの登録に失敗しました。もう一度お試しください。', 'DB');

    mysqli_insert_id($link);
}

// ----- json出力
$json_list['data'] = ['token' => $token, 'token_changed' => $token_changed];
show_success($json_list, 'トークン生成に成功しました。');
