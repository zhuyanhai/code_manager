<?php
/**
 * DB select 类
 *
 * - 专门负责 select 的所有构造操作
 */
final class F_Db_Table_Select
{
    /**
     * 数据表配置
     * 
     * @var array
     */
    private $_tableConfigs = array();
    
    /**
     * 构造成 sql 前的各种条件
     * 
     * 例如 column 或 where
     * 
     * @var array
     */
    private $_queryConditions = array();
    
    private $_queryConditionsInit = array(
        'columns' => '*',
        'where'   => array(
            'expression' => '',
            'bindParams' => array(),
        ),
    );
    
    /**
     * 获取类实例
     *
     * @staticvar F_Db_Table_Select $instance
     * @return \F_Db_Table_Select
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
     * 设置需要查询出来的列
     * 
     * $param string $columns 本次 select 查询需要获取的列
     * @return \F_Db_Table_Select
     */
    public function fromColumns($columns = '*')
    {
        if (empty($columns)) {
            $columns = '*';
        }
        $this->_queryConditions['columns'] = $columns;
        return $this;
    }
    
    /**
     * 查询条件
     * 
     * @param string $columnExpression
     * return \F_Db_Table_Select
     */
    public function where($columnExpression)
    {
        //获取函数的所有参数列表
        $args = func_get_args();
        //表达式所需要填充的变量的启始索引
        $paramIndex = 1;
        //参数总量
        $argsTotal = count($args);
        //提取表达式中的变量
        preg_match_all('%(:[a-zA-Z0-9]+)%i', $columnExpression, $matches);
        if (empty($matches) || empty($matches[0])) {
            throw new F_Db_Exception('columnExpression failed : '.$columnExpression);
        }
        //依次处理,防止SQL注入
        for ($i = $paramIndex; $i < $argsTotal; $i++) {
            $this->_queryConditions['where']['bindParams'][$matches[1][$i-1]] = $args[$i].'';
        }
        
        $this->_queryConditions['where']['expression'] = $columnExpression;
        return $this;
    }
    
    /**
     * 查询【单行】记录
     * 
     * @param null|string $connectServer null=自动选择 master=主库 slave=从库
     * @return F_Db_Table_Row
     */
    public function fetchRow($connectServer = null)
    {
        return $this->_find('fetchRow', $connectServer);
    }
    
    /**
     * 查询【多行】记录
     * 
     * @param null|string $connectServer null=自动选择 master=主库 slave=从库
     * @return F_Db_Table_RowSet
     */
    public function fetchAll($connectServer = null)
    {
        return $this->_find('fetchAll', $connectServer);
    }
    
    /**
     * 查询(单行或多行)记录
     * 
     * 私有方法
     * 服务于 fetchRow、fetchAll 方法
     * 
     * @param string $fetchMethod 查询使用手段 fetchRow 或 fetchAll
     * @param null|string $connectServer null=自动选择 master=主库 slave=从库
     * @return mixed
     */
    private function _find($fetchMethod, $connectServer)
    {
        $pdo = F_Db::getInstance()->changeConnectServer($connectServer);
        
        $rowClassName = $this->_tableConfigs['rowClassName'];
        $dbName       = $this->_tableConfigs['dbFullName'];
        $tableName    = $this->_tableConfigs['tableName'];

        $sql = "SELECT {$this->_queryConditions['columns']} FROM {$dbName}.{$tableName}";
        
        if (!empty($this->_queryConditions['where']['expression'])) {
            $sql .= " WHERE {$this->_queryConditions['where']['expression']} ";
        }

        $pdo->prepare($sql);

        if (!empty($this->_queryConditions['where']['bindParams'])) {
            foreach ($this->_queryConditions['where']['bindParams'] as $wk => $wv) {
                $pdo->bindParam($wk, $wv, PDO::PARAM_STR);
            }
        }
        
        if ('fetchRow' === $fetchMethod) {
            $row = $pdo->fetchRow();
            if ($row) {
                return new $rowClassName($row);
            }
            return $row;
        } else {
            $rows = $pdo->fetchAll();
            if (!empty($rows)) {
                $rowList = array();
                foreach ($rows as $row) {
                    array_push($rowList, new $rowClassName($row));
                }
                return new F_Db_Table_RowSet($rowList);
            }
            return $rows;
        }
    }
    
    /**
     * 初始化数据表配置
     * 
     * @param array $tableConfigs 数据表配置
     * @return \F_Db_Table_Select
     */
    public function ___initTableConfigs($tableConfigs)
    {
        $this->_tableConfigs = $tableConfigs;
        
        return $this;
    }
    
    /**
     * 每次使用 select 前清理查询条件
     * 
     * @return \F_Db_Table_Select
     */
    public function ___cleanQueryCondition()
    {
        $this->_queryConditions = $this->_queryConditionsInit;
        return $this;
    }
}