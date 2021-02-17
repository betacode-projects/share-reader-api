<?php

require_once './__default.php';
$json_list['data'] = ['flag' => false];


// ----- 送信者トークンチェック
if (!isset($_POST['token']) || strlen($_POST['token']) !== 64 || !isset($_POST['recv_token']) || strlen($_POST['recv_token']) !== 64){
    show_errors($json_list, 'トークン形式が正しくありません。', 'NG');
}


// ----- DBへ登録
$link = @mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if (!$link) {
    show_errors($json_list, 'データベースからの応答がありません。しばらくたってからアクセスしてください。', 'DB');
}
mysqli_set_charset($link, 'utf8');


// SQL生成
$token_sql = create_insert_sql($link, 'file_download', [
    'recv_token' => $_POST['recv_token'],
    'send_token' => $_POST['token']
]);

$result = mysqli_query($link, $token_sql);
if (!$result){
    show_errors($json_list, 'トークンが一致しませんでした', 'NG');
}
mysqli_insert_id($link);


// ----- json出力
$json_list['data'] = ['flag' => true];
show_success($json_list, 'データベースへ追加が完了しました。');