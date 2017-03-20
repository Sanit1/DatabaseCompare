<?php
/**
 * 比较数据库差异的配置文件,在里面配置两个或者多个数据库的配置.
 * User: Sanit <huangsinan@zsdx.cn>
 * Date: 2017/3/13
 * Time: 13:53
 */

$database =
    [
        //db 1
        [
            'type'           => 'mysql',
            // 服务器地址
            'hostname'       => '',//'svn.98school.com',
            // 数据库名
            'database'       => '',
            // 用户名
            'username'       => '',
            // 密码
            'password'       => '',
            // 端口
            'hostport'       => '',
            // 连接dsn
            'dsn'            => '',
            // 数据库连接参数
            'params'         => [],
            // 数据库编码默认采用utf8
            'charset'        => 'utf8mb4',
            // 数据库表前缀
            'prefix'         => 'ad_',

        ],
        //db2 如果还要加数据库自己再在后面加数组
        [
            'type'           => 'mysql',
            // 服务器地址
            'hostname'       => '',
            // 数据库名
            'database'       => '',
            // 用户名
            'username'       => '',
            // 密码
            'password'       => '',
            // 端口
            'hostport'       => '',
            // 连接dsn
            'dsn'            => '',
            // 数据库连接参数
            'params'         => [],
            // 数据库编码默认采用utf8
            'charset'        => 'utf8mb4',
            // 数据库表前缀
            'prefix'         => '',

        ]
    ];
