<?php

// ----- デフォルト設定
require_once './__default.php';
$json_list['data'] = ['flag' => false];


// ----- トークンチェック
if (!isset($_POST['recv_secret_token']) || strlen($_POST['recv_secret_token']) !== 64){
    show_errors($json_list, 'トークン形式が正しくありません。');
}


// ----- DBへ登録
$link = @mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if (!$link) {
    show_errors($json_list, 'データベースからの応答がありません。しばらくたってからアクセスしてください。');
}
mysqli_set_charset($link, 'utf8');

$recv_token = get_receiver_secret2token($link, $json_list, $_POST['recv_secret_token']);


// ----- トークンの削除
$token_remove_sql = create_update_sql($link, 'receiver', ['recv_flag' => 0], '"'.esc($link, $recv_token) .'"', 'recv_token');
$result = mysqli_query($link, $token_remove_sql);

if (!$result){
    show_errors($json_list, 'トークンが存在しません');
}


$json_list['data'] = ['flag' => true];
show_success($json_list, '受信者のトークンを削除しました。');
