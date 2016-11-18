<?php
/**
 * 数据表配置类
 * 
 * 每个数据表必须有,没有就好报错
 * 
 */
final class Dao_CodeManager_ProjectUser_Config
{   
    public static $configs = array(
        //数据行类名
        'rowClassName' => 'Dao_CodeManager_ProjectUser',
        //完整表名
        'tableName'    => 'tbl_project_user',
        //数据库缩略名,对应 db.cfg.php 配置文件
        'dbShortName'  => 'code_manager',
    );
}

/**
 * tbl_project_user 数据表类
 * 
 * 项目的用户权限
 * 
 * @package Dao
 * @subpackage Dao_CodeManager
 * @author allen <allenifox@163.com>
 */
class Dao_CodeManager_ProjectUser extends Dao_Abstract
{

}