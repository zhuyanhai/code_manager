<?php
/**
 * 验证类抛出的异常
 *
 */
class Utils_Validation_Exception extends F_Exception
{
    public $errorKey = null;
    public $errorMsg = null;
    
    function __construct($errorKey, $errorMsg = '', $errorCode = 0)
    {
        $this->errorKey = $errorKey;
        $this->errorMsg = $errorMsg;
        parent::__construct($errorMsg, $errorCode);
    }
    
    /**
     * 跳转到 refer[来源页]，如果没有来源，就跳转到指定的URL
     * 
     * @param string $url 如果没有来源，就跳转到指定的URL
     * @return void
     */
    public function jumpToRefer($url)
    {
        $refer = Utils_Http::getReferer($url);
        F_Controller_Redirector::getInstance()->gotoUrlAndExit($refer);
    }
}