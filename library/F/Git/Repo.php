<?php
/**
 * git 仓库 操作类
 * 
 * @category F
 * @package F_Git
 * @author allen <allenifox@163.com>
 * 
 */
final class F_Git_Repo
{
    protected $repoPath = null;
	protected $bare     = false;
	protected $envopts  = array();

	/**
	 * 创建一个 git 代码库
	 *
	 * Accepts a creation path, and, optionally, a source path
	 *
	 * @access  public
	 * @param   string  repository path
	 * @param   string  directory to source
	 * @param   string  reference path
	 * @return  GitRepo
	 */
	public static function &createNew($repoPath, $source = null, $remoteSource = false, $reference = null) 
    {
		if (is_dir($repoPath) && file_exists($repoPath."/.git") && is_dir($repoPath."/.git")) {
			throw new Exception('"'.$repoPath.'" is already a git repository');
		} else {
            
			$repo = new self($repoPath, true, false);
			if (is_string($source)) {
				if ($remote_source) {
					if (!is_dir($reference) || !is_dir($reference.'/.git')) {
						throw new Exception('"'.$reference.'" is not a git repository. Cannot use as reference.');
					} else if (strlen($reference)) {
						$reference = realpath($reference);
						$reference = "--reference $reference";
					}
					$repo->clone_remote($source, $reference);
				} else {
					$repo->clone_from($source);
				}
			} else {
				$repo->run('init');
			}
			return $repo;
		}
	}
    
    /**
	 * 构造函数
	 *
	 * @access public
	 * @param string $repoPath 仓库的本地目录路径
	 * @param bool   $createNew true=如果不存在，就创建 false=忽略
	 * @return void
	 */
	public function __construct($repoPath = null, $createNew = false, $init = true)
    {
		if (is_string($repoPath)) {
			$this->setRepoPath($repoPath, $createNew, $init);
		}
	}
    
    /**
	 * 设置仓库路径
	 *
	 * Accepts the repository path
	 *
	 * @access  public
	 * @param   string $repoPath 仓库的本地目录路径
	 * @param   bool    create if not exists?
	 * @param   bool    initialize new Git repo if not exists?
	 * @return  void
	 */
	public function setRepoPath($repoPath, $createNew = false, $init = true) 
    {
		if (is_string($repoPath)) {//是字符串
			if ($newPath = realpath($repoPath)) {//返回规范化的绝对路径名
				$repoPath = $newPath;
				if (is_dir($repoPath)) {//是目录
					if (file_exists($repoPath."/.git") && is_dir($repoPath."/.git")) {//是一个仓库目录
						$this->repoPath = $repoPath;
						$this->bare     = false;
					
					} else if (is_file($repoPath."/config")) {
                        $parseIni = parse_ini_file($repoPath."/config");
						if ($parseIni['bare']) {
							$this->repoPath = $repoPath;
							$this->bare     = true;
						}
					} else {
						if ($createNew) {
							$this->repoPath = $repoPath;
							if ($init) {//创建仓库
								$this->run('init');
							}
						} else {
							throw new Exception('"'.$repoPath.'" is not a git repository');
						}
					}
				} else {
					throw new Exception('"'.$repoPath.'" is not a directory');
				}
			} else {// $repoPath 目录不存在
				if ($createNew) {
					if ($parent = realpath(dirname($repoPath))) {
						mkdir($repoPath);
						$this->repoPath = $repoPath;
						if ($init) $this->run('init');//创建仓库
					} else {
						throw new Exception('cannot create repository in non-existent directory');
					}
				} else {
					throw new Exception('"'.$repoPath.'" does not exist');
				}
			}
		}
	}
    
    /**
	 * 获取 git 仓库目录路径（.git目录）
	 * 
	 * @access public
	 * @return string
	 */
	public function gitDirectoryPath()
    {
		return ($this->bare) ? $this->repoPath : $this->repoPath."/.git";
	}
    
