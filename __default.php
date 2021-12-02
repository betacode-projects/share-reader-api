<?php

require_once './define/const.php';
require_once './define/func.php';

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");

$json_list = ['status' => 'error', 'messages' => '', 'code' => '', 'data' => []];
