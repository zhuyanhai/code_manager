<?php
/**
 * 文件方式记录
 * 
 * 直接记录，不需要等待进程退出
 */
class F_Log_Handler_Direct extends F_Log_Handler_Abstract
{
    protected $_logMessages = '';
    
    /**
     * 保存
     */
	public function save()
	{
        $this->_logMessages .= $this->_format().PHP_EOL;
        
        $destDir = F_Log_Config::getBasePath() . date('Y'). '/' . date('m');
		if (!is_dir($destDir)) {
			mkdir($destDir, 0777, true);
		}
		$destFile = $destDir . '/' . $this->_formatterObj->args['level'] . '_' . date('Y-m-d') . '.log';
		touch($destDir);
		chmod($destDir, 0777);
        
		file_put_contents($destFile, $this->_logMessages, FILE_APPEND);
        $this->_logMessages = '';
	}
}