    /**
	 * 测试 git 是否安装
	 *
	 * @access  public
	 * @return  bool
	 */
	public function testGit() 
    {
		$descriptorspec = array(
			1 => array('pipe', 'w'),
			2 => array('pipe', 'w'),
		);
		$pipes = array();
		$resource = proc_open(F_Git::getBin(), $descriptorspec, $pipes);

		$stdout = stream_get_contents($pipes[1]);
		$stderr = stream_get_contents($pipes[2]);
		foreach ($pipes as $pipe) {
			fclose($pipe);
		}

		$status = trim(proc_close($resource));
		return ($status != 127);
	}
    
    /**
	 * 在 git 仓库中运行命令
	 *
	 * 运行 shell 命令
	 *
	 * @access  protected
	 * @param   string  command to run
	 * @return  string
	 */
	protected function runCommand($command) 
    {
		$descriptorspec = array(
			1 => array('pipe', 'w'),
			2 => array('pipe', 'w'),
		);
		$pipes = array();
		/* Depending on the value of variables_order, $_ENV may be empty.
		 * In that case, we have to explicitly set the new variables with
		 * putenv, and call proc_open with env=null to inherit the reset
		 * of the system.
		 *
		 * This is kind of crappy because we cannot easily restore just those
		 * variables afterwards.
		 *
		 * If $_ENV is not empty, then we can just copy it and be done with it.
		 */
		if(count($_ENV) === 0) {
			$env = NULL;
			foreach($this->envopts as $k => $v) {
				putenv(sprintf("%s=%s",$k,$v));
			}
		} else {
			$env = array_merge($_ENV, $this->envopts);
		}
		$cwd = $this->gitDirectoryPath();
		$resource = proc_open($command, $descriptorspec, $pipes, $cwd, $env);

		$stdout = stream_get_contents($pipes[1]);
		$stderr = stream_get_contents($pipes[2]);
		foreach ($pipes as $pipe) {
			fclose($pipe);
		}

		$status = trim(proc_close($resource));
		if ($status) throw new Exception($stderr);

		return $stdout;
	}

	/**
	 * Run a git command in the git repository
	 *
	 * Accepts a git command to run
	 *
	 * @access  public
	 * @param   string  command to run
	 * @return  string
	 */
	public function run($command) 
    {
		return $this->runCommand(F_Git::getBin()." ".$command);
	}
    
    
//---- 以下是常用命令
    
    /**
	 * 显示有变更的文件
	 *
	 * Accept a convert to HTML bool
	 *
	 * @access public
	 * @param bool  return string with <br />
	 * @return string
	 */
	public function status($html = false) 
    {
		$msg = $this->run("status");
		if ($html == true) {
			$msg = str_replace("\n", "<br />", $msg);
		}
		return $msg;
	}
    
    /**
	 * 显示当前分支的版本历史
	 *
	 * @param strgin $format
	 * @return string
	 */
	public function log($format = null)
    {
		if ($format === null) {
			return $this->run('log');
        } else {
			return $this->run('log --pretty=format:"' . $format . '"');
        }
	}

	/**
	 * 添加指定文件 或 目录 到暂存区
	 *
	 * @access  public
	 * @param   mixed  $files 文件或目录列表，多个使用英文空格隔开
	 * @return  string
	 */
	public function add($files = "*") 
    {
		if (is_array($files)) {
			$files = '"'.implode('" "', $files).'"';
		}
		return $this->run("add $files -v");
	}

	/**
	 * $cached=false 删除工作区文件，并且将这次删除放入暂存区
     * $cached=true  停止追踪指定文件，但该文件会保留在工作区
	 *
	 * @access  public
	 * @param   mixed    $files 文件列表，多个使用英文空格隔开
	 * @param   Boolean  $cached use the --cached flag?
	 * @return  string
	 */
	public function rm($files = "*", $cached = false) 
    {
		if (is_array($files)) {
			$files = '"'.implode('" "', $files).'"';
		}
		return $this->run("rm ".($cached ? '--cached ' : '').$files);
	}

	/**
	 * $commitAll=false 提交暂存区到仓库区
     * $commitAll=true  提交工作区自上次commit之后的变化，直接到仓库区
	 *
	 * @access  public
	 * @param   string  $message 提交的描述
	 * @param   boolean $commitAll should all files be committed automatically (-a flag)
	 * @return  string
	 */
	public function commit($message = "", $commitAll = true) 
    {
		$flags = $commitAll ? '-av' : '-v';
		return $this->run("commit ".$flags." -m ".escapeshellarg($message));
	}

