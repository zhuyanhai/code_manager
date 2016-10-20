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
}