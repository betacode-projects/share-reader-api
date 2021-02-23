<?php

// ----- デフォルト設定
require_once './__default.php';
$json_list['data'] = ['flag' => false];


// ----- トークンチェック
if (!isset($_POST['send_secret_token']) || strlen($_POST['send_secret_token']) !== 64){
    show_errors($json_list, 'トークン形式が正しくありません。');
}


// ----- DBへ登録
$link = @mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if (!$link) {
    show_errors($json_list, 'データベースからの応答がありません。しばらくたってからアクセスしてください。');
}
mysqli_set_charset($link, 'utf8');

$send_token = get_sender_secret2token($link, $json_list, $_POST['send_secret_token']);


// DBからのリスト取得
$token_check_sql = 'SELECT * FROM sender WHERE send_token = "'. esc($link, $send_token). '" AND send_flag = 1';
$token_list = get_allrows($link, $token_check_sql);

if (count($token_list) <= 0){
    show_errors($json_list, 'トークンが存在しません。');
}


$json_list['data'] = ['flag' => true];
show_success($json_list, '送信者トークン照合に成功しました。');
