<?php
/**
 * 外部接口
 * 
 * 访问权限 - 所有模块均可访问
 * 
 * 助手模块
 * 
 * 辅助程序业务的另类模块 - 处理接口
 * 
 * 配置模块
 * 
 * - 存放没有具体规划的;临时性的;KV键值配置的 数据
 * 
 * @package Bll
 * @subpackage Bll_HelperModule
 * @author allen <allen@yuorngcorp.com>
 */
final class Bll_HelperModule_Config
{
    private function __construct()
    {
        //empty
    }

    /**
     * 单例模式
     * 
     * 获取类的对象实例
     * 
     * @staticvar Bll_HelperModule_Config $instance
     * @return \Bll_HelperModule_Config
     */
    public static function getInstance()
    {
        static $instance = null;
        
        if (is_null($instance)) {
            $instance = new self();
        }
        
        return $instance;
    }
    
    /**
     * 获取指定 key 对应的 val
     * 
     * @param string $key
     * @return ResultSet
     */
    public function get($key)
    {
        $result = Dao_CodeManager_Config::get($key, 'name');
        if ($result && $result->isValid()) {
            return F_Result::build()->success($result->getVal());
        }
        return F_Result::build()->error();
    }
    
    /**
     * 设置指定的key 中存放的 val
     * 
     * @param string $key
     * @param mixed $val
     * @return ResultSet
     */
    public function set($key, $val)
    {
        return $this->add($key, $val, $desc='', true);
    }
    
    /**
     * 添加key
     * 
     * 也是第一次初始化
     * 
     * @param string $key
     * @param mixed $val
     * @param string $desc
     * @param boolean $isSet true=set方法调用
     */
    public function add($key, $val, $desc, $isSet = false)
    {
        $parseMode = 1;
        
        if (is_array($val)) {
            json_decode($val);
            $error = json_last_error();
            if ($error === JSON_ERROR_NONE) {//json 格式字符串
                $parseMode = 2;
            }
        } else {//数组,自动json_encode
            $val = json_encode($val);
            $parseMode = 2;
        }
        
        $cfgResultSet = $this->get($key);
        
        if ($isSet) {//set
            if ($cfgResultSet->isError()) {
                return F_Result::build()->error('key ['.$key.'] 不存在,请先到后台初始化!');
            }
        }
        
        if ($cfgResultSet->isSuccess()) {
            
            if (!$cfgResultSet->isValid()) {
                return F_Result::build()->error('key ['.$key.'] 已被删除!');
            }
            
            try {
                Dao_CodeManager_Config::getManager()->update(array(
                    'name'        => $key,
                    'val'         => $val,
                    'update_time' => time(),
                ), 'key=:key', $key);
                return F_Result::build()->success();
            } catch(Exception $e) {
                return F_Result::build()->error($e->getMessage(), $e->getCode());
            }         
        } else {
            try {
                Dao_CodeManager_Config::getManager()->insert(array(
                    'name'        => $key,
                    'val'         => $val,
                    'desc'        => $desc,
                    'parse_mode'  => $parseMode,
                    'create_time' => time(),
                    'update_time' => time(),
                ));
                return F_Result::build()->success();
            } catch(Exception $e) {
                return F_Result::build()->error($e->getMessage(), $e->getCode());
            }        
        }
    }
    
    /**
     * 删除指定的key
     * 
     * @param string $key
     * @return ResultSet
     */
    public function del($key)
    {
        $cfgResultSet = $this->get($key);
        if ($cfgResultSet->isSuccess()) {
            if (!$cfgResultSet->isValid()) {
                return F_Result::build()->success();
            }
            try {
                Dao_CodeManager_Config::getManager()->update(array(
                    'status'      => 1,
                    'update_time' => time(),
                ), 'name=:name', $key);
                return F_Result::build()->success();
            } catch(Exception $e) {
                return F_Result::build()->error($e->getMessage(), $e->getCode());
            }            
        } else {
            return F_Result::build()->success();
        }
    }
    
    /**
     * 恢复指定的key
     * 
     * @param string $key
     * @return ResultSet
     */
    public function revert($key)
    {
        $cfgResultSet = $this->get($key);
        if ($cfgResultSet->isSuccess()) {
            if ($cfgResultSet->isValid()) {
                return F_Result::build()->success();
            }
            try {
                Dao_CodeManager_Config::getManager()->update(array(
                    'status'      => 0,
                    'update_time' => time(),
                ), 'name=:name', $key);
                return F_Result::build()->success();
            } catch(Exception $e) {
                return F_Result::build()->error($e->getMessage(), $e->getCode());
            }            
        } else {
            return F_Result::build()->error('key ['.$key.'] 不存在');
        }
    }
    
}