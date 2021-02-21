<?php

require_once './__default.php';

// ----- トークンチェック
if (!isset($_POST['recv_token']) || strlen($_POST['recv_token']) !== 64){
    show_errors($json_list, '受信者トークン形式が正しくありません。');
}
if (!isset($_POST['send_token']) || strlen($_POST['send_token']) !== 64){
    show_errors($json_list, '送信者トークン形式が正しくありません。');
}


// ----- DBへアクセス
$link = @mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if (!$link) {
    show_errors($json_list, 'データベースからの応答がありません。しばらくたってからアクセスしてください。', 'DB');
}
mysqli_set_charset($link, 'utf8');


// 受信者検索
$filecheck_sql = 'SELECT * FROM reciever WHERE recv_token = "'. esc($link, $_POST['recv_token']) .'" AND recv_flag = 1';
$file_list = get_allrows($link, $filecheck_sql);

if (count($file_list) <= 0){
    show_errors($json_list, '受信者トークンが見つかりません。');
}

// 受信者削除
$token_remove_sql = create_update_sql($link, 'reciever', ['recv_flag' => 0], '"'.esc($link, $_POST['recv_token']) .'"', 'recv_token');
$result = mysqli_query($link, $token_remove_sql);
if (!$result){
    show_errors($json_list, '受信者トークンの更新に失敗しました。');
}


// ファイル情報テーブルにアクセス
$filecheck_sql = 'SELECT * FROM file_info WHERE send_token = "'. esc($link, $_POST['send_token']) .'" AND file_flag = 1';
$file_list = get_allrows($link, $filecheck_sql);

if (count($file_list) <= 0){
    show_errors($json_list, 'ファイル情報が見つかりません。');
}
$file_info = $file_list[0];
$file_path = UPLOAD_FILES . $file_info['file_path'];


// ファイルチェック
$file_info = $file_list[0];
$file_path = UPLOAD_FILES . $file_info['file_path'] . '/'. $file_info['file_name'];
if (!is_file($file_path)){
    show_errors($json_list, 'ファイルが存在しません。');
}


// ファイルのハッシュ値照合
$file_hash = hash_file('sha256', $file_path);
if ($file_hash !== $file_info['file_hash']){
    show_errors($json_list, 'ファイルのハッシュ値が一致しません。');
}


// ダウンロード数+1
$download_sql = create_insert_sql($link, 'file_download', [
    'recv_token' => $_POST['recv_token'],
    'send_token' => $_POST['send_token']
]);
mysqli_query($link, $download_sql);


// チェック終了
$download_url = (empty($_SERVER['HTTPS']) ? 'http://' : 'https://') . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']).'/'. $file_path;
$json_list['data'] = ['file_link' => $download_url];
show_success($json_list, 'ファイルダウンロード時の設定が完了しました');