<?php
/**
 * PDO 封装类
 * 
 * @method string lastInsertId (([ string $name= NULL ] ) 返回最后insert的行的主键值
 */
final class F_Db_Pdo
{   
    /**
     * 当前要用到的数据库连接配置
     * 
     * @var array 
     */
    private $_dbConnectCfg = array();
    
    /**
     * 当前数据库的链接句柄
     * 
     * @var PDO
     */
    private $_dbHandel = null;
    
    /**
     * 当前数据库的stmt句柄
     * 
     * @var PDOStatement
     */
    private $_stmtHandel = null;
    
    /**
     * 需要连接到的服务器
     * 
     * @var string
     */
    private $_connectServer = 'master';
    
    /**
     * 单例模式,获取PDO实例
     * 
     * @staticvar F_Db_Pdo $instance
     * @return \F_Db_Pdo
     */
    public static function getInstance()
    {
        static $instance = null;
        
        if (is_null($instance)) {
            $instance = new F_Db_Pdo();
        }
        
        return $instance;
    }
    
    /**
     * 析构函数
     */
    public function __destruct()
    {
        $this->_dbHandel   = null;
        $this->_stmtHandel = null;
    }
    
    /**
     * 主动关闭数据库连接
     * 
     * @return \F_Db_Pdo
     */
    public function close()
    {
        $this->_dbHandel   = null;
        $this->_stmtHandel = null;
        return $this;
    }
    
    /**
     * 设置当前要用到的数据库连接配置
     * 
     * @param array $dbConnectCfg
     * @return \F_Db_Pdo
     */
    public function setDbConnectCfg($dbConnectCfg)
    {
        $this->_dbConnectCfg = $dbConnectCfg;
        return $this;
    }
    
    /**
     * 插入数据
     * 
     * @param array $rowData 需要插入的数据
     * @param string $tableName 数据表名字[格式：db.table]
     * @return string
     */
    public function insert($rowData, $tableName)
    {
        $fields = array_keys($rowData);
        $this->prepare('INSERT INTO '. $tableName . ' ('.implode(',', $fields).') VALUES (:'.implode(',:', $fields).')');
        foreach ($rowData as $k => $v) {
            $this->bindParam(':'.$k, $v, PDO::PARAM_STR);
        }
        return $this->execute()->lastInsertId();
    }
    
    /**
     * 插入数据
     * 
     * @param array $dataList 需要插入的数据数组
     * @param string $tableName 数据表名字[格式：db.table]
     * @param boolean $ignore
     * @return string
     */
    public function insertOfMulti($dataList, $tableName, $ignore)
    {
        $keys   = array_keys($dataList[0]);

		$prepareSql  = "INSERT " .( $ignore ? 'IGNORE ' : '') . "INTO " . $tableName;
		$prepareSql .= " (" . implode(',', $keys)." ) VALUES ";
		$prepareSql .= implode(',', array_fill(0, count($dataList), "(". implode(',', array_fill(0, count($keys), '?') ) .") "));

		$this->prepare($prepareSql);
        
        $inputParameters = array();
		foreach($dataList as $rowData) {
			foreach ($rowData as $v) {
                array_push($inputParameters, $v);
            }
		}

		return $this->execute($inputParameters);
    }
    
    /**
     * 更新数据
     * 
     * @param array $rowData 需要更新的数据
     * @param string $whereCondition 更新条件
     * @param array $whereBind 更新条件绑定数据
     * @param string $tableName 数据表名字[格式：db.table]
     * @return int
     * @throws PDOException
     */
    public function update($rowData, $whereCondition, $whereBind, $tableName)
    {
        $fields = array_keys($rowData);

        $sql = 'UPDATE ' . $tableName . ' SET ';
        foreach ($rowData as $rk => $rv) {
            $sql .= $rk . '=:' . $rk . ',';
        }
        $sql = rtrim($sql, ',');
        $sql .= ' WHERE ' . $whereCondition; 

        $this->prepare($sql);
        
        foreach ($rowData as $rk => $rv) {
            $this->bindParam(':'.$rk, $rv, PDO::PARAM_STR);
        }
        foreach ($whereBind as $wk => $wv) {
            if (!preg_match('%'.$wk.'%', $whereCondition)) {
                throw new PDOException('Pdo update where bindParam ['.$wk.'] not exist');
            }
            $this->bindParam($wk, $wv, PDO::PARAM_STR);
        }
        return $this->execute()->_stmtHandel->rowCount();
    }
    
