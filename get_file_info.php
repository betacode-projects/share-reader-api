<?php

require_once './__default.php';


// ----- 送信者トークン取得
if (!isset($_POST['send_token']) || strlen($_POST['send_token']) !== 64){
    show_errors($json_list, 'トークン形式が正しくありません。', '404');
}


// ----- DBへアクセス
$link = @mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if (!$link) {
    show_errors($json_list, 'データベースからの応答がありません。しばらくたってからアクセスしてください。', 'DB');
}
mysqli_set_charset($link, 'utf8');


// ファイル情報テーブルにアクセス
$filecheck_sql = 'SELECT * FROM file_info WHERE send_token = "'. esc($link, $_POST['send_token']) .'" AND file_flag = 1';
$file_list = get_allrows($link, $filecheck_sql);

if (count($file_list) <= 0){
    show_errors($json_list, 'ファイル情報が見つかりません。', '404');
}


// ファイルチェック
$file_info = $file_list[0];
$file_path = UPLOAD_FILES . $file_info['file_path'];
if (!is_file($file_path)){
    show_errors($json_list, 'ファイルが存在しません。', '404');
}
unset($file_info['file_path']);


// ダウンロード数取得
$filecount_sql = 'SELECT COUNT(*) AS download_count FROM file_download WHERE send_token = "'. esc($link, $_POST['send_token']) .'"';
$download_count = (int)get_allrows($link, $filecount_sql)[0]['download_count'];



// ファイル情報送信
$json_list['data'] = ['file_info' => $file_info, 'download_count' => $download_count];
show_success($json_list, 'ファイル情報の取得が完了しました');