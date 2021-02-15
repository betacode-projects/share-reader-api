<?php
// ----- デフォルト設定
require_once './__default.php';

// ファイルチェック
if (!isset($_FILES['file']) || empty($_FILES['file']['tmp_name'])){
    show_errors($json_list, 'ファイルがアップロードされていません。');
}


// MIME・ファイルサイズ・ファイル名情報取得
$finfo = new finfo(FILEINFO_MIME_TYPE);
$file_mime = $finfo->file($_FILES['file']['tmp_name']);
$file_size = $_FILES['file']['size'];
$file_name = $_FILES['file']['name'];


// DB接続
$link = @mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if (!$link) {
    show_errors($json_list, 'データベースからの応答がありません。しばらくたってからアクセスしてください。');
}
mysqli_set_charset($link, 'utf8');


// file_infoテーブルに追加
$fileinfo_sql = create_insert_sql($link, 'file_info', [
    'file_size' => (int)$file_size,
    'file_name' => $file_name,
    'file_format' => $file_mime,
    'file_flag' => 1
]);
$result = mysqli_query($link, $fileinfo_sql);
if (!$result)
    show_errors($json_list, 'データーベースへの登録に失敗しました。もう一度お試しください。', 'DB');

$file_id = mysqli_insert_id($link);


// アップロードファイル移動
if (@move_uploaded_file($_FILES['file']['tmp_name'], './upload_files/'. $file_id .'.DAT') === False){
    show_errors($json_list, 'アップロードに失敗しました。');
}


// アップロード者トークン生成
$token = create_uuid();


// アップロード者情報登録
$token_sql = create_insert_sql($link, 'sender', [
    'file_id' => $file_id,
    'send_token' => $token,
    'send_agent' => $_SERVER['HTTP_USER_AGENT'],
    'send_ipaddr' => $_SERVER["REMOTE_ADDR"],
    'send_flag' => 1
]);
$result = mysqli_query($link, $token_sql);
if (!$result)
    show_errors($json_list, 'データーベースへの登録に失敗しました。もう一度お試しください。', 'DB');
mysqli_insert_id($link);


// 完了処理
$json_list['data'] = ['token' => $token];
show_success($json_list, 'アップロードに成功しました');
