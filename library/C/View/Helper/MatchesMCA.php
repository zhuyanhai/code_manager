<?php
/**
 * 匹配 module controller action
 * 
 * 根据传入的字符串，匹配当前访问的 module_controller_action
 *
 * @author allen <allen@yuorngcorp.com>
 * @package C_View
 */
final class C_View_Helper_MatchesMCA
{
    /**
     * 匹配 module controller action
     * 
     * @param string $inputPath 指定的需要对比的 module_module_controller_action module_controller_action
     * @return boolean true=匹配 false=不匹配
     */
    public function matchesMCA($inputPath, $output = null)
    {
        $requestObj = F_Controller_Request_Http::getInstance();
        $path  = $requestObj->getModule();
        $path .= '_' . $requestObj->getController();
        $path .= '_' . $requestObj->getAction();
        if ($path === $inputPath) {
            return (is_null($output))?true:$output;
        }
        return (is_null($output))?false:'';
    }
    
}