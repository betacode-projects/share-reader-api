<?php

// ----- デフォルト設定
require_once './__default.php';


// ----- トークンチェック
if (!isset($_POST['recv_secret_token']) || strlen($_POST['recv_secret_token']) !== 64){
    show_errors($json_list, 'トークン形式が正しくありません。');
}


// ----- DBへアクセス
$link = @mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if (!$link) {
    show_errors($json_list, 'データベースからの応答がありません。しばらくたってからアクセスしてください。', 'DB');
}
mysqli_set_charset($link, 'utf8');

$recv_token = get_reciever_secret2token($link, $json_list, $_POST['recv_secret_token']);


$check_sql = 'SELECT * FROM qr_read_list WHERE recv_token = "'. esc($link, $recv_token) .'"';
$check_list = get_allrows($link, $check_sql);

if (count($check_list) <= 0){
    show_errors($json_list,'トークンと一致するファイルは見つかりませんでした。');
}
$sender_token = $check_list[0]['send_token'];


// ----- json出力
$json_list['data'] = ['token' => $sender_token];
show_success($json_list, 'ファイルが見つかりました。');