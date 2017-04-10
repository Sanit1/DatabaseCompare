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
            'hostname'       => '115.28.231.211',
            // 数据库名
            'database'       => 'work',
            // 用户名
            'username'       => 'root',
            // 密码
            'password'       => 'hangzhou123',
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

        ],
        //db2
        [
            'type'           => 'mysql',
            // 服务器地址
            'hostname'       => '115.28.231.211',
            // 数据库名
            'database'       => 'test',
            // 用户名
            'username'       => 'root',
            // 密码
            'password'       => 'hangzhou123',
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