	/**
	 * 从本地仓库下载一个项目和它的整个代码历史
	 *
	 * @access  public
	 * @param   string  $target 需要把代码下载到的目录经路径
	 * @return  string
	 */
	public function cloneTo($target)
    {
		return $this->run("clone --local ".$this->repoPath." $target");
	}

	/**
	 * Runs a `git clone` call to clone a different repository
	 * into the current repository
	 *
	 * Accepts a source directory
	 *
	 * @access  public
	 * @param   string  source directory
	 * @return  string
	 */
	public function cloneFrom($source) 
    {
		return $this->run("clone --local $source ".$this->repoPath);
	}

	/**
	 * Runs a `git clone` call to clone a remote repository
	 * into the current repository
	 *
	 * Accepts a source url
	 *
	 * @access  public
	 * @param   string  source url
	 * @param   string  reference path
	 * @return  string
	 */
	public function cloneRemote($source, $reference)
    {
		return $this->run("clone $reference $source ".$this->repoPath);
	}

	/**
	 * Runs a `git clean` call
	 *
	 * @access  public
	 * @param   bool    delete directories?
	 * @param   bool    force clean?
	 * @return  string
	 */
	public function clean($dirs = false, $force = false) 
    {
		return $this->run("clean".(($force) ? " -f" : "").(($dirs) ? " -d" : ""));
	}

	/**
	 * Runs a `git branch` call
	 *
	 * Accepts a name for the branch
	 *
	 * @access  public
	 * @param   string  branch name
	 * @return  string
	 */
	public function createBranch($branch)
    {
		return $this->run("branch $branch");
	}

	/**
	 * Runs a `git branch -[d|D]` call
	 *
	 * Accepts a name for the branch
	 *
	 * @access  public
	 * @param   string  branch name
	 * @return  string
	 */
	public function deleteBranch($branch, $force = false)
    {
		return $this->run("branch ".(($force) ? '-D' : '-d')." $branch");
	}

	/**
	 * Runs a `git branch` call
	 *
	 * @access  public
	 * @param   bool    keep asterisk mark on active branch
	 * @return  array
	 */
	public function listBranches($keep_asterisk = false)
    {
		$branchArray = explode("\n", $this->run("branch"));
		foreach($branchArray as $i => &$branch) {
			$branch = trim($branch);
			if (! $keep_asterisk) {
				$branch = str_replace("* ", "", $branch);
			}
			if ($branch == "") {
				unset($branchArray[$i]);
			}
		}
		return $branchArray;
	}

	/**
	 * Lists remote branches (using `git branch -r`).
	 *
	 * Also strips out the HEAD reference (e.g. "origin/HEAD -> origin/master").
	 *
	 * @access  public
	 * @return  array
	 */
	public function listRemoteBranches()
    {
		$branchArray = explode("\n", $this->run("branch -r"));
		foreach($branchArray as $i => &$branch) {
			$branch = trim($branch);
			if ($branch == "" || strpos($branch, 'HEAD -> ') !== false) {
				unset($branchArray[$i]);
			}
		}
		return $branchArray;
	}

	/**
	 * 返回活跃分支的名字
	 *
	 * @access  public
	 * @param   bool    keep asterisk mark on branch name
	 * @return  string
	 */
	public function activeBranch($keep_asterisk = false) 
    {
		$branchArray = $this->listBranches(true);
		$active_branch = preg_grep("/^\*/", $branchArray);
		reset($active_branch);
		if ($keep_asterisk) {
			return current($active_branch);
		} else {
			return str_replace("* ", "", current($active_branch));
		}
	}

	/**
	 * Runs a `git checkout` call
	 *
	 * Accepts a name for the branch
	 *
	 * @access  public
	 * @param   string  branch name
	 * @return  string
	 */
	public function checkout($branch)
    {
		return $this->run("checkout $branch");
	}


	/**
	 * Runs a `git merge` call
	 *
	 * Accepts a name for the branch to be merged
	 *
	 * @access  public
	 * @param   string $branch
	 * @return  string
	 */
	public function merge($branch)
    {
		return $this->run("merge $branch --no-ff");
	}


