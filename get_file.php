<?php

require_once './define/const.php';
require_once './define/func.php';


function show_error_headers($error, $code, $page_error){
    if (isset($_GET['jump'])){
        header('Error-Body: '. urlencode($error));
        header('location: '. ERROR_PAGE .'?error='. urlencode($error), true, 307);
    }
    else{
        header('Error-Body: '. urlencode($error));
        http_response_code($code);
    }
    exit;
}


// ----- トークンチェック
if (!isset($_GET['recv_secret_token']) || strlen($_GET['recv_secret_token']) !== 64){
    show_error_headers('受信者トークン形式が正しくありません。', 406, 1);
}


// ----- DBへアクセス
$link = @mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if (!$link) {
    show_error_headers('データベースからの応答がありません。しばらくたってからアクセスしてください。', 500, 2);
}
mysqli_set_charset($link, 'utf8');


// 受信者検索
$recvcheck_sql = 'SELECT * FROM reciever WHERE recv_secret_token = "'. esc($link, $_GET['recv_secret_token']) .'" AND recv_flag = 1';
$recv_list = get_allrows($link, $recvcheck_sql);

if (count($recv_list) <= 0){
    show_error_headers('受信者トークンが見つかりません。', 404, 3);
}
$recv_token = $recv_list[0]['recv_token'];


// 受信者削除
$token_remove_sql = create_update_sql($link, 'reciever', ['recv_flag' => 0], '"'.esc($link, $recv_token) .'"', 'recv_token');
$result = mysqli_query($link, $token_remove_sql);
if (!$result){
    show_error_headers('受信者トークンの更新に失敗しました。', 500, 4);
}


// 送信者トークン取得
$check_sql = 'SELECT * FROM qr_read_list WHERE recv_token = "'. esc($link, $recv_token) .'"';
$file_list = get_allrows($link, $check_sql);

if (count($file_list) <= 0){
    show_error_headers('トークンと一致するファイルは見つかりませんでした。', 404, 5);
}
$sender_token = $file_list[0]['send_token'];


// ファイル情報テーブルにアクセス
$filecheck_sql = 'SELECT * FROM file_info WHERE send_token = "'. esc($link, $sender_token) .'" AND file_flag = 1';
$file_list = get_allrows($link, $filecheck_sql);

if (count($file_list) <= 0){
    show_error_headers('ファイル情報が見つかりません。', 404, 6);
}
$file_info = $file_list[0];
$file_path = UPLOAD_FILES . $file_info['file_path'];


// ファイルチェック
if (!is_file($file_path)){
    show_error_headers('ファイルが存在しません。', 404, 7);
}


// ファイルのハッシュ値照合
$file_hash = hash_file('sha256', $file_path);
if ($file_hash !== $file_info['file_hash']){
    show_error_headers('ファイルのハッシュ値が一致しません。', 404, 8);
}


// ダウンロード数+1
$download_sql = create_insert_sql($link, 'file_download', [
    'recv_token' => $recv_token,
    'send_token' => $sender_token
]);
$result = mysqli_query($link, $download_sql);
if (!$result){
    show_error_headers('この受信者トークンは、ダウンロード済みです。', 500, 9);
}

// ダウンロード開始
while (ob_get_clean()) {
    ob_end_clean();
}
header("Access-Control-Allow-Origin: *");
header('Content-Length: '.filesize($file_path));
header('Content-Disposition: attachment; filename="'.$file_info['file_name'].'"');
readfile($file_path);

/*
header("Content-Type: application/json; charset=UTF-8");
$download_url = (empty($_SERVER['HTTPS']) ? 'http://' : 'https://') . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']).'/'. $file_path;
$json_list['data'] = ['file_link' => $download_url];
show_success($json_list, 'ファイルダウンロード時の設定が完了しました');
*/