<?php
/**
 * 数据表配置类
 * 
 * 每个数据表必须有,没有就好报错
 * 
 */
final class Dao_CodeManager_Org_Config
{   
    public static $configs = array(
        //数据行类名
        'rowClassName' => 'Dao_CodeManager_Org',
        //完整表名
        'tableName'    => 'tbl_org',
        //数据库缩略名,对应 db.cfg.php 配置文件
        'dbShortName'  => 'code_manager',
        //主键
        'primaryKey'   => 'id',
    );
}

/**
 * tbl_org 数据表类
 * 
 * 组织机构信息
 * 
 * @package Dao
 * @subpackage Dao_CodeManager
 * @author allen <allenifox@163.com>
 */
class Dao_CodeManager_Org extends Dao_Abstract
{
    
}