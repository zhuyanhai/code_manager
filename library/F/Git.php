<?php
/**
 * git 操作类
 * 
 * @category F
 * @package F_Git
 * @author allen <allenifox@163.com>
 * 
 */
final class F_Git
{
    /**
     * git 可执行文件
     * 
     * @var string
     */
    private static $_bin = '/usr/bin/git';
    
    /**
     * 设置 git 的可执行文件
     * 
     * @param string $binPath 设置git的可执行文件
     */
    public static function setBin($binPath)
    {
        self::$_bin = $binPath;
    }
    
    /**
     * 获取 git 的可执行文件
     * 
     * @return string
     */
    public static function getBin()
    {
        return self::$_bin;
    }
    
    /**
	 * 创建一个Git创库
	 *
     * @param   bool    $isBare true=创建裸库 false=在工作区创建库
	 * @param   string  $repoPath 仓库路径
	 * @param   string  $source 
	 * @return  F_Git_Repo
	 */
	public static function &create($isBare, $repoPath, $source = null)
    {
        if ($isBare) {
            return F_Git_Repo::createBareNew($repoPath);
        } else {
            return F_Git_Repo::createNew($repoPath, $source);
        }
	}
    
    /**
	 * Clones a remote repo into a directory and then returns a GitRepo object
	 * for the newly created local repo
	 *
	 * Accepts a creation path and a remote to clone from
	 *
	 * @access  public
	 * @param   string  repository path
	 * @param   string  remote source
	 * @param   string  reference path
	 * @return  F_Git_Repo
	 **/
	public static function &cloneRemote($repoPath, $remote, $reference = null)
    {
		return F_Git_Repo::createNew($repoPath, $remote, true, $reference);
	}

	/**
	 * Open an existing git repository
	 *
	 * Accepts a repository path
	 *
	 * @access  public
	 * @param   string  repository path
	 * @return  F_Git_Repo
	 */
	public static function open($repoPath)
    {
		return new F_Git_Repo($repoPath);
	}

	/**
	 * Checks if a variable is an instance of GitRepo
	 *
	 * Accepts a variable
	 *
	 * @access  public
	 * @param   mixed   variable
	 * @return  bool
	 */
	public static function isRepo($var) 
    {
		return (get_class($var) == 'F_Git_Repo');
	}
}