    /**
     * 删除数据
     * 
     * @param string $whereCondition 更新条件
     * @param array $whereBind 更新条件绑定数据
     * @param string $tableName 数据表名字[格式：db.table]
     * @return int
     * @throws PDOException
     */
    public function delete($whereCondition, $whereBind, $tableName)
    {
        $sql = 'DELETE FROM ' . $tableName;
        $sql .= ' WHERE ' . $whereCondition; 
        $this->prepare($sql);
        foreach ($whereBind as $wk => $wv) {
            if (!preg_match('%'.$wk.'%', $whereCondition)) {
                throw new PDOException('Pdo delete where bindParam ['.$wk.'] not exist');
            }
            $this->bindParam($wk, $wv, PDO::PARAM_STR);
        }
        return $this->execute()->_stmtHandel->rowCount();
    }

    /**
     * prepare 预处理sql
     * 
     * @param string $sql
     * @return \F_Db_Pdo
     * @throws PDOException
     */
    public function prepare($sql)
    {
    	$this->_connect();
        try {
            $this->_stmtHandel = $this->_dbHandel->prepare($sql);
            if (!$this->_stmtHandel) {
                throw new PDOException('Pdo prepare error');
            }
        } catch (PDOException $e) {
            throw new PDOException('Pdo prepare error: '.$e->getMessage());
        }
    	return $this; 
    }
    
    /**
     * 绑定参数
     * 
     * @param string $parameter
     * @param mixed $variable
     * @return \F_Db_Pdo
     */
    public function bindParam($parameter , $variable = null, $dataType = PDO::PARAM_STR)
    {
        $this->_stmtHandel->bindParam($parameter, $variable, $dataType);
        return $this;
    }
    
    /**
     * 执行
     * 
     * @param array|null $inputParameters
     * @return \F_Db_Pdo
     * @throws F_Db_Exception
     */
    public function execute(array $inputParameters = null)
    {
        if (!$this->_stmtHandel->execute($inputParameters)) {
            $error = $this->_stmtHandel->errorInfo();
            throw new F_Db_Exception('execute failed : '.$error[1].' '.$error[2]);
        }
        return $this;
    }
    
    /**
     * 获取单行记录
     * 
     * @return mixed
     * @throws F_Db_Exception
     */
    public function fetchRow()
    {
        if (!$this->_stmtHandel->execute()) {
            $error = $this->_stmtHandel->errorInfo();
            throw new F_Db_Exception('execute failed : '.$error[1].' '.$error[2]);
        }
        return $this->_stmtHandel->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * 获取多行记录
     * 
     * @return array
     * @throws F_Db_Exception
     */
    public function fetchAll()
    {
        if (!$this->_stmtHandel->execute()) {
            $error = $this->_stmtHandel->errorInfo();
            throw new F_Db_Exception('execute failed : '.$error[1].' '.$error[2]);
        }
        return $this->_stmtHandel->fetchAll(PDO::FETCH_ASSOC);
    }
    
     /**
     * 主动切换使用的【主】数据库连接
     * 
     * @return \F_Db_Pdo
     */
    public function changeMaster()
    {
        $this->_connectServer = 'master';
        $this->_dbHandel      = null;
        $this->_stmtHandel    = null;
        return $this;
    }
    
    /**
     * 主动切换使用的【从】数据库连接
     * 
     * @return \F_Db_Pdo
     */
    public function changeSlave()
    {
        $this->_connectServer = 'slave';
        $this->_dbHandel      = null;
        $this->_stmtHandel    = null;
        return $this;
    }
    
    /**
     * 魔术方法，调用PDO对象中的方法
     * 
     * @param string $method
     * @param array $args
     * @return mixed
     */
    public function __call($method, $args)
    {
        $this->_connect();
    	return call_user_func_array(array($this->_dbHandel, $method), $args);
    }
    
    /**
     * 连接数据库
     * 
     * @throws F_Db_Exception
     */
    private function _connect()
    {
        if (is_null($this->_dbHandel)) {
            if (empty($this->_dbConnectCfg)) {
                throw new F_Db_Exception('$this->_dbConnectCfg 不能为空');
            }
            try {
                $cfg = $this->_dbConnectCfg[$this->_connectServer];
                if (!isset($cfg['host']) || !isset($cfg['port']) || !isset($cfg['username']) || !isset($cfg['password']) || !isset($cfg['charset'])) {
                    throw new F_Db_Exception('Connection failed: params error');
                }

                $dsn = 'mysql:host='.$cfg['host'].';port='.$cfg['port'];
                if (isset($cfg['socket']) && !empty($cfg['socket'])) {
                    $dsn .=  ';unix_socket='.$cfg['socket'];
                }
                
                $this->_dbHandel = new PDO($dsn, $cfg['username'], $cfg['password'], array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \''.$cfg['charset'].'\''));
                $this->_dbHandel->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
                $this->_dbHandel->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                throw new F_Db_Exception('Connection failed: '.$e->getMessage());
            }
        }
    }
}