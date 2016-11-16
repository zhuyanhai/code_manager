<?php
/**
 * 数据表配置类
 * 
 * 每个数据表必须有,没有就好报错
 * 
 */
final class Dao_CodeManager_Config_Config
{   
    public static $configs = array(
        //数据行类名
        'rowClassName' => 'Dao_CodeManager_Config',
        //完整表名
        'tableName'    => 'tbl_config',
        //数据库缩略名,对应 db.cfg.php 配置文件
        'dbShortName'  => 'code_manager',
        //主键
        'primaryKey'   => 'key',
        /**
         * key-value 形式缓存整个数据表 - 行数据
         *
         * - server  参考 application.ini -> resources.cachemanager.{user}
         * - key     格式说明：kv_table_{表名【取消前缀yr_eap 和 _】}_{版本号【便于以后手动改代码刷新缓存，新增字段后上线来不及写脚本刷新时使用，非不得已不要用，影响效率】}_{主键或指定类似主键的唯一字段}
         * - expires 过期时间，参考 application.ini -> resources.cachemanager.user.frontend.options.lifetime 时间为秒，null 是永不过期
         * - field   kv table 使用的字段名
         * - saveFields memcache 中存储需要明确知道的字段，无或空代表整行数据
         * - keyEncrypt key的加密方式，默认=无 例如：md5
         *
         */
        'memcache' => array(
            'key' => array(
                'server'  => 'user',
                'key'     => 'kv_table_config_1_%d',
                'field'   => 'key',
                'expires' => null,
            ),
        ),
    );
}

/**
 * tbl_config 数据表类
 * 
 * 辅助程序的配置存储表
 * 
 * @package Dao
 * @subpackage Dao_CodeManager
 * @author allen <allenifox@163.com>
 */
class Dao_CodeManager_Config extends Dao_Abstract
{
    /**
     * 根据解析模式获取值
     * 
     * @return mixed
     */
    public function getVal()
    {
        switch (intval($this->parse_mode)) {
            case 1:
                return intval($this->val);
                break;
            case 2:
                return strval($this->val);
                break;
            case 3:
                return json_decode($this->val, true);
                break;
        }
    }
    
    /**
     * 记录状态是否有效
     * 
     * @return boolean true=有效 false=无效
     */
    public function isValid()
    {
        if (intval($this->status) === 0) { 
            return true;
        }
        return false;
    }
}