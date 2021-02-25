<?php

require_once './__default.php';

// ----- トークンチェック
if (!isset($_POST['send_secret_token']) || strlen($_POST['send_secret_token']) !== 64){
    show_errors($json_list, '送信者トークン形式が正しくありません。');
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
$filecount_sql = 'SELECT * FROM file_download JOIN reciever ON reciever.recv_token = file_download.recv_token WHERE send_token = "'. esc($link, $sender_token) .'" ORDER BY file_downloaded';
$download_list = get_allrows($link, $filecount_sql);


// ファイル情報送信
$json_list['data'] = ['download_list' => $download_list];
show_success($json_list, 'ファイルダウンロードリストの取得に成功しました。');
