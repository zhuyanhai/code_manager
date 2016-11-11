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
    
}