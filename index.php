<?php
/**
 * 测试数据库比较这个包.
 * User: Sanit <huangsinan@zsdx.cn>
 * Date: 2017/3/20
 * Time: 20:55
 */

require  './vendor/autoload.php';
require './src/config/config.php';

use DatabaseCompare\Compare\Compare;

$result = new Compare($database);

echo (json_encode($result->compareResult()));
