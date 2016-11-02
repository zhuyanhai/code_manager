<?php
/**
 * DB multi insert 类
 *
 * - 专门负责 multi insert 的所有构造操作
 */
final class F_Db_Table_MultiInsert
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
     * @staticvar F_Db_Table_MultiInsert $instance
     * @return \F_Db_Table_MultiInsert
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
     * 插入行记录
     * 
     * @param array $dataList 插入行的内容数组
     * @param boolean $ignore 主键或唯一键冲突是否忽略
     * @return string 返回成功与否
     */
    public function insert($dataList, $ignore = false, $onDuplicateKeyUpdate = null)
    {
        $pdo       = F_Db::getInstance()->changeConnectServer('master');
        $dbName    = $this->_tableConfigs['dbFullName'];
        $tableName = $this->_tableConfigs['tableName'];
        $result    = $pdo->insertOfMulti($dataList, $dbName.'.'.$tableName, $ignore);
        return $result;
    }
    
    /**
     * 初始化数据表配置
     * 
     * @param array $tableConfigs 数据表配置
     * @return \F_Db_Table_Insert
     */
    public function ___initTableConfigs($tableConfigs)
    {
        $this->_tableConfigs = $tableConfigs;
        
        return $this;
    }
    
}