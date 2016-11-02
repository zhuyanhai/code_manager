<?php
/**
 * DB update 类
 *
 * - 专门负责 update 的所有构造操作
 */
final class F_Db_Table_Update
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
     * @staticvar F_Db_Table_Update $instance
     * @return \F_Db_Table_Update
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
     * 更新行记录
     * 
     * @param array $rowData 更新行的内容
     * @param string $whereCondition 更新条件
     * @param array $whereBind 更新条件绑定数据
     * @return $rowCount 影响行数
     */
    public function update($rowData, $whereCondition, $whereBind)
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
                if (is_array($args[$i])) {
                    $replaceWhereCondition = '';
                    foreach ($args[$i] as $j=>$a) {
                        $k = $matches[1][$i-1];
                        if ($j > 0) {
                            $k = $k.''.$j;
                        }
                        $replaceWhereCondition .= $k . ',';
                        $whereBind[$k] = $a.'';
                    }
                    $replaceWhereCondition = rtrim($replaceWhereCondition, ',');
                    $whereCondition = preg_replace('%'.$matches[1][$i-1].'%i', $replaceWhereCondition, $whereCondition);
                } else {
                    $whereBind[$matches[1][$i-1]] = $args[$i].'';
                }
            }
        }
        
        $pdo       = F_Db::getInstance()->changeConnectServer('master');
        $dbName    = $this->_tableConfigs['dbFullName'];
        $tableName = $this->_tableConfigs['tableName'];
        $rowCount  = $pdo->update($rowData, $whereCondition, $whereBind, $dbName.'.'.$tableName);
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