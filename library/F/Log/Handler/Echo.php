<?php
/**
 * 打印记录
 */
class F_Log_Handler_Echo extends F_Log_Handler_Abstract
{
    protected $_logMessages = array();
            
    /**
     * 析构函数
     */
    public function __destruct()
    {
        $content = implode('<br/><hr/>', $this->_logMessages);
        Utils_Http::outHeaderEncoding();
        $html = <<<EOF
        <div id="flGlobalOfDebugId" style="position:fixed;bottom:0px;left:0px;width:600px;height:500px;overflow:hidden;overflow-y:auto;background:#ccc";padding:0px;margin:0px;">
            <p style="border-bottom:1px solid #000;padding:0px;margin:0px;margin-top:5px;padding-bottom:5px;position:relative;">
                &nbsp;打印的调试信息：
                <span style="display:inline-block;float:right;margin-right:5px;font-weight:bold;cursor:pointer;" onclick="document.getElementById('flGlobalOfDebugId').style.display='none'">×</span>
            </p>
            <p style="padding:5px;">
            {$content}
            </p>
        </div>
EOF;
        echo $html;
    }
    
    /**
     * 打印
     */
	public function save()
	{
        $logMessage = $this->_format().PHP_EOL;
        array_push($this->_logMessages, $logMessage);
        //当 $_GET 参数中带有 sLogFlag 参数时，并且参数值＝echo，就会在程序结束后打印到页面上
        if (isset($_GET['sLogFlag']) && $_GET['sLogFlag'] === 'echo') {
            echo $logMessage;
        }
	}
}
