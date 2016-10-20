<?php
/**
 * 添加自定义的路由器
 */

//访问API接口
F_Route::add('/eapi?sMethod=[a-zA-Z0-9\.]+', function(F_Controller_Request_Http $requestObj, $params) {
    //符合路由规则后的处理,定位到哪个 module/controller/action
    //print_r($params);
    
    //exit;
    
    $requestObj->setParams($params);
    F_Eapi::run();
})->opTermination();

//F_Route::add('/aa/{id}/{name}', function($requestObj, $params) {
//    //符合路由规则后的处理,定位到哪个 module/controller/action
//    print_r($params);
//    exit;
//})->where('id', '[0-9]+')->where('name', '[a-zA-Z0-9\.]+')->opTermination();