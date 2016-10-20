<?php
/**
 * git 操作类
 * 
 * @category F
 * @package F_Git
 * @author allen <allenifox@163.com>
 * 
 */
final class F_Git
{
    private static $_bin = '/usr/bin/git';
    
    /**
     * git add命令主要用于把我们要提交的文件的信息添加到索引库中
     * 
     * @param string $path *=添加所有 | 如果是指定多个文件,使用空格分隔,例如 a.txt b.txt
     * @param string $option A=表示把$path中所有tracked文件中被修改过或已删除文件和所有untracted的文件信息添加到索引库
     * @return void
     */
    public static function add($path = '*', $option = 'A')
    {
        $command = self::$_bin . ' add -' . $option .' '. $path;
        self::_run($command);
    }
    
    /**
     * git commit 
     * 
     * @param string $message
     * @param string $option A=表示把$path中所有tracked文件中被修改过或已删除文件和所有untracted的文件信息添加到索引库
     * @return void
     */
    public static function commit($message = '', $option = 'am')
    {
        $command = self::$_bin . ' commit -' . $option .' "'. $message.'"';
        self::_run($command);
    }
    
    /**
     * git push 
     * 
     * @return void
     */
    public static function push()
    {
        $command = self::$_bin . ' push';
        self::_run($command);
    }
    
    /**
     * 运行构造好的git命令
     * 
     * @param string $command
     * @return string
     */
    private static function _run($command)
    {
        $descriptorspec = array(
			1 => array('pipe', 'w'),
			2 => array('pipe', 'w'),
		);
        
		$pipes = array();
        
        $cwd = '/data/docs/';
		$resource = proc_open($command, $descriptorspec, $pipes, $cwd, $_ENV);
        
        if (is_resource($resource)) {

            $stdout = stream_get_contents($pipes[1]);
            $stderr = stream_get_contents($pipes[2]);

            foreach ($pipes as $pipe) {
                fclose($pipe);
            }

            $status = trim(proc_close($resource));
            if ($status) {
                throw new Exception($stderr);
            }

            return $stdout;
        }
		
        return false;
    }
    
    
}