<?php
/**
 * 框架应用程序全局的并且是必须的配置文件
 * 
 * @author allen <allenifox@163.com>
 */
return array(
    //配置的命名空间名字
    'namespace' => 'application',
    
    'application' => array(//命名空间中的配置内容

        //与运行环境有关的配置
        'environments' => array(
            'dev' => array(//开发环境
                //php 错误配置
                'phpSettings' => array(
                    'display_errors' => 1,
                    'display_startup_errors' => 1,
                    'track_errors' => 1,
                    'error_reporting' => E_ALL | E_STRICT,
                ),
            ),
            'test_dev' => array(//测试环境
                //php 错误配置
                'phpSettings' => array(
                    'display_errors' => 1,
                    'display_startup_errors' => 1,
                    'track_errors' => 1,
                    'error_reporting' => E_ALL | E_STRICT,
                ),
            ),
            'test_online' => array(//线上测试环境
                //php 错误配置
                'phpSettings' => array(
                    'display_errors' => 0,
                    'display_startup_errors' => 0,
                    'track_errors' => 0,
                    'error_reporting' => E_ALL | E_STRICT,
                ),
            ),
            'online' => array(//线上正式环境
                //php 错误配置
                'phpSettings' => array(
                    'display_errors' => 0,
                    'display_startup_errors' => 0,
                    'track_errors' => 0,
                    'error_reporting' => E_ALL | E_STRICT,
                ),
            ),
        ),
        //页面布局配置
        'view' => array(
            'charset'    => 'utf-8',
            'layoutPath' => APPLICATION_PATH . '/views/layouts/',
            'scriptPath' => APPLICATION_PATH . '/views/scripts/',
        ),
        //autoload 命名空间配置
        'autoloaderNamespaces' => array(
           
        ),
        //bootstrap 框架执行时的引导程序
        'bootstrap' => array(
            'path'  => APPLICATION_PATH . '/Bootstrap.php',
            'class' => "Bootstrap",
        ),
        'domain' => array(//域名标识与域名的map
            'doc' => 'doc.bxshare.cn'
        ),
        'asset' => array(
            'isDedicatedDomain' => 'off',
            'combo' => array(//是否使用nginx combo 加载 合并静态文件
                'jsEnable'  => 'off',
                'cssEnable' => 'off',
            ),
            'cdn' => array(//是否使用第三方 cdn 加载 静态文件
                'jsEnable'  => 'off',
                'cssEnable' => 'off',
                'js' => array(
                    'jquery_1_11_1_min_js' => 'http://cdn.bootcss.com/jquery/1.11.1/jquery.min.js',
                ),
                'css' => array(
                ),
            ),
        ),
    ),
);