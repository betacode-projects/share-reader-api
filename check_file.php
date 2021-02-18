<?php

// ----- デフォルト設定
require_once './__default.php';
$json_list['data'] = ['flag' => false];


// ----- トークンチェック
if (!isset($_POST['token']) || strlen($_POST['token']) !== 64){
    show_errors($json_list, 'トークン形式が正しくありません。');
}


// ----- DBへアクセス
$link = @mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if (!$link) {
    show_errors($json_list, 'データベースからの応答がありません。しばらくたってからアクセスしてください。', 'DB');
}
mysqli_set_charset($link, 'utf8');


$check_sql = 'SELECT * FROM file_download WHERE recv_token = "'. esc($link, $_POST['token']) .'"';
$file_list = get_allrows($link, $check_sql);

if (count($file_list) <= 0){
    show_errors($json_list,'トークンと一致するファイルは見つかりませんでした。');
}
$sender_token = $file_list[0]['send_token'];


// ----- json出力
$json_list['data'] = ['token' => $sender_token];
show_success($json_list, 'ファイルが見つかりました。');