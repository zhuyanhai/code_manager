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
     * @return array
     */
    public function getAll()
    {
        $memKey = 'code_manager_menu_of_all';
        $memObj = F_Cache::createMemcache('user');
        //$memObj->remove($memKey);
        //获取所有构造好的菜单
        $menuList = $memObj->load($memKey);
        if (!empty($menuList)) {
            return $menuList;
        } else {//需要动态构造，最多 3 级菜单
            $menuList = array();
            $parentIdsOfMenu = null;
            $i = 0;
            do {
                $parentIdsOfMenu = $this->_buildMenuList($parentIdsOfMenu, $menuList);
            } while(!empty($parentIdsOfMenu));
            $memObj->save($menuList, $memKey);
            return $menuList;
        }
    }
    
    /**
     * 构建菜单列表 - 根据指定的 $parentIdsOfMenu
     * 
     * @param array $parentIdsOfMenu 父元素菜单ID数组
     * @param array $menuList 需要构建的菜单列表
     * @return array
     */
    private function _buildMenuList($parentIdsOfMenu = array(), &$menuList)
    {
        //获取菜单列表
        $selector = Dao_CodeManager_Privilege::getSelect();
        if (is_array($parentIdsOfMenu) && !empty($parentIdsOfMenu)) {
            $selector->where('parent_id in(:parent_id) and status=:status order by id asc', implode(',', $parentIdsOfMenu), 0);
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
                'data'       => $v->toArray(),
                'childCount' => 0,
                'childData'  => array(),
            ));
        }

        $tmpMenuList = Utils_Sort::getpao($tmpMenuList, 'level', 'desc');
        if (!empty($menuList)) {
            foreach ($tmpMenuList as $tv) {
                self::_buildLevelMenuList($tv, $menuList);
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
    private static function _buildLevelMenuList($tv, &$menuList)
    {
        foreach ($menuList as $k=>&$m) {
            if (!empty($m['childData'])) {
                self::_buildLevelMenuList($tv, $m['childData']);
            }
            if ($tv['data']['parent_id'] === $m['data']['id']) {
                $menuList[$k]['childCount'] += 1;
                array_push($m['childData'], $tv);
            }
        }
    }
    
}