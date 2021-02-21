<?php

// ----- デフォルト設定
require_once './__default.php';
$json_list['data'] = ['flag' => false];


// ----- DBへ登録
$link = @mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if (!$link) {
    show_errors($json_list, 'データベースからの応答がありません。しばらくたってからアクセスしてください。');
}
mysqli_set_charset($link, 'utf8');

// ----- トークンチェック
if (!isset($_POST['token']) || strlen($_POST['token']) !== 64){
    show_errors($json_list, 'トークン形式が正しくありません。');
}


// DBからのリスト取得
$token_check_sql = 'SELECT * FROM reciever WHERE recv_token = "'. esc($link, $_POST['token']). '" AND recv_flag = 1';
$token_list = get_allrows($link, $token_check_sql);

if (count($token_list) <= 0){
    show_errors($json_list, 'トークンが存在しません。');
}


$json_list['data'] = ['flag' => true];
show_success($json_list, '受信者トークン照合に成功しました。');
