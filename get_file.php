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

// TODO
// $file_hash = hash_file('sha256', $file_path);
$filecheck_sql = 'SELECT * FROM file_info WHERE send_token = "'. esc($link, $_POST['send_token']) .'" AND file_hash = "'. esc($link, $file_hash). '"';
$file_list = get_allrows($link, $filecheck_sql);

if (count($file_list) <= 0){
    show_errors($json_list, 'ファイルのハッシュ値が一致しません。');
}

// ファイルチェック
$file_info = $file_list[0];
$file_path = UPLOAD_FILES . $_POST['send_token'] . '/'. $file_info['file_name'];
if (!is_file($file_path)){
    show_errors($json_list, 'ファイルが存在しません。');
}


// 受信者削除
$token_remove_sql = create_update_sql($link, 'reciever', ['recv_flag' => 0], '"'.esc($link, $_POST['recv_token']) .'"', 'recv_token');
//$result = mysqli_query($link, $token_remove_sql);


// チェック終了
$json_list['data'] = ['file_info' => $file_info, 'file_link' => $file_path];
show_success($json_list, 'ファイル情報の取得が完了しました');