<?php
/**
 * 正则路由器
 *
 * @category F
 * @package F_Route
 * @author allen <allenifox@163.com>
 */
final class F_Route_Adapter_RegExp extends F_Route_Adapter_Abstract
{ 
    
    /**
     * 执行
     * 
     * @param string $condition
     * @param array $where
     * @param callback $callback
     * @return void
     */
    public function exec($condition, $where, $callback)
    {
        $requestObj = F_Controller_Request_Http::getInstance();
        
        $uri = $requestObj->getRequestUri();
        $uri = trim($uri);

        if (empty($uri)) {
            throw new F_Route_Exception('When routing uri is empty');
        }
        
        $regExpString = $condition;
        if (!empty($where)) {// 替换参数正则
            foreach ($where as $key=>$val) {
                $regExpString = preg_replace('%\{'.$key.'\}%i', '('.$val.')', $regExpString);
            }
        }
        
        $regExpString = preg_replace('%\?%i', '\\?',$regExpString);
        //echo $regExpString.PHP_EOL;
        if (preg_match_all('%'.$regExpString.'%i', $uri, $matches)) {// 匹配
            //print_r($matches);
            
            
//            $i = 1;
//            $params = array();
//            foreach ($where as $key=>$val) {
//                $params[$key] = $matches[$i][0];
//            }
//            if (!empty($params)) {
//                $this->_buildParams($params);
//            }

            $splitURI = explode('?', $uri);
            if (count($splitURI) > 1) {// 设置请求参数
                $this->_buildParams($splitURI[1], true);
            }
            
            $callback($requestObj, $this->_params);
            
            return true;
        }

        return false;
    }
}
