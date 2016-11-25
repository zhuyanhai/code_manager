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
	 * 创建一个 裸的 git 代码库
	 *
	 * Accepts a creation path, and, optionally, a source path
	 *
	 * @access  public
	 * @param   string  repository path
	 * @return  GitRepo
	 */
	public static function &createBareNew($repoPath) 
    {
		if (is_dir($repoPath) && file_exists($repoPath."/.git") && is_dir($repoPath."/.git")) {
			throw new Exception('"'.$repoPath.'" is already a git repository');
		} else {
			$repo = new self($repoPath, true, true, true);
			return $repo;
		}
	}
    
    /**
	 * 构造函数
	 *
	 * @access public
	 * @param string $repoPath 仓库的本地目录路径
	 * @param bool   $createNew true=如果不存在，就创建 false=忽略
     * @param bool    $isBare true=创建裸库 false=在工作区创建库
	 * @return void
	 */
	public function __construct($repoPath = null, $createNew = false, $init = true, $isBare = false)
    {
		if (is_string($repoPath)) {
			$this->setRepoPath($repoPath, $createNew, $init, $isBare);
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
     * @param   bool    $isBare true=创建裸库 false=在工作区创建库
	 * @return  void
	 */
	public function setRepoPath($repoPath, $createNew = false, $init = true, $isBare = false) 
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
                        Utils_File::dirCreate($repoPath);
						$this->repoPath = $repoPath;
						if ($init) {
                            if ($isBare) {
                                $this->bare = true;
                                $this->run('init --bare');//创建裸仓库
                            } else {
                                $this->run('init');//在工作区创建仓库
                            }
                        }
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
	public function gitCommitInfo($hash = 'HEAD') 
    {
		$output = $this->run("rev-list --header --max-count=1 $hash");
        $output = explode(PHP_EOL, $output);  
        $info   = array();
        foreach ($output as $o) {
            if (substr($o, 0, 4) === '    ') {//message
                $info['message'] = trim($o);
                continue;
            }
            $o = trim($o);
            if (empty($o)) {
                continue;
            }
            
            $t = explode(' ', $o);
            if (count($t) <= 1) {
                if (preg_match('%^[0-9a-z]+$%i', $t[0])) {
                    $info['commitHash'] = $t[0];
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
     * 获取本地引用
     * 
     * @param boolean $tags
     * @param boolean $heads
     * @param boolean $remotes
     * @return array
     */
    public function gitRefList($tags = true, $heads = true, $remotes = true)
    {
        $cmd = "show-ref --dereference";
        if (!$remotes) {
            if ($tags) { $cmd .= " --tags"; }
            if ($heads) { $cmd .= " --heads"; }
        }
        
        $result = array();
        $output = $this->run($cmd);
        $output = explode(PHP_EOL, $output);
        foreach ($output as $line) {
            $line = trim($line);
            if (empty($line)) {
                continue;
            }
            // <hash> <ref>
            $parts = explode(' ', $line, 2);
            $name = str_replace(array('refs/', '^{}'), array('', ''), $parts[1]);
            $result[$parts[0]][] = $name;
        }
        return $result;
    }
    
    /**
     * 获取 版本范围
     * 
     * @param int $skip
     * @param int $max_count
     * @param string $start
     * @return array
     */
    public function gitGetRevList($skip = 0, $max_count = null, $start = 'HEAD')
    {
        $cmd = "rev-list ";
        if ($skip != 0) {
            $cmd .= "--skip=$skip ";
        }
        if (!is_null($max_count)) {
            $cmd .= "--max-count=$max_count ";
        }
        $cmd .= $start;
        
        $result = array();
        $output = $this->run($cmd);
        $output = explode(PHP_EOL, $output);
        foreach ($output as $line) {
            $line = trim($line);
            if (empty($line)) {
                continue;
            }
            $result[] = $line;
        }
        return $result;
    }
    
    /**
     * 获取某个分支节点的提交历史计数
     * 
     * @param string $hash
     * return array
     */
    public function getCommitHistoryCount($hash = 'HEAD')
    { 
        $cmd = "rev-list --count {$hash}";
        $output = $this->run($cmd);
        $output = trim($output, PHP_EOL);
        $output = trim($output);
        return $output;
    }
    
    /**
     * 获取某个分支节点的提交历史 - 分页
     * 
     * @param int $page
     * @param int $count
     * @param string $hash
     * return array
     */
    public function getCommitHistory($page, $count = 50, $hash = 'HEAD')
    {
        $refsByHash = $this->gitRefList();
        $refsByHashKeyArray = array_keys($refsByHash);
        $result  = array();
        $revList = $this->gitGetRevList(($page-1) * $count, $count, $hash);
        foreach ($revList as $rev) {
            $info = $this->gitCommitInfo($rev);
            $refs = array();
            if (in_array($rev, $refsByHashKeyArray)) {
                $refs = $refsByHash[$rev];
            }

            $result[] = array(
                'author'    => $info['author']['name'],
                'date'      => $info['author']['date'],
                'message'   => $info['message'],
                'commitId'  => $rev,
                'tree'      => $info['treeHash'],
                'refs'      => $refs,
            );
        }
        $total = $this->getCommitHistoryCount($refsByHashKeyArray[0]);
        return array('list' => $result, 'total' => $total);
    }
    
    /**
     * 获取某个提交节点的内容列表
     * 
     * @param string $hash
     */
    public function getCommitContentList($hash)
    {
        // git log -p --max-count=1 d373facd8a031a92456b0d87031e8833787824dc | awk '/^diff/{ print FNR "\t" $0 }'|sed 's/ /\t/g'|sed 's/diff\|--git\|a\///g'|awk '{print $1" "$2}'
        
        //获取指定 commit 的内容文件列表
//        $cmd = "show --pretty=\"format:\" --name-only {$hash}";
//        $affectedFiles = $this->run($cmd);
//        $affectedFiles = trim($affectedFiles, PHP_EOL);
//        $affectedFiles = trim($affectedFiles);
//        $affectedFiles = explode(PHP_EOL, $affectedFiles);

        //获取指定 commit 的内容文件列表和修改行号
        $cmd = "log -p --max-count=1 {$hash} | awk '/^diff/{ print FNR \"\t\" $0 }'| sed 's/ /\t/g'| sed 's/diff\|--git\|a\///g'| awk '{print $1\" \"$2}'";
        $affectedFiles = $this->run($cmd);
        $affectedFiles = trim($affectedFiles, PHP_EOL);
        $affectedFiles = trim($affectedFiles);
        $affectedFiles = explode(PHP_EOL, $affectedFiles);
        foreach ($affectedFiles as &$af) {
            $af = explode(' ', $af);
        }
        
        //获取指定 commit 的内容文件列表的每个文件的修改统计信息
        //git log --stat --max-count=1 068a23cbfff0cbc530d6110ceb1ed49997b5e8d8
        $cmd = "log --stat --max-count=1 --pretty=\"format:\" {$hash}";
        $amendInfos = $this->run($cmd);
        $amendInfos = trim($amendInfos, PHP_EOL);
        $amendInfos = trim($amendInfos);
        $amendInfos = explode(PHP_EOL, $amendInfos);
        $amendFiles = array();
        foreach ($amendInfos as $k=>$v) {
            $v = trim($v);
            $v = explode('|', $v);
            try{
                if (count($v) > 1) {
                    $c = trim($v[1]);
                    $c = explode(' ', $c);
                    if (count($c) > 1) {
                        if ($c[0] === 'Bin') {
                            $c[0] = $c[1];
                            $insertions = 0;
                            $deletion   = 0;
                        } else {
                            $insertions = substr_count($c[1], '+');
                            $deletion   = substr_count($c[1], '-');
                        }
                    } else {
                        $insertions = 0;
                        $deletion   = 0;
                    }
                    $t = $insertions + $deletion;
                    $normalBlock = 5;
                    $insertionsBlock = ($insertions > 0)?floor($insertions / $t * $normalBlock):0;
                    $deletionBlock = ($deletion > 0)?floor($deletion / $t * $normalBlock):0;
                    $amendFiles[trim($affectedFiles[$k][1])] = array(
                        'total'           => trim($c[0]),
                        'insertions'      => $insertions,
                        'insertionsBlock' => $insertionsBlock,
                        'deletion'        => $deletion,
                        'deletionBlock'   => $deletionBlock,
                        'normalBlock'     => $normalBlock - $insertionsBlock - $deletionBlock,
                        'beginLineNum'    => $affectedFiles[$k][0],
                        'endLineNum'      => (isset($affectedFiles[$k+1]))?($affectedFiles[$k+1][0]-1):null,
                    );
                }
            } catch(Exception $e) {
                print_r($v).PHP_EOL;
                exit;
            }
        }

        $result = array();
        foreach ($affectedFiles as $file) {
            // The format above contains a blank line; Skip it.
            if ($file[1] == '') {
                continue;
            }
            
            //获取commit的内容列表中的每个文件的hash值
//            $output = $this->run("ls-tree {$hash} {$file[1]}");
//            $output = trim($output, PHP_EOL);
//            $output = trim($output);
//            if (empty($output)) {
//                continue;
//            }
//
//            $output = explode(PHP_EOL,$output);
//            foreach ($output as $line) {
//                try {
//                    $parts = preg_split('/\s+/', $line, 4);
//                    $result[] = array('name' => $parts[3], 'hash' => $parts[2], 'statistics' => $amendFiles[$parts[3]]);
//                } catch(Exception $e) {
//                    echo $line;
//                    echo "ls-tree {$hash} {$file[1]}";
//                    //print_r($amendFiles);
//                    exit;
//                }
//            }
            
            $result[] = array('name' => $file[1], 'statistics' => $amendFiles[$file[1]]);
        }
        
        //print_r($result);exit;
        return array('commitIdHash' => $hash, 'list' => $result);
    }
    
    /**
     * 获取commit 的某个文件的 diff 内容
     * 
     * @param string $hash
     * @param int $bl
     * @param int $el
     * @return string
     */
    public function getDiffOfNearest($hash, $bl, $el)
    {
        if (is_null($el)) {
            $cmd = 'log -p --max-count=1 '.$hash.' | awk "NR>='.$bl.'{print}"';
        } else {
            $cmd = "log -p --max-count=1 {$hash} | sed -n {$bl},{$el}p";
        }
        
        $output = $this->run($cmd);
        $output = trim($output);
        $output = substr($output, strpos($output, '@@'));
        $output = explode(PHP_EOL, $output);
        $html = '<table border="0" style="width:100%">';
        $delColumnLine = 0;
        $addColumnLine = 0; 
        foreach ($output as $k=>&$o) {
            $o = trim($o);
            if (preg_match('%^@@([\s-\+0-9,]+)@@$%i', $o, $matches)) {
                
                $matches[1] = trim($matches[1]);
                $matches[1] = explode(' ', $matches[1]);
                
                $matches[1][0] = ltrim($matches[1][0], '-');
                $matches[1][0] = explode(',', $matches[1][0]);
                $delColumnLine = $matches[1][0][0];
                
                $matches[1][1] = ltrim($matches[1][1], '+');
                $matches[1][1] = explode(',', $matches[1][1]);
                $addColumnLine = $matches[1][1][0];
                
                $html .= "<tr class=\"init-line\"><td class=\"code-rborder\">...</td><td class=\"code-rborder\">...</td><td>";
                $html .= '<code class="code-init">'.$o.'</code>';
            } else {
                if (preg_match("%^-([^".PHP_EOL."]*)%i", $o)) {
                    $html .= "<tr class=\"removed-line\"><td class=\"code-rborder\">{$delColumnLine}</td><td class=\"code-rborder\"></td><td>";
                    $html .= '<code class="removed-code">'.$o.'</code>';
                    $delColumnLine++;
                } else if (preg_match("%^\+([^".PHP_EOL."]*)%i", $o)) {
                    $html .= "<tr class=\"add-line\"><td class=\"code-rborder\"></td><td class=\"code-rborder\">$addColumnLine</td><td>";
                    $html .= '<code class="add-code">'.$o.'</code>';
                    $addColumnLine++;
                } else {
                    $html .= "<tr class=\"normal-code\"><td class=\"code-rborder\">$delColumnLine</td><td class=\"code-rborder\">$addColumnLine</td><td>";
                    $html .= '<code class="normal-code">'.$o.'</code>';
                    $delColumnLine++;
                    $addColumnLine++;
                } 
            }

            $html .= '</td></tr>';
        }
        $html .= '</table>';
        unset($output);
        return $html;
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