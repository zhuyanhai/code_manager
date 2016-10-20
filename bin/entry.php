<?php

//判断是否为 CLI 脚本 1 = 是 0 = 不是
defined('DEFAULT_CLI') || define( 'DEFAULT_CLI', 1);

// 设定 - 站点根目录
defined('ROOT_PATH') || define('ROOT_PATH', realpath(dirname(__FILE__) . '/..'));

// 设定 - 站点程序目录
defined('APPLICATION_PATH') || define('APPLICATION_PATH', ROOT_PATH . '/app');

// 设定 - 站点环境变量
defined('APPLICATION_ENV') || define('APPLICATION_ENV', TMS_ENVIRON);

// 设定 - 站点类库检索路径 Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
	realpath(APPLICATION_PATH),
    realpath(APPLICATION_PATH . '/controllers'),
    realpath(ROOT_PATH . '/library'),
    get_include_path(),
)));

//加载应用程序初始处理类
require ROOT_PATH . '/library/F/Application.php';

F_Application::getInstance()->bootstrap();