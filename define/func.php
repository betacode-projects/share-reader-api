<?php

// issetとemptyを検証
function isset_list($data, $check_list, $empty=true){
    foreach($check_list as $l){
        if (!isset($data[$l]) || ($empty && empty($data[$l]))){
            return false;
        }
    }
    return true;
}

// ページ遷移
function jump_page($page){
    header('location: '.$page);
    exit;
}

function get_allrows($link, $sql) {
    $result = mysqli_query($link, $sql);
    $db_data = [];

    if (!$result)
        return $db_data;

    while ($row = mysqli_fetch_assoc($result))
        $db_data[] = $row;
        
    return $db_data;
}


function set_keys($key_list){
    $tmp_list = [];
    foreach($key_list as $k)
        $tmp_list[$k] = '';
    return $tmp_list;
}

// ランダム文字列生成
function random_str($length = 16){
    return substr(bin2hex(random_bytes($length)), 0, $length);
}


// 特殊文字を削除
function esc($link, $str){
    return mysqli_real_escape_string($link, $str);
}

// XSS対策
function h($s) {
    return htmlspecialchars($s, ENT_QUOTES, "UTF-8");
}


function show($val){
    if (!isset($val) || $val == NULL)
        echo '';
    else
        echo h($val);
}


function show_errors($json_list, $msg, $code='SYNTAX'){
    $json_list['messages'] = $msg;
    $json_list['code'] = $code;
    echo json_encode($json_list);
    exit;
}

function show_success($json_list, $msg = 'successful'){
    $json_list['messages'] = $msg;
    $json_list['status'] = 'success';
    echo json_encode($json_list);
    exit;
}


// INSERT文生成
function create_insert_sql($link, $table_name, $dict){
    $vars = 'INSERT INTO '. $table_name .' ('. implode(', ', array_keys($dict)) .')';
    $values_list = [];

    foreach(array_values($dict) as $value){
        if ($value == 'NULL' || (!ctype_digit($value) && is_numeric($value)))
            $values_list[] = mysqli_real_escape_string($link, $value);
        else
            $values_list[] .= '"'. mysqli_real_escape_string($link, $value) . '" ';
    }
    $datas = ' VALUES ('. implode(', ', $values_list) . ')';

    return $vars . $datas;
}


// UPDATE文生成
function create_update_sql($link, $table_name, $dict, $id_value, $id_name='id'){
    $vars = 'UPDATE '. $table_name .' SET ';
    $len = count($dict);
    $count = 1;

    foreach($dict as $key => $value){
        if ($value != 'NULL' && !is_numeric($value))
            $value = '"'. mysqli_real_escape_string($link, $value). '" ';
        else
            $value = mysqli_real_escape_string($link, $value);
        $vars .= $key .' = '. $value;

        if ($len > $count)
            $vars .= ', ';
        $count++;
    }

    return $vars . ' WHERE '. $id_name .' = '. $id_value;
}


function create_uuid(){
    return hash('sha256', bin2hex(random_bytes(64)));
}

