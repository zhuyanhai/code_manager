<?php
/**
 * 框架应用程序获取配置类
 * 
 * @category F
 * @package F_Config
 * @author allen <allenifox@163.com>
 */
final class F_Config
{
    /**
     * 配置数组
     * 
     * @var array
     */
    private static $_configs = array();
    
    /**
     * 检测配置文件是否已经加载了
     * 
     * @var array 
     */
    private static $_configFileList = array();
    
    private function __construct()
    {
        //empty
    }

    /**
     * 加载其他配置
     * 
     * @param string $filename 配置文件的路径 /configs/db.cfg.php
     * @return F_Config
     */
    public static function load($filename)
    {
        $filename = APPLICATION_PATH . $filename;
        if (!isset(self::$_configFileList[$filename])) {
            if (!file_exists($filename)) {
                throw new F_Exception("F_Config::load 文件 {$filename} 找不到");
            }
            self::$_configFileList[$filename] = 1;
            $configs = include $filename;
            self::$_configs[$configs['namespace']] = $configs[$configs['namespace']];
        }
    }
    
    /**
     * 获取 已经通过 load 方法加载的配置信息
     * 
     * @param string $chainName 获取配置，例如：application.domain.doc
     * @return mixed
     */
    public static function get($chainName)
    {
        //分解 $chainName 
        $chainList  = explode('.', $chainName);
        $chainTotal = count($chainList);
        $namespace  = $chainList[0];
        
        if (!isset(self::$_configs[$namespace])) {//命名空间不存在
            throw new F_Exception('F_Config->get 中 “'.$namespace.'” 命名空间的配置项找不到'); 
        }
        
        $returnCfg = self::$_configs[$namespace];
        
        if ($chainTotal <= 1) {
            return $returnCfg;
        }
        
        $recursion = function($i, &$returnCfg)use(&$chainList, $namespace)
        {
            $cfgIndex = $chainList[$i];
            
            if (!isset($returnCfg[$cfgIndex])) {
                throw new F_Exception('F_Config->get 中 “'.$namespace.'” 命名空间下 “'.$cfgIndex.'” 配置项找不到'); 
            }

            if ($i < $chainTotal - 1) {
                $i++;
                return $recursion($i, $returnCfg[$cfgIndex]);
            } else {
                return $returnCfg[$cfgIndex];
            }
        };
        
        return $recursion(1, $returnCfg);
    }
}