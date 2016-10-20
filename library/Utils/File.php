<?php
/**
 * 文件处理集合 － 工具集合
 *
 * @package Utils
 */
final class Utils_File
{
    /**
     * 获取某文件的指定多行记录
     * 
     * @param string $filename 文件全路径
     * @param int $startLine 开始行位置
     * @param int $endLine 结束行位置
     * @param string $method 读取文件的方式
     * @return array
     */
    public static function getLines($filename, $startLine = 1, $endLine = 50, $method = 'rb')
    {
        $content = array();
        $count = $endLine - $startLine;
        $fp = new SplFileObject($filename, $method);
        $fp->seek($startLine - 1); // 转到第N行, seek方法参数从0开始计数 
        for ($i = 0; $i <= $count; ++$i) {
            $content[] = $fp->current(); // current()获取当前行内容 
            $fp->next(); // 下一行 
        }
        return array_filter($content); // array_filter过滤：false,null,'' 
    }
    
    /**
     * 把整个文件读入一个数组中
     * 
     * @param string $filename 文件全路径
     * @return array
     */
    public static function getArray($filename)
    {
        if (file_exists($filename) && is_file($filename)) {
            return file($filename);
        }
        return array();
    }

    /**
     * 保存内容到文件中
     * 
     * @param string $filename 文件全路径
     * @param string|array $content 文件内容
     * @param string $method 写文件的方式 w=覆盖文件内容 wl=覆盖文件内容,在写入时获得一个独占锁 a=追加文件内容 al=追加文件内容,在写入时获得一个独占锁
     * @return void
     */
    public static function save($filename, $content, $method = 'w')
    {
        if (is_array($content)) {
            if (!empty($content)) {
                foreach ($content as &$con) {
                    $con = trim($con, PHP_EOL);
                }
                $content = implode(PHP_EOL, $content);
            } else {
                $content = '';
            }
        }
        
        switch ($method) {
            case 'wl':
                file_put_contents($filename, $content, LOCK_EX);
                break;
            case 'al':
                file_put_contents($filename, $content, FILE_APPEND | LOCK_EX);
                break;
            case 'a':
                file_put_contents($filename, $content, FILE_APPEND);
                break;
            default:
                file_put_contents($filename, $content);
                break;
        }
    }
}
