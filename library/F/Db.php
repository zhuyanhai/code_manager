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
     * 数据表行记录缓存配置
     * 
     * @var array
     */
    private $_cacheConfigs = array();
            
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
     * 初始化数据表配置
     * 
     * @param array $tableConfigs 数据表配置
     * @return \F_Db
     */
    public function ___initTableConfigs($tableConfigs)
    {
        $this->_initDbConfig($tableConfigs['dbShortName']);

        $this->_tableConfigs = $tableConfigs;
        $this->_tableConfigs['dbFullName'] = $this->_dbConnectCfg[$tableConfigs['dbShortName']]['dbName'];
        $this->_tableConfigs['dbTable']    = '`'.$this->_tableConfigs['dbFullName'].'`.`'.$this->_tableConfigs['tableName'].'`';
        if (isset($tableConfigs['memcache']) && is_array($tableConfigs['memcache'])) {
            $this->_cacheConfigs = $tableConfigs['memcache'];
        }
        return $this;
    }

    /**
     * insert 插入单行记录
     * 
     * @param array $rowData 插入行的内容
     * @return string 返回插入成功后的主键值
     */
    public function insert($rowData)
    {
        $lastId = $this->changeConnectServer('master')->insert($rowData, $this->_tableConfigs['dbTable']);
        $this->_postInsert($lastId);
        return $lastId;
    }

    /**
     * insert 插入多行记录
     * 
     * @param array $dataList 插入行的内容数组
     * @param boolean $ignore 主键或唯一键冲突是否忽略
     * @return string 返回成功与否
     */
    public function multiInsert($dataList, $ignore = false, $onDuplicateKeyUpdate = null)
    {
        $result = $this->changeConnectServer('master')->insertOfMulti($dataList, $this->_tableConfigs['dbTable'], $ignore);
        return $result;
    }
    
    /**
     * 更新行记录
     * 
     * @param array $rowData 更新行的内容
     * @param string $whereCondition 更新条件
     * @return $rowCount 影响行数
     */
    public function update($rowData, $whereCondition)
    {
        //获取函数的所有参数列表
        $args = func_get_args();
        //表达式所需要填充的变量的启始索引
        $paramIndex = 2;
        //参数总量
        $argsTotal = count($args);
        //提取表达式中的变量
        preg_match_all('%(:[a-zA-Z0-9_]+)%i', $whereCondition, $matches);
        if (empty($matches) || empty($matches[0])) {
            throw new F_Db_Exception('columnExpression failed : '.$whereCondition);
        }
        
        $valOfPrimaryKey = '';
        if (isset($this->_tableConfigs['primaryKey'])) {
            $primaryKey = ':'.$this->_tableConfigs['primaryKey'];
        } else {
            $primaryKey = '';
        }
        
        $whereBind = array();
        //依次处理,防止SQL注入
        if ($argsTotal > 0) {
            for ($i = $paramIndex; $i < $argsTotal; $i++) {
                if (is_array($args[$i])) {
                    $replaceWhereCondition = '';
                    foreach ($args[$i] as $j=>$a) {
                        $k = $matches[1][$i-$paramIndex];
                        if ($j > 0) {
                            $k = $k.''.$j;
                        }
                        $replaceWhereCondition .= $k . ',';
                        $whereBind[$k] = $a.'';
                    }
                    $replaceWhereCondition = rtrim($replaceWhereCondition, ',');
                    $whereCondition = preg_replace('%'.$matches[1][$i-$paramIndex].'%i', $replaceWhereCondition, $whereCondition);
                } else {
                    $whereBind[$matches[1][$i-$paramIndex]] = $args[$i].'';
                    if ($matches[1][$i-$paramIndex] === $primaryKey) {
                        $valOfPrimaryKey = $args[$i];
                    }
                }
            }
        }
        
        $rowCount = $this->changeConnectServer('master')->update($rowData, $whereCondition, $whereBind, $this->_tableConfigs['dbTable']);
        if (!empty($valOfPrimaryKey)) {
            $this->_postUpdate($valOfPrimaryKey);
        }
        return $rowCount;
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
        
        $valOfPrimaryKey = '';
        if (isset($this->_tableConfigs['primaryKey'])) {
            $primaryKey = ':'.$this->_tableConfigs['primaryKey'];
        } else {
            $primaryKey = '';
        }
        
        
        $whereBind = array();
        //依次处理,防止SQL注入
        if ($argsTotal > 0) {
            for ($i = $paramIndex; $i < $argsTotal; $i++) {
                if (is_array($args[$i])) {
                    $replaceWhereCondition = '';
                    foreach ($args[$i] as $j=>$a) {
                        $k = $matches[1][$i-$paramIndex];
                        if ($j > 0) {
                            $k = $k.''.$j;
                        }
                        $replaceWhereCondition .= $k . ',';
                        $whereBind[$k] = $a.'';
                    }
                    $replaceWhereCondition = rtrim($replaceWhereCondition, ',');
                    $whereCondition = preg_replace('%'.$matches[1][$i-$paramIndex].'%i', $replaceWhereCondition, $whereCondition);
                } else {
                    $whereBind[$matches[1][$i-$paramIndex]] = $args[$i].'';
                    if ($matches[1][$i-$paramIndex] === $primaryKey) {
                        $valOfPrimaryKey = $args[$i];
                    }
                }
            }
        }
        
        $rowCount = $this->changeConnectServer('master')->delete($whereCondition, $whereBind, $this->_tableConfigs['dbTable']);
        if (!empty($valOfPrimaryKey)) {
            $this->_postDelete($valOfPrimaryKey);
        }
        return $rowCount;
    }
    
    /**
     * 获取 get 操作对象
     * 
     * 通过 主键或唯一键 来获取数据
     * 并且如果配置的memcache缓存,将自动使用
     * 
     * @param string $val 字段值
     * @param string $field 字段名字
     * @param boolean $forceNoCache true=强制不使用缓存
     * @return \F_Db_Table_Row
     */
    public function get($val, $field, $forceNoCache = false)
    {
        $error = '';
        $usememcache = false;
        if ($forceNoCache === false &&!is_null($this->_cacheConfigs) && $this->_checkCacheIsUse() && isset($this->_cacheConfigs[$field]) && $this->_cacheConfigs[$field]['field'] == $field) {//使用缓存
            try {
                $usememcache = true;
                $memcache = F_Cache::createMemcache($this->_cacheConfigs[$field]['server']);
                $memkey   = $this->_getCacheKey($field, $val);
                $result   = $memcache->load($memkey);
            } catch (Exception $e) {
                $result = '';
                $error  = $e->getMessage();
            }
            if (!empty($result)) {
                return new $this->_tableConfigs['rowClassName']($result);
            }
        }
        
        $pdo = $this->changeConnectServer('master');
        
        $rowClassName = $this->_tableConfigs['rowClassName'];
        $dbName       = $this->_tableConfigs['dbFullName'];
        $tableName    = $this->_tableConfigs['tableName'];
        
        $sql = "SELECT * FROM {$dbName}.{$tableName} WHERE ".$field.'=:'.$field;

        $pdo->prepare($sql);

        $pdo->bindParam(':'.$field, $val, PDO::PARAM_STR);
        
        $row = $pdo->fetchRow();
        
        if ($row) {
            $row = new $rowClassName($row);
        }
        
        if(!empty($row) && $usememcache && $error != 'Connection timed out'){
            $this->_setToCache($field, $row);
        }

        return $row;
    }

    /**
     * 获取构造 select 语句的对象
     * 
     * @return \F_Db_Table_Select
     */
    public function getSelect()
    {
        return F_Db_Table_Select::getInstance()->___initTableConfigs($this->_tableConfigs)->___cleanQueryCondition();
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
     * 检测是否使用 kv table 缓存 - get postInsert postUpdate postDelete 时使用
     * 
     * @return boolean true 使用缓存 false 不使用缓存
     */
    private function _checkCacheIsUse()
    {
        if(!empty($this->_cacheConfigs) && is_array($this->_cacheConfigs)){
            foreach($this->_cacheConfigs as $v){
                if(!isset($v['key'])){
                    throw new F_Db_Exception($this->_tableConfigs['dbFullName'].'.'.$this->_tableConfigs['tableName'].' - kv table key not set');
                }
                if(!isset($v['field'])){
                    throw new F_Db_Exception($this->_tableConfigs['dbFullName'].'.'.$this->_tableConfigs['tableName'].' - kv table field not set');
                }
                if(!isset($v['server'])){
                    throw new F_Db_Exception($this->_tableConfigs['dbFullName'].'.'.$this->_tableConfigs['tableName'].' - kv table server not set');
                }
            }
            
            return true;
        }
        return false;
    }
    
    /**
     * 构造k-v memcache key
     *
     * @param string $field
     * @return string
     */
    private function _getCacheKey($field, $val)
    {
        $memkey = strtr($val, '.', '_');
        $memkey = strtr($memkey, '-', '_');
        if(isset($this->_cacheConfigs[$field]['keyEncrypt'])){
            switch($this->_cacheConfigs[$field]['keyEncrypt']){
                case 'md5':
                    $memkey = md5($memkey);
                    break;
            }
        }
        return sprintf($this->_cacheConfigs[$field]['key'], $memkey);
    }
    
    /**
     * 设置数据行数据到 memcache
     * 
     * @param string $field
     * @param array $result 数据行
     */
    private function _setToCache($field, $result)
    {
        $fieldCfg = $this->_cacheConfigs[$field];
        if(isset($fieldCfg['expires'])){
            if(empty($fieldCfg['expires'])){
                $fieldCfg['expires'] = null;
            }
        } else {
            $fieldCfg['expires'] = null;
        }

        $memcache = F_Cache::createMemcache($fieldCfg['server']);
        $memkey   = $this->_getCacheKey($field, $result->$field);
        $data     = $result->toArray();
        
        if(isset($fieldCfg['savefields']) && !empty($fieldCfg['savefields'])){//只保存指定字段
            $data = array();
            foreach($fieldCfg['savefields'] as $v){
                $data[$v] = $result->$v;
            }
        }
        $memcache->save($data, $memkey, $fieldCfg['expires']);
    }
    
    /**
     * 删除 kv table 缓存
     * 
     * @param string $field
     * @param array $result 数据行
     */
    private function _delCache($field, $result)
    {
        $fieldCfg = $this->_cacheConfigs[$field];
        $memcache = F_Cache::createMemcache($fieldCfg['server']);
        $memkey   = $this->_getCacheKey($field, $result->$field);
        $memcache->remove($memkey);
    }
    
    /**
     * 在插入数据完毕后
     * 
     * @param string $valOfPrimaryKey 主键字段的值
     * @return void
     */
    private function _postInsert($valOfPrimaryKey)
    {
//        if ($this->_checkCacheIsUse()) {//使用缓存
//            $row = $this->get($valOfPrimaryKey, $this->_tableConfigs['primaryKey'], true);
//            if ($row) {
//                foreach ($this->_cacheConfigs as $field=>$cache) {
//                    self::_setToCache($field, $row->toArray());
//                }
//            } 
//        }
    }
    
    /**
     * 在更新数据完毕后
     * 
     * @param string $valOfPrimaryKey 主键字段的值
     * @return void
     */
    private function _postUpdate($valOfPrimaryKey = null)
    {
        if ($this->_checkCacheIsUse() && !empty($valOfPrimaryKey)) {//使用缓存
            $row = $this->get($valOfPrimaryKey, $this->_tableConfigs['primaryKey'], true);
            if ($row) {
                foreach ($this->_cacheConfigs as $field=>$cache) {
                    self::_setToCache($field, $row);
                }
            } 
        }
    }
    
    /**
     * 在删除数据完毕后
     * 
     * @param string $valOfPrimaryKey 主键字段的值
     * @return void
     */
    private function _postDelete($valOfPrimaryKey = null)
    {
        if ($this->_checkCacheIsUse() && !empty($valOfPrimaryKey)) {//使用缓存
            $row = $this->get($valOfPrimaryKey, $this->_tableConfigs['primaryKey'], true);
            if ($row) {
                foreach ($this->_cacheConfigs as $field=>$cache) {
                    self::_delCache($field, $row);
                }
            } 
        }
    }
}