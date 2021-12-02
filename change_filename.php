<?php

// ----- デフォルト設定
require_once './__default.php';
$json_list['data'] = ['flag' => false];


// ----- トークンチェック
if (!isset($_POST['send_secret_token']) || strlen($_POST['send_secret_token']) !== 64){
    show_errors($json_list, 'トークン形式が正しくありません。');
}

if (!isset($_POST['filename'])){
    show_errors($json_list, 'ファイル名が指定されていません。');
}
elseif (preg_match('/^.*[(\\|/|:|\*|?|\"|<|>|\|)].*$/', $_POST['filename']) || mb_strlen($_POST['filename']) > 255){
    show_errors($json_list, '無効なファイル名です。');
}


// ----- DBへ登録
$link = @mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if (!$link) {
    show_errors($json_list, 'データベースからの応答がありません。しばらくたってからアクセスしてください。');
}
mysqli_set_charset($link, 'utf8');

$send_token = get_sender_secret2token($link, $json_list, $_POST['send_secret_token']);


// ファイル情報テーブルにアクセス
$filecheck_sql = 'SELECT * FROM file_info WHERE send_token = "'. esc($link, $send_token) .'" AND file_flag = 1';
$file_list = get_allrows($link, $filecheck_sql);

if (count($file_list) <= 0){
    show_errors($json_list, 'ファイル情報が見つかりません。', '404');
}
$file_info = $file_list[0];

// ファイル名アップデート
$file_rename_sql = create_update_sql($link, 'file_info', ['file_name' => $_POST['filename']], '"'.esc($link, $send_token) .'"', 'send_token');
$result = mysqli_query($link, $file_rename_sql);
if (!$result){
    show_errors($json_list, 'ファイル名の変更に失敗しました。');
}


$json_list['data'] = ['flag' => true, 'file_name' => $_POST['filename'], 'file_ext' => $file_info['file_ext']];
show_success($json_list, 'ファイル名を変更しました');
