<?php

// ----- デフォルト設定
require_once './__default.php';


// ----- パラメータチェック
if (!isset($_POST['status']) || ($_POST['status'] !== 'reciever' && $_POST['status'] !== 'sender')){
    $json_list['post'] = $_POST;
    show_errors($json_list, '無効なステータスです。');
}

if ($_POST['status'] === 'sender' && !isset($_POST['file_id'])){
    show_errors($json_list, 'ファイルIDが設定されていません。');
}


// ----- DBへ登録
$link = @mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if (!$link) {
    show_errors($json_list, 'データベースからの応答がありません。しばらくたってからアクセスしてください。');
}
mysqli_set_charset($link, 'utf8');

// トークンチェック
$token = '';
$token_changed = true;
if (isset($_POST['token']) && strlen($_POST['token']) === 64){
    if ($_POST['status'] === 'sender')
        $token_check_sql = 'SELECT * FROM sender WHERE send_token = "'. esc($link, $_POST['token']) .'" AND send_flag = 1';
    else
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


// DB追加処理
if ($token_changed){

    if ($_POST['status'] === 'sender'){
        $token_list = [
            'file_id' => $_POST['file_id'],
            'send_token' => $token,
            'send_agent' => $_SERVER['HTTP_USER_AGENT'],
            'send_flag' => 1
        ];
    }
    else{
        $token_list = [
            'recv_token' => $token,
            'recv_agent' => $_SERVER['HTTP_USER_AGENT'],
            'recv_flag' => 1
        ];
    }

    $token_sql = create_insert_sql($link, $_POST['status'], $token_list);
    $result = mysqli_query($link, $token_sql);
    if (!$result)
        show_errors($json_list, $token_sql, 'DB');

    mysqli_insert_id($link);
}

// ----- json出力
$json_list['data'] = ['token' => $token, 'token_changed' => $token_changed];
show_success($json_list, 'トークン生成に成功しました。');
