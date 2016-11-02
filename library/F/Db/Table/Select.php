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
        'where'   => array(),
        'order'   => '',
        'group'   => '',
        'having'  => array(),
        'limit'   => '',
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
        preg_match_all('%(:[a-zA-Z0-9_]+)%i', $columnExpression, $matches);
        if (empty($matches) || empty($matches[0])) {
            throw new F_Db_Exception('columnExpression failed : '.$columnExpression);
        }
        
        $whereIndex = count($this->_queryConditions['where']);
        $this->_queryConditions['where'][$whereIndex] = array(
            'expression' => '',
            'bindParams' => array(),
        );
        
        //依次处理,防止SQL注入
        if ($argsTotal > 0) {
            for ($i = $paramIndex; $i < $argsTotal; $i++) {
                if (is_array($args[$i])) {
                    $replaceColumnExpression = '';
                    foreach ($args[$i] as $j=>$a) {
                        $k = $matches[1][$i-1];
                        if ($j > 0) {
                            $k = $k.''.$j;
                        }
                        $replaceColumnExpression .= $k . ',';
                        $this->_queryConditions['where'][$whereIndex]['bindParams'][$k] = $a.'';
                    }
                    $replaceColumnExpression = rtrim($replaceColumnExpression, ',');
                    $columnExpression = preg_replace('%'.$matches[1][$i-1].'%i', $replaceColumnExpression, $columnExpression);
                } else {
                    $this->_queryConditions['where'][$whereIndex]['bindParams'][$matches[1][$i-1]] = $args[$i].'';
                }
            }
        }
        $this->_queryConditions['where'][$whereIndex]['expression'] = $columnExpression;
        return $this;
    }
    
    /**
     * 查询条件
     * 
     * @param string $columnExpression
     * return \F_Db_Table_Select
     */
    public function having($columnExpression)
    {
        //获取函数的所有参数列表
        $args = func_get_args();
        //表达式所需要填充的变量的启始索引
        $paramIndex = 1;
        //参数总量
        $argsTotal = count($args);
        //提取表达式中的变量
        preg_match_all('%(:[a-zA-Z0-9_]+)%i', $columnExpression, $matches);
        if (empty($matches) || empty($matches[0])) {
            throw new F_Db_Exception('columnExpression failed : '.$columnExpression);
        }
        
        $whereIndex = count($this->_queryConditions['having']);
        $this->_queryConditions['having'][$whereIndex] = array(
            'expression' => '',
            'bindParams' => array(),
        );
        
        //依次处理,防止SQL注入
        if ($argsTotal > 0) {
            for ($i = $paramIndex; $i < $argsTotal; $i++) {
                $this->_queryConditions['having'][$whereIndex]['bindParams'][$matches[1][$i-1]] = $args[$i].'';
            }
        }
        
        $this->_queryConditions['having'][$whereIndex]['expression'] = $columnExpression;
        return $this;
    }
    
    /**
     * order by 表达式
     * 
     * 多个排序字段使用英文逗号隔开
     * 
     * @param string $expression
     * return \F_Db_Table_Select
     */
    public function order($expression)
    {
        $this->_queryConditions['order'] = $expression;
        return $this;
    }
    
    /**
     * group by 表达式
     * 
     * @param string $expression
     * return \F_Db_Table_Select
     */
    public function group($expression)
    {
        $this->_queryConditions['group'] = $expression;
        return $this;
    }
    
    /**
     * limit
     * 
     * @param int $offset
     * @param int $count
     * return \F_Db_Table_Select
     */
    public function limit($offset, $count)
    {
        $this->_queryConditions['limit'] = $offset.','.$count;
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
     * 查询【多行】记录 - 分页操作
     * 
     * @param int $page  第几页
     * @param int $count 每页数量
     * @param null|string $connectServer null=自动选择 master=主库 slave=从库
     * @return F_Pagination
     */
    public function fetchAllOfPage($page, $count, $connectServer = null)
    {
        $offset     = ($page - 1) * $count;
        $result     = $this->_find('fetchAll', $connectServer, true);
        $itemTotal  = $result->itemTotal;
        $this->limit($offset, $count);
        $resultList =  $this->_find('fetchAll', $connectServer, false);
        $pageDatas  = array();
        if ($resultList->count() > 0) {
            $pageDatas = $resultList->toArray();
        }
        return new F_Pagination($itemTotal, $page, $count, $pageDatas);
    }
    
    /**
     * 查询(单行或多行)记录
     * 
     * 私有方法
     * 服务于 fetchRow、fetchAll 方法
     * 
     * @param string $fetchMethod 查询使用手段 fetchRow 或 fetchAll
     * @param null|string $connectServer null=自动选择 master=主库 slave=从库
     * @param int $page  第几页
     * @param int $count 每页数量
     * @return mixed
     */
    private function _find($fetchMethod, $connectServer, $isPage = false)
    {
        $pdo = F_Db::getInstance()->changeConnectServer($connectServer);
        
        $rowClassName = $this->_tableConfigs['rowClassName'];
        $dbName       = $this->_tableConfigs['dbFullName'];
        $tableName    = $this->_tableConfigs['tableName'];
        
        if ($isPage) {
            $fetchMethod = 'fetchRow';
            $sql = "SELECT count(*) as itemTotal FROM {$dbName}.{$tableName}";
        } else {
            $sql = "SELECT {$this->_queryConditions['columns']} FROM {$dbName}.{$tableName}";
        }

        if (!empty($this->_queryConditions['where'])) {
            $sql .= " WHERE ";
            foreach ($this->_queryConditions['where'] as $_where) {
                $sql .= " {$_where['expression']} ";
            }
        }
        
        if (!empty($this->_queryConditions['order'])) {
            $sql .= " ORDER BY " . $this->_queryConditions['order'];
        }
        
        if (!empty($this->_queryConditions['group'])) {
            $sql .= " GROUP BY " . $this->_queryConditions['group'];
        }
        
        if (!empty($this->_queryConditions['having'])) {
            $sql .= " HAVING ";
            foreach ($this->_queryConditions['having'] as $_where) {
                $sql .= " {$_where['expression']} ";
            }
        }
        
        if (!empty($this->_queryConditions['limit'])) {
            $sql .= " limit " . $this->_queryConditions['limit'];
        }

        $pdo->prepare($sql);

        if (!empty($this->_queryConditions['where'])) {
            foreach ($this->_queryConditions['where'] as $_where) {
                if(!empty($_where['bindParams'])) {
                    foreach ($_where['bindParams'] as $wk => $wv) {
                        $pdo->bindParam($wk, $wv, PDO::PARAM_STR);
                    }
                }
            }
        }
        
        if (!empty($this->_queryConditions['having'])) {
            foreach ($this->_queryConditions['having'] as $_where) {
                if(!empty($_where['bindParams'])) {
                    foreach ($_where['bindParams'] as $wk => $wv) {
                        $pdo->bindParam($wk, $wv, PDO::PARAM_STR);
                    }
                }
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
            } else {
                return new F_Db_Table_RowSet(array());
            }
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