<?php

require_once './__default.php';

// ----- トークンチェック
if (!isset($_POST['send_secret_token']) || strlen($_POST['send_secret_token']) !== 64){
    show_errors($json_list, '送信者トークン形式が正しくありません。');
}

if (!isset($_POST['offset']) || !is_numeric($_POST['offset'])){
    show_errors($json_list, 'オフセットが宣言されていません。');
}

// ----- DBへアクセス
$link = @mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if (!$link) {
    show_errors($json_list, 'データベースからの応答がありません。しばらくたってからアクセスしてください。', 'DB');
}
mysqli_set_charset($link, 'utf8');


// 受信者トークン・秘密トークンチェック
$sender_token = get_sender_secret2token($link, $json_list, $_POST['send_secret_token']);


// ダウンロード数取得
$filecount_sql = 'SELECT * FROM qr_read_list JOIN receiver ON receiver.recv_token = qr_read_list.recv_token WHERE send_token = "'. esc($link, $sender_token) .'" ORDER BY qr_read_date';
$reader_list_tmp = get_allrows($link, $filecount_sql);
$reader_list = array_slice($reader_list_tmp, $_POST['offset']);

// ファイル情報送信
$json_list['data'] = ['reader_list' => $reader_list];
show_success($json_list, 'QRコード読み取りリストの取得に成功しました。');
