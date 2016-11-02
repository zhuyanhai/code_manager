<?php
/**
 * DB delete 类
 *
 * - 专门负责 delete 的所有构造操作
 */
final class F_Db_Table_Delete
{
    /**
     * 数据表配置
     * 
     * @var array
     */
    private $_tableConfigs = array();
    
    /**
     * 获取类实例
     *
     * @staticvar F_Db_Table_Delete $instance
     * @return \F_Db_Table_Delete
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
     * 删除行记录
     * 
     * @param string $whereCondition 更新条件
     * @return $rowCount 影响行数
     */
    public function delete($whereCondition)
    {
        //获取函数的所有参数列表
        $args = func_get_args();
        //表达式所需要填充的变量的启始索引
        $paramIndex = 1;
        //参数总量
        $argsTotal = count($args);
        //提取表达式中的变量
        preg_match_all('%(:[a-zA-Z0-9_]+)%i', $whereCondition, $matches);
        if (empty($matches) || empty($matches[0])) {
            throw new F_Db_Exception('columnExpression failed : '.$whereCondition);
        }
        
        $whereBind = array();
        //依次处理,防止SQL注入
        if ($argsTotal > 0) {
            for ($i = $paramIndex; $i < $argsTotal; $i++) {
                $whereBind[$matches[1][$i-1]] = $args[$i].'';
            }
        }
        
        $pdo       = F_Db::getInstance()->changeConnectServer('master');
        $dbName    = $this->_tableConfigs['dbFullName'];
        $tableName = $this->_tableConfigs['tableName'];
        $rowCount  = $pdo->delete($whereCondition, $whereBind, $dbName.'.'.$tableName);
        return $rowCount;
    }
    
    /**
     * 初始化数据表配置
     * 
     * @param array $tableConfigs 数据表配置
     * @return \F_Db_Table_Update
     */
    public function ___initTableConfigs($tableConfigs)
    {
        $this->_tableConfigs = $tableConfigs;
        
        return $this;
    }
    
    
}