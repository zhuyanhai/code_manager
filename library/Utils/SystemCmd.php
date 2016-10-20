<?php
/**
 * 运行系统命令 － 工具集合
 *
 * 运行Linux系统的各种命令
 *
 * @package Utils
 */
final class Utils_SystemCmd
{
    
    /**
     * 运行命令
     * 
     * @param string $command 命令
     * @param string $cwd 要执行命令的初始工作目录。 必须是 绝对 路径， 设置此参数为 NULL 表示使用默认值（当前 PHP 进程的工作目录）。
     * @return boolean
     * @throws Exception
     */
    public static function run($command, $cwd)
    {
        //一个索引数组。 数组的键表示描述符，数组元素值表示 PHP 如何将这些描述符传送至子进程 0 表示标准输入（stdin），1 表示标准输出（stdout），2 表示标准错误（stderr）。
        $descriptorspec = array(
            1 => array('pipe', 'w'),
            2 => array('pipe', 'w'),
        );

        $pipes = array();

        // $_ENV = 要执行的命令所使用的环境变量。 设置此参数为 NULL 表示使用和当前 PHP 进程相同的环境变量。
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