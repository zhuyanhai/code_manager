<?php
/**
 * 数据表配置类
 * 
 * 每个数据表必须有,没有就好报错
 * 
 */
final class Dao_CodeManager_ProjectCode_Config
{   
    public static $configs = array(
        //数据行类名
        'rowClassName' => 'Dao_CodeManager_ProjectCode',
        //完整表名
        'tableName'    => 'tbl_project_code',
        //数据库缩略名,对应 db.cfg.php 配置文件
        'dbShortName'  => 'code_manager',
        //主键
        'primaryKey'   => 'id',
    );
}

/**
 * tbl_project 数据表类
 * 
 * 项目代码
 * 
 * @package Dao
 * @subpackage Dao_CodeManager
 * @author allen <allenifox@163.com>
 */
class Dao_CodeManager_ProjectCode extends Dao_Abstract
{
    /**
     * 判断项目代码记录是否有效
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
     * 项目代码类型
     * 
     * @return string
     */
    public function ___showType()
    {
        if (!isset($this->type)) {
            return '';
        }
        
        switch (intval($this->type)) {
            case 1:
                return 'master';
                break;
            case 2:
                return 'integration';
                break;
            case 3:
                return 'develop';
                break;
            case 4:
                return 'hotfix';
                break;
        }
    }
    
    /**
     * 项目代码阶段
     * 
     * @return string
     */
    public function ___showStep()
    {
        if (!isset($this->step)) {
            return '';
        }
        
        switch (intval($this->step)) {
            case 0:
                return '分支被创建';
                break;
            case 1:
                return '进入开发';
                break;
            case 2:
                return '线下测试';
                break;
            case 3:
                return '线上测试';
                break;
            case 4:
                return '上线';
                break;
        }
    }
    
    /**
     * 格式化项目代码创建
     * 
     * @return string
     */
    public function ___showCreateTime()
    {
        if (!isset($this->create_time) || empty($this->create_time)) {
            return '';
        }
        return date('Y-m-d H:i:s', $this->create_time);
    }
    
}