	/**
	 * Runs a git fetch on the current branch
	 *
	 * @access  public
	 * @return  string
	 */
	public function fetch() 
    {
		return $this->run("fetch");
	}

	/**
	 * Add a new tag on the current position
	 *
	 * Accepts the name for the tag and the message
	 *
	 * @param string $tag
	 * @param string $message
	 * @return string
	 */
	public function addTag($tag, $message = null) 
    {
		if ($message === null) {
			$message = $tag;
		}
		return $this->run("tag -a $tag -m " . escapeshellarg($message));
	}

	/**
	 * List all the available repository tags.
	 *
	 * Optionally, accept a shell wildcard pattern and return only tags matching it.
	 *
	 * @access	public
	 * @param	string	$pattern	Shell wildcard pattern to match tags against.
	 * @return	array				Available repository tags.
	 */
	public function listTags($pattern = null)
    {
		$tagArray = explode("\n", $this->run("tag -l $pattern"));
		foreach ($tagArray as $i => &$tag) {
			$tag = trim($tag);
			if ($tag == '') {
				unset($tagArray[$i]);
			}
		}

		return $tagArray;
	}

	/**
	 * Push specific branch to a remote
	 *
	 * Accepts the name of the remote and local branch
	 *
	 * @param string $remote
	 * @param string $branch
	 * @return string
	 */
	public function push($remote, $branch) 
    {
		return $this->run("push --tags $remote $branch");
	}

	/**
	 * Pull specific branch from remote
	 *
	 * Accepts the name of the remote and local branch
	 *
	 * @param string $remote
	 * @param string $branch
	 * @return string
	 */
	public function pull($remote, $branch)
    {
		return $this->run("pull $remote $branch");
	}

	/**
	 * Sets the project description.
	 *
	 * @param string $new
	 */
	public function setDescription($new) 
    {
		$path = $this->gitDirectoryPath();
		file_put_contents($path."/description", $new);
	}

	/**
	 * Gets the project description.
	 *
	 * @return string
	 */
	public function getDescription() 
    {
		$path = $this->gitDirectoryPath();
		return file_get_contents($path."/description");
	}
    
    /**
     * 查看所有本地分支信息
     * 
     * @return array
     */
    public function getBranchInfoOfLocal()
    {
        $output = $this->run("branch");
        $output = trim($output);
        $output = explode(PHP_EOL, $output);
        $selected = '';
        foreach ($output as &$o) {
            $o = trim($o);
            if (preg_match('%\*%i', $o)) {
                $t = explode(' ', $o);
                $o = $t[1];
                $selected = $t[1];
            }
        }
        rsort($output);
        return array('selected' => $selected, 'branchList' => $output);
    }
    
    /**
	 * 获取分支最后提交的信息
	 *
	 * @return string
	 */
	public function getCommitInfo($hash = 'HEAD') 
    {
		$output = $this->run("rev-list --header --max-count=1 $hash");
        $output = explode(PHP_EOL, $output);
        $info   = array();
        foreach ($output as $o) {
            $o = trim($o);
            if (empty($o)) {
                continue;
            }
            $t = explode(' ', $o);
            if (count($t) <= 1) {
                if (preg_match('%^[0-9a-z]+$%i', $t[0])) {
                    $info['commitHash'] = $t[0];
                } else {
                    $info['message'] = $t[0];
                }
            } else {
                switch ($t[0]) {
                    case 'tree':
                        $info['treeHash'] = $t[1];
                        break;
                    case 'parent':
                        $info['parentHash'] = $t[1];
                        break;
                    case 'author'://作者
                        $info['author'] = array(
                            'name' => $t[1],
                            'mail' => $t[2],
                            'date' => $t[3],
                            'zone' => $t[4],
                        );
                        break;
                    case 'committer'://提交者
                        $info['committer'] = array(
                            'name' => $t[1],
                            'mail' => $t[2],
                            'date' => $t[3],
                            'zone' => $t[4],
                        );
                        break;
                }
            }
        }
        return $info;
	}

	/**
	 * Sets custom environment options for calling Git
	 *
	 * @param string key
	 * @param string value
	 */
	public function setenv($key, $value)
    {
		$this->envopts[$key] = $value;
	}
}