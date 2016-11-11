<?php
/**
 * 内部API (其它模块不可访问)
 * 
 * 访问权限 - 仅 Bll/PrivilegeModule 目录中的任何程序
 * 
 * 构建菜单逻辑
 * 
 * @package Bll
 * @subpackage Bll_PrivilegeModule
 * @author allen <allen@yuorngcorp.com>
 */
class Bll_PrivilegeModule_Internal_BuildMenu extends F_InternalAbstract
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
     * @staticvar Bll_UserModule_Internal_Info $instance
     * @return \Bll_UserModule_Internal_Info
     */
    public static function getInstance()
    {
        static $instance = null;
        if(null == $instance){
            $instance = new self();
        }

        //本步骤必须，检查是否是本模块的调用
        $instance->checkCall();
        return $instance;
    }
    
    /**
     * 构建出所有有效的菜单列表
     * 
     * @param boolean $resetCache true=重置缓存 false=正常流程
     * @return array
     */
    public function getAll($resetCache = false)
    {
        $memKey = 'code_manager_menu_of_all';
        $memObj = F_Cache::createMemcache('user');
        //if ($resetCache) {
            $memObj->remove($memKey);
        //}
        //获取所有构造好的菜单
        $menuList = $memObj->load($memKey);
        if (!empty($menuList)) {
            return $menuList;
        } else {//需要动态构造，最多 3 级菜单
            $menuList = array();
            $parentIdsOfMenu = null;
            do {
                $parentIdsOfMenu = $this->_buildMenuListOfAll($parentIdsOfMenu, $menuList);
            } while(!empty($parentIdsOfMenu));
            $memObj->save($menuList, $memKey);
            return $menuList;
        }
    }
    
    /**
     * 构建出指定用户的有效的菜单列表
     * 
     * @param int $userid 用户ID
     * @param boolean $resetCache true=重置缓存 false=正常流程
     * @return array
     */
    public function getByUserid($userid, $resetCache = false)
    {
        $conditionResultSet = Bll_HelperModule_Config::getInstance()->get('privilege_of_user_memcache_update');
        if ($conditionResultSet->isError()) {
            $conditionResultSet->throwException();
        }
        $memKey = 'code_manager_menu_of_user_'.$conditionResultSet->getResult().'_'.$userid;
        $memObj = F_Cache::createMemcache('user');
        //if ($resetCache) {
            $memObj->remove($memKey);
        //}
        //获取所有构造好的菜单
        $menuList = $memObj->load($memKey);
        if (!empty($menuList)) {
            return $menuList;
        } else {//需要动态构造，最多 3 级菜单

            //获取菜单列表
            $userPrivilegeIdsResultSet = Bll_PrivilegeModule_User::getInstance()->getIds($userid);
            if ($userPrivilegeIdsResultSet->isError() || $userPrivilegeIdsResultSet->isEmpty()) {
                return '';
            }
            $privilegeIds = $userPrivilegeIdsResultSet->getResult();

            $list = Dao_CodeManager_Privilege::getSelect()->where('id in(:id) AND status=:status order by id asc', $privilegeIds, 0)->fetchAll()->toArray();
            if (count($list) <= 0) {
                return '';
            }

            $menuList = array();
            $this->_buildMenuListOfUser(0, $menuList, $list);
            $menuList = Utils_Sort::getpao($menuList, 'level', 'desc');
            $memObj->save($menuList, $memKey, array(), 86400);
            return $menuList;
        }
    }
    
//--- 以下是私有方法
    
    /**
     * 构建菜单列表 - 根据指定的 $parentIdsOfMenu
     * 
     * @param array $parentIdsOfMenu 父元素菜单ID数组
     * @param array $menuList 需要构建的菜单列表
     * @return array
     */
    private function _buildMenuListOfAll($parentIdsOfMenu = array(), &$menuList)
    {
        //获取菜单列表
        $selector = Dao_CodeManager_Privilege::getSelect();
        if (is_array($parentIdsOfMenu) && !empty($parentIdsOfMenu)) {
            $selector->where('parent_id in(:parent_id) and status=:status order by id asc', $parentIdsOfMenu, 0);
        } else {
            $selector->where('parent_id=:parent_id and status=:status order by id asc', 0, 0);
        }
        $list = $selector->fetchAll();
        if ($list->count() <= 0) {
            return null;
        }
        //构造下级菜单的父元素菜单ID数组
        $parentIdsOfMenu = Utils_Array::toFlat($list, 'id');
        $tmpMenuList = array();
        foreach ($list as $v) {
            array_push($tmpMenuList, array(
                'level'      => $v->level,
                'type'       => $v->type,
                'data'       => $v->toArray(),
                'childCount' => 0,
                'childData'  => array(),
            ));
        }

        $tmpMenuList = Utils_Sort::getpao($tmpMenuList, 'level', 'desc');
        if (!empty($menuList)) {
            foreach ($tmpMenuList as $tv) {
                $this->_buildLevelMenuList($tv, $menuList);
            }
        } else {
            $menuList = $tmpMenuList;
        }
        
        return $parentIdsOfMenu;
    }
    
    /**
     * 构建出菜单的层级列表
     * 
     * @param array $tv
     * @param array $menuList
     */
    private function _buildLevelMenuList($tv, &$menuList)
    {
        foreach ($menuList as $k=>&$m) {
            if (!empty($m['childData'])) {
                $this->_buildLevelMenuList($tv, $m['childData']);
            }
            if ($tv['data']['parent_id'] === $m['data']['id']) {
                $menuList[$k]['childCount'] += 1;
                array_push($m['childData'], $tv);
            }
        }
    }
    
    /**
     * 构建用户的菜单列表
     * 
     * @param array $parentIdsOfMenu 父元素菜单ID数组
     * @param array $menuList 需要构建的菜单列表
     * @return array
     */
    private function _buildMenuListOfUser($parentId, &$menuList, &$list)
    {
        foreach ($list as $v) {
            if ($parentId == $v['parent_id']) {
                if (isset($menuList['childData'])) {
                    array_push($menuList['childData'], array(
                        'level'      => $v['level'],
                        'type'       => $v['type'],
                        'data'       => $v,
                        'childCount' => 0,
                        'childData'  => array(),
                    ));
                    $menuList['childCount'] += 1;
                    $this->_buildMenuListOfUser($v['id'], $menuList['childData'][count($menuList['childData']) - 1], $list);
                } else {
                    array_push($menuList, array(
                        'level'      => $v['level'],
                        'type'       => $v['type'],
                        'data'       => $v,
                        'childCount' => 0,
                        'childData'  => array(),
                    ));
                    $this->_buildMenuListOfUser($v['id'], $menuList[count($menuList) - 1], $list);
                }
            }
        }
    }
    
}