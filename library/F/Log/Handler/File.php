<?php
/**
 * 文件方式记录
 */
class F_Log_Handler_File extends F_Log_Handler_Abstract
{
    protected $_logMessages = '';
            
    /**
     * 析构函数
     */
    public function __destruct()
    {
		$destDir = F_Log_Config::getBasePath() . date('Y'). '/' . date('m');
		if (!is_dir($destDir)) {
			mkdir($destDir, 0777, true);
		}
		$destFile = $destDir . '/' . $this->_formatterObj->args['level'] . '_' . date('Y-m-d') . '.log';
		touch($destDir);
		chmod($destDir, 0777);
		file_put_contents($destFile, $this->_logMessages, FILE_APPEND);
    }
    
    /**
     * 保存
     */
	public function save()
	{
        $this->_logMessages .= $this->_format().PHP_EOL;
	}
}
