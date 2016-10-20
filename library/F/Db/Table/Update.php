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
    public static function update($rowData, $whereCondition, $whereBind)
    {
        $pdo = self::_changeConnectServer('master');
        static::_update($rowData, $whereBind);
        $rowCount = $pdo->update($rowData, $whereCondition, $whereBind, F_Db_PdoConnectPool::getDbName(static::$_dbShortName) . '.' . static::$_tableName);
        static::_postUpdate($rowData, $whereBind);
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