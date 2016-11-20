<?php
/**
 * 数据表配置类
 * 
 * 每个数据表必须有,没有就好报错
 * 
 */
final class Dao_CodeManager_ProjectRepo_Config
{   
    public static $configs = array(
        //数据行类名
        'rowClassName' => 'Dao_CodeManager_ProjectRepo',
        //完整表名
        'tableName'    => 'tbl_project_repo',
        //数据库缩略名,对应 db.cfg.php 配置文件
        'dbShortName'  => 'code_manager',
        //主键
        'primaryKey'   => 'id',
    );
}

/**
 * tbl_project_repo 数据表类
 * 
 * 项目仓库信息
 * 
 * @package Dao
 * @subpackage Dao_CodeManager
 * @author allen <allenifox@163.com>
 */
class Dao_CodeManager_ProjectRepo extends Dao_Abstract
{
    public function ___showHTTPPath()
    {
        if (!isset($this->repo_path)) {
            return '';
        }
        
        F_Config::load('/configs/repo.cfg.php');
        $repoRootPath = F_Config::get('repo.root');
        $repoPath = preg_replace('%'.$repoRootPath.'%i', '', $this->repo_path);
        $repoPath = ltrim($repoPath, '/');
        return 'http://' . Utils_Http::getServerIp(). ':3000/' . $repoPath;
    }
    
    public function ___showSSHPath()
    {
        if (!isset($this->repo_path)) {
            return '';
        }

        F_Config::load('/configs/repo.cfg.php');
        $repoRootPath = F_Config::get('repo.root');
        $repoPath = preg_replace('%'.$repoRootPath.'%i', '', $this->repo_path);
        $repoPath = ltrim($repoPath, '/');
        return 'root@' . Utils_Http::getServerIp(). ':' . $repoPath;
    }
}