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


// ----- トークンの削除
$token_remove_sql = create_update_sql($link, 'sender', ['send_flag' => 0], '"'.esc($link, $send_token) .'"', 'send_token');
$result = mysqli_query($link, $token_remove_sql);

if (!$result){
    show_errors($json_list, 'トークンが存在しません');
}

$token_remove_sql = create_update_sql($link, 'file_info', ['file_flag' => 2], '"'.esc($link, $send_token) .'"', 'send_token');
$result = mysqli_query($link, $token_remove_sql);

if (!$result){
    show_errors($json_list, 'トークンが存在しません');
}


$json_list['data'] = ['flag' => true];
show_success($json_list, '送信者のトークンを削除しました。');
