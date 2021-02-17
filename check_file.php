<?php

// ----- デフォルト設定
require_once './__default.php';
$json_list['data'] = ['flag' => false];


// ----- DBへ登録
$link = @mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if (!$link) {
    show_errors($json_list, 'データベースからの応答がありません。しばらくたってからアクセスしてください。', 'DB');
}
mysqli_set_charset($link, 'utf8');


//TODO: トークンチェック