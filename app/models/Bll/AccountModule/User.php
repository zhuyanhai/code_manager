<?php
/**
 * 外部接口
 * 
 * 访问权限 - 所有模块均可访问
 * 
 * 用户模块
 * 
 * 用户管理逻辑 - 处理接口
 * 
 * @package Bll
 * @subpackage Bll_AccountModule
 * @author allen <allen@yuorngcorp.com>
 */
final class Bll_AccountModule_User
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
     * @staticvar Bll_AccountModule_User $instance
     * @return \Bll_AccountModule_User
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
     * 获取后台用户分页列表
     * 
     * @param array $conditions 搜索条件
     * @param int $page 页码
     * @param int $count 每页数量
     * @return ResultSet
     */
    public function getListOfAdmin($conditions, $page, $count = 20)
    {
        $selector = Dao_CodeManager_User::getSelect();

        if(isset($conditions['searchType']) && !empty($conditions['searchType']) && isset($conditions['searchValue']) && !empty($conditions['searchValue'])){
            if ($conditions['searchType'] === 'realname') {
                $selector->where("realname like :realname%", $conditions['searchValue']);
            } else {
                $selector->where($conditions['searchType'].'=:'.$conditions['searchType'], $conditions['searchValue']);
            }
        }
        
        if (intval($conditions['status']) !== 99) {
            $selector->where('status=:status', $conditions['status']);
        }

        $list = $selector->order('userid desc')->fetchAllOfPage($page, $count);
        
        return F_Result::build()->success($list);
    }
    
    /**
     * 获取用户信息 - 根据用户ID
     * 
     * @param int $userid 用户ID
     * @return ResultSet
     */
    public function getByUserid($userid)
    {
        $userRow = Dao_CodeManager_User::get($userid, 'userid');
        if (!empty($userRow)) {
            return F_Result::build()->success($userRow->toArray());
        }
        return F_Result::build()->error('用户不存在');
    }
    
    /**
     * 获取用户信息 - 根据用户登录账号
     * 
     * @param string $account 用户登录账号
     * @return ResultSet
     */
    public function getByAccount($account)
    {
        $accountRow = Dao_CodeManager_User::get($account, 'account');
        $userRow    = Dao_CodeManager_User::get($accountRow->userid, 'userid');
        if (!empty($userRow)) {
            return F_Result::build()->success($userRow->toArray());
        }
        return F_Result::build()->error('用户不存在');
    }
    
    /**
     * 添加账号
     * 
     * @param array $post
     * @return ResultSet
     */
    public function add($post)
    {
        try {
            $post['sAccount']  = Utils_Validation::verify('sAccoutn', $post)->required()->receive();
            $post['sPasswd']   = Utils_Validation::verify('sPasswd', $post)->required()->receive();
            $post['sRealname'] = Utils_Validation::verify('sRealname', $post)->required()->receive();
                    
            $time = time();
            $userInfo = array(
                'account'       => $post['sAccount'], 
                'passwd'        => $post['sPasswd'], 
                'realname'      => $post['sRealname'], 
                'contact_phone' => $post['sContactPhone'], 
                'contact_email' => $post['sContactEmail'],
                'create_time'   => $time,
                'update_time'   => $time,
            );
            $userInfo['passwd'] = $this->buildPassword($post['sPasswd'], $time);
            
            try {
                $userid = Dao_CodeManager_User::getManager()->insert($userInfo);
            } catch(Exception $e) {
                $errorMsg = $e->getMessage();
                if ($e->getCode() == 23000 && preg_match('%^SQLSTATE\[23000\]%i', $errorMsg)) {
                    return F_Result::build()->error('账户请勿重复');
                }
                return F_Result::build()->error('添加失败');
            }

            
            
            return F_Result::build()->success(array('userid' => $userid));
        } catch(Utils_Validation_Exception $e) {
            switch ($e->errorKey) {
                case "sRealname":
                    $msg = '请输入姓名';
                    break;
                case "sPasswd":
                    $msg = '请输入密码';
                    break;
                case "sAccount":
                    $msg = '请输入账号';
                    break;
                default:
                    $msg = '添加失败';
                    break;
            }
            return F_Result::build()->error($msg);
        }
    }
    
    /**
     * 编辑账号
     * 
     * @param array $post
     * @return ResultSet
     */
    public function edit($post)
    {
        try {
            $userid = Utils_Validation::verify('iUserid', $post)->required()->int()->notZero()->receive();
            $post['sAccount']  = Utils_Validation::verify('sAccount', $post)->required()->receive();
            $post['sRealname'] = Utils_Validation::verify('sRealname', $post)->required()->receive();

            $userInfo = array(
                'account'       => $post['sAccount'], 
                'passwd'        => $post['sPasswd'], 
                'realname'      => $post['sRealname'], 
                'contact_phone' => $post['sContactPhone'], 
                'contact_email' => $post['sContactEmail'],
                'update_time'   => time(),
            );
            
            //检测并获取用户信息
            $userResultSet = Bll_AccountModule_User::getInstance()->getByUserid($userid);
            if ($userResultSet->isError()) {
                return $userResultSet;
            }

            //如果需要修改密码
            if (isset($userInfo['passwd']) && !empty($userInfo['passwd'])) {
                $userInfo['passwd'] = $this->buildPassword($userInfo['passwd'], $userResultSet->create_time);
            } else {
                unset($userInfo['passwd']);
            }

            try {
                Dao_CodeManager_User::getManager()->update($userInfo, 'userid=:userid', $userid);
            } catch(Exception $e) {
                echo $e->getMessage();
                return F_Result::build()->error('编辑失败');
            }
            
            return F_Result::build()->success(array('userid' => $userid));
        } catch(Utils_Validation_Exception $e) {
            echo $e->errorKey;
            switch ($e->errorKey) {
                case "sRealname":
                    $msg = '请输入姓名';
                    break;
                case "sAccount":
                    $msg = '请输入账号';
                    break;
                default:
                    $msg = '编辑失败';
                    break;
            }
            return F_Result::build()->error($msg);
        }
    }
    
    /**
     * 锁定用户
     * 
     * @param int $userid
     * @return ResultSet
     */
    public function lock($userid)
    {
        //检测并获取用户信息
        $userResultSet = $this->getByUserid($userid);
        if ($userResultSet->isError()) {
            return $userResultSet;
        }
        
        try {
            Dao_CodeManager_User::getManager()->update(array('status' => 10, 'update_time' => time()), 'userid=:userid', $userid);
            return F_Result::build()->success();
        } catch(Exception $e) {
            return F_Result::build()->error('锁定失败');
        }
    }
    
     /**
     * 解锁用户
     * 
     * @param int $userid
     * @return ResultSet
     */
    public function unlock($userid)
    {
        //检测并获取用户信息
        $userResultSet = $this->getByUserid($userid);
        if ($userResultSet->isError()) {
            return $userResultSet;
        }
        
        try {
            Dao_CodeManager_User::getManager()->update(array('status' => 0, 'update_time' => time()), 'userid=:userid', $userid);
            return F_Result::build()->success();
        } catch(Exception $e) {
            return F_Result::build()->error('解锁失败');
        }
    }
    
}