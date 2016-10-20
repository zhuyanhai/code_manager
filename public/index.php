<?php
// 设定 - 站点根目录
defined('ROOT_PATH') || define('ROOT_PATH', realpath(dirname(__FILE__) . '/..'));

//加载应用程序初始处理类
require ROOT_PATH . '/library/F/Application.php';

F_Application::getInstance()->bootstrap()->run();