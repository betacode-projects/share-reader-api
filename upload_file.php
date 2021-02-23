<?php
// ----- デフォルト設定
require_once './__default.php';

$secret_token = create_uuid();
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


// アップロード者トークン生成
$token = create_uuid();


// アップロード者情報登録
$token_sql = create_insert_sql($link, 'sender', [
    'send_token' => $token,
    'send_agent' => $_SERVER['HTTP_USER_AGENT'],
    'send_ipaddr' => $_SERVER["REMOTE_ADDR"],
    'send_secret_token' => $secret_token,
    'send_flag' => 1
]);
$result = mysqli_query($link, $token_sql);
if (!$result)
    show_errors($json_list, 'データーベースへの登録に失敗しました。もう一度お試しください。', 'DB');


// ハッシュ値取得
$file_hash = hash_file('sha256', $_FILES['file']['tmp_name']);

// file_infoテーブルに追加
$file_path_hash = create_uuid();
$fileinfo_sql = create_insert_sql($link, 'file_info', [
    'send_token' => $token,
    'file_size' => (int)$file_size,
    'file_name' => $file_name,
    'file_format' => $file_mime,
    'file_hash' => $file_hash,
    'file_path' => $file_path_hash,
    'file_flag' => 1
]);
$result = mysqli_query($link, $fileinfo_sql);
if (!$result)
    show_errors($json_list, 'データーベースへの登録に失敗しました。もう一度お試しください。', 'DB');


// アップロードファイル移動
$file_dir = UPLOAD_FILES . $file_path_hash;
$file_path = $file_dir . '/'. $file_name;
if (!mkdir($file_dir)){
    show_errors($json_list, 'ファイルアップロードの設定に失敗しました。');
}

if (!@move_uploaded_file($_FILES['file']['tmp_name'], $file_path)){
    show_errors($json_list, 'ファイルアップロードに失敗しました。');
}


// 完了処理
$json_list['data'] = ['token' => $token, 'secret_token' => $secret_token];
show_success($json_list, 'アップロードに成功しました');
