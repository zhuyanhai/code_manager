<?php
/**
 * 数据表配置类
 * 
 * 每个数据表必须有,没有就好报错
 * 
 */
final class Dao_CodeManager_Project_Config
{   
    public static $configs = array(
        //数据行类名
        'rowClassName' => 'Dao_CodeManager_Project',
        //完整表名
        'tableName'    => 'tbl_project',
        //数据库缩略名,对应 db.cfg.php 配置文件
        'dbShortName'  => 'code_manager',
        //主键
        'primaryKey'   => 'id',
    );
}

/**
 * tbl_project 数据表类
 * 
 * 项目
 * 
 * @package Dao
 * @subpackage Dao_CodeManager
 * @author allen <allenifox@163.com>
 */
class Dao_CodeManager_Project extends Dao_Abstract
{
    /**
     * 判断项目是否有效
     * 
     * @return boolean
     */
    public function ___isValid()
    {
        if (!isset($this->status)) {
            return false;
        }
        
        if (intval($this->status) === 0) {
            return true;
        }
        return false;
    }
    
    /**
     * 格式化最后一次项目的启动时间
     * 
     * @return string
     */
    public function ___showLastBeginTime()
    {
        if (!isset($this->last_begin_time) || empty($this->last_begin_time)) {
            return '';
        }
        return date('Y-m-d H:i:s', $this->last_begin_time);
    }
    
    /**
     * 格式化最后一次项目的完成时间
     * 
     * @return string
     */
    public function ___showLastEndTime()
    {
        if (!isset($this->last_end_time) || empty($this->last_end_time)) {
            return '';
        }
        return date('Y-m-d H:i:s', $this->last_end_time);
    }
}