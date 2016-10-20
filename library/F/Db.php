<?php
/**
 * DB类
 * 
 * @category F
 * @package F_Db
 * @author allen <allenifox@163.com>
 * 
 */
final class F_Db
{
    /**
     * 数据库连接需要用到的配置
     * 
     * @var array 
     */
    private $_dbConnectCfg = array();
    
    /**
     * 数据表配置
     * 
     * @var array
     */
    private $_tableConfigs = array();
            
    /**
     * 获取类实例
     *
     * @staticvar F_Db $instance
     * @return \F_Db
     */
    public static function getInstance()
    {
        static $instance = null;
        if(null == $instance){
            $instance = new self();
        }
        return $instance;
    }

    /**
     * 获取构造 select 语句的对象
     * 
     * @return \F_Db_Table_Select
     */
    public function getSelect()
    {
        $this->_initDbConfig($this->_tableConfigs['dbShortName']);
        $dbName = $this->_dbConnectCfg[$this->_tableConfigs['dbShortName']]['dbName'];
        $this->_tableConfigs['dbFullName'] = $dbName;
        return F_Db_Table_Select::getInstance()->___initTableConfigs($this->_tableConfigs)->___cleanQueryCondition();
    }
    
    /**
     * 获取构造 insert 语句的对象
     * 
     * @return \F_Db_Table_Insert
     */
    public function getInsert()
    {
        $this->_initDbConfig($this->_tableConfigs['dbShortName']);
        $dbName = $this->_dbConnectCfg[$this->_tableConfigs['dbShortName']]['dbName'];
        $this->_tableConfigs['dbFullName'] = $dbName;
        return F_Db_Table_Insert::getInstance()->___initTableConfigs($this->_tableConfigs);
    }
    
    /**
     * 获取构造 update 语句的对象
     * 
     * @return \F_Db_Table_Update
     */
    public function getUpdate()
    {
        $this->_initDbConfig($this->_tableConfigs['dbShortName']);
        $dbName = $this->_dbConnectCfg[$this->_tableConfigs['dbShortName']]['dbName'];
        $this->_tableConfigs['dbFullName'] = $dbName;
        return F_Db_Table_Update::getInstance()->___initTableConfigs($this->_tableConfigs);
    }
    
    /**
     * 获取PDO数据库操作对象
     * 
     * @return \F_Db_Pdo
     */
    public function getPdo()
    {
        $this->_initDbConfig($this->_tableConfigs['dbShortName']);
        return F_Db_Pdo::getInstance()->setDbConnectCfg($this->_dbConnectCfg[$this->_tableConfigs['dbShortName']]);
    }
    
    /**
     * 切换链接的服务器,并返回PDO对象
     * 
     * @param null|string $connectServer null=自动选择 master=主库 slave=从库
     * @return \F_Db_Pdo
     */
    public function changeConnectServer($connectServer)
    {
        $pdo = $this->getPdo();
        if (is_null($connectServer)) {
            $pdo->changeMaster();
        } else {
            if ($connectServer === 'master') {
                $pdo->changeMaster();
            } else {
                $pdo->changeSlave();
            }
        }
        return $pdo;
    }
    
    /**
     * 第一次访问数据库时，初始bulid数据库连接需要的配置
     */
    private function _initDbConfig($dbShortName)
    {
        static $defaultParams = array();
        
        if (!isset($this->_dbConnectCfg[$dbShortName])) {//配置初始加载
            F_Config::load('/configs/db.cfg.php');
            if (empty($defaultParams)) {
                $defaultParams = F_Config::get('db.default');
            }
            $dbConfigs = F_Config::get('db.'.$dbShortName);
            $this->_dbConnectCfg[$dbShortName]['dbName'] = $dbConfigs['dbName'];
            if (isset($dbConfigs['params'])) {
                $params = array_merge($defaultParams, $dbConfigs['params']);
            } else {
                $params = $defaultParams;
            }
            $this->_dbConnectCfg[$dbShortName]['master'] = $params['master'];
            $this->_dbConnectCfg[$dbShortName]['slave']  = $params['slave'];
            unset($params, $dbConfigs);
        }
    }
    
    /**
     * 初始化数据表配置
     * 
     * @param array $tableConfigs 数据表配置
     * @return \F_Db
     */
    public function ___initTableConfigs($tableConfigs)
    {
        $this->_tableConfigs = $tableConfigs;
        
        return $this;
    }
}