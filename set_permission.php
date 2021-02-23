<?php
// ----- デフォルト設定
require_once './__default.php';

// ----- 送信者トークンチェック
if (!isset($_POST['send_secret_token']) || strlen($_POST['send_secret_token']) !== 64){
    show_errors($json_list, 'トークン形式が正しくありません。', 'NG');
}


// DB接続
$link = @mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if (!$link) {
    show_errors($json_list, 'データベースからの応答がありません。しばらくたってからアクセスしてください。');
}
mysqli_set_charset($link, 'utf8');


// アップロード者トークン生成
$token = create_uuid();


// 受信者トークン・秘密トークンチェック
$before_token = get_sender_secret2token($link, $json_list, $_POST['send_secret_token']);


// 受信者削除
$token_remove_sql = create_update_sql($link, 'sender', ['send_flag' => 0], '"'.esc($link, $before_token) .'"', 'send_token');
$result = mysqli_query($link, $token_remove_sql);
if (!$result){
    show_errors($json_list, '送信者トークンの更新に失敗しました。');
}


// 秘密トークン生成
$secret_token = create_uuid();


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


// 権限移行用データベースに追加
$move_token_sql = create_insert_sql($link, 'move_permission', [
    'send_token_before' => $before_token,
    'send_token_after' => $token
]);
$result = mysqli_query($link, $move_token_sql);
if (!$result)
    show_errors($json_list, 'データーベースへの登録に失敗しました。もう一度お試しください。'.$move_token_sql, 'DB');


// ファイル情報更新
$token_remove_sql = create_update_sql($link, 'file_info', ['send_token' => $token], '"'.esc($link, $before_token) .'"', 'send_token');
$result = mysqli_query($link, $token_remove_sql);
if (!$result){
    show_errors($json_list, '送信者トークンの更新に失敗しました。');
}


// ダウンロードリスト更新
$token_remove_sql = create_update_sql($link, 'file_download', ['send_token' => $token], '"'.esc($link, $before_token) .'"', 'send_token');
$result = mysqli_query($link, $token_remove_sql);
if (!$result){
    show_errors($json_list, '送信者トークンの更新に失敗しました。');
}

// QR読み込みリスト更新
$token_remove_sql = create_update_sql($link, 'qr_read_list', ['send_token' => $token], '"'.esc($link, $before_token) .'"', 'send_token');
$result = mysqli_query($link, $token_remove_sql);
if (!$result){
    show_errors($json_list, '送信者トークンの更新に失敗しました。');
}


// 完了処理
$json_list['data'] = ['sender_token' => $token, 'sender_secret_token' => $secret_token];
show_success($json_list, 'ファイル操作権限の移行に成功しました。');
