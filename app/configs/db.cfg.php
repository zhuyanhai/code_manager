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
                'host'     => 'db1.int.utan.com',
                'port'     => '3306',
                'socket'   => '',
                'username' => 'dbuser',
                'password' => 'gm9QROY55=IPVF-l',
                'charset'  => 'utf8mb4',
                'collation' => 'utf8mb4_general_ci',
            ),
            'slave' => array(
                'host'     => 'db1.int.utan.com',
                'port'     => '3306',
                'socket'   => '',
                'username' => 'dbuser',
                'password' => 'gm9QROY55=IPVF-l',
                'charset'  => 'utf8mb4',
                'collation' => 'utf8mb4_general_ci',
            ),
        ),
        'code_manager' => array(
            'dbName' => 'db2_utan_code_manager',
        ),
    ),
);