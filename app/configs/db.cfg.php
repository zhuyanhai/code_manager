<?php
/**
 * 数据库 配置文件
 * 
 * @author allen <allenifox@163.com>
 */
return array(
    //配置的命名空间名字
    'namespace' => 'db',
    
    'db' => array(//命名空间中的配置内容
        
        'default' => array(
            'master' => array(
                'host'     => 'localhost',
                'port'     => '3306',
                'socket'   => '/data/mysqldb/mysql.sock',
                'username' => 'root',
                'password' => '19820111',
                'charset'  => 'utf8mb4',
                'collation' => 'utf8mb4_general_ci',
            ),
            'slave' => array(
                'host'     => 'localhost',
                'port'     => '3306',
                'socket'   => '/data/mysqldb/mysql.sock',
                'username' => 'root',
                'password' => '19820111',
                'charset'  => 'utf8mb4',
                'collation' => 'utf8mb4_general_ci',
            ),
        ),
        'code_manager' => array(
            'dbName' => 'db2_utan_code_manager',
        ),
    ),
);