<?php
/**
 * memcache 配置文件
 * 
 * @author allen <allenifox@163.com>
 */
return array(
    //配置的命名空间名字
    'namespace' => 'memcache',
    
    'memcache' => array(//命名空间中的配置内容
        
        //用户服务
        'user' => array(
            'servers' => array(
                0 => array(
                    'host' => '127.0.0.1',
                    'post' => 11211,
                    'weight' => 1,
                    'automatic_serialization' => true,
                ),
            ),
        ),
    ),
);