<?php 
/**
 * 获取登陆用户的菜单权限，并构建显示
 */
class C_View_Helper_GetMenuListByUser extends Zend_View_Helper_Abstract 
{
    /**
     * 获取后台用户菜单列表 - 根据用户
     * 
     * @param array $user 后台用户信息数组
     * @return array
     */
    public function getMenuListByUser($user)
    {
        $list = Bll_PrivilegeModule_Query::getInstance()->getListOfByUserid($user['userid'], $user['is_super_administrator']);
        if (empty($list) || !is_array($list)) {
            return '';
        }
        $menu = $this->_build($list);
        return $menu;
    }
    
    /**
     * 组件菜单html
     * 
     * @param array $list
     * @return string
     */
    private function _build($list)
    {
        $menuStr = '';
        foreach ($list as $v) {
            if (empty($v['data']['url'])) {
                $menuStr .= '<h3>'.$v['data']['name'].'</h3>';
                if ($v['childCount'] > 0) {
                    $menuStr .= '<ul class="toggle">';
                    $menuStr .= $this->_build($v['childData']);
                    $menuStr .= '</ul>';
                }
            } else {
                $menuStr .= '<li><a href="'.$v['data']['url'].'">'.$v['data']['name'].'</a></li>';
            }
        }
        return $menuStr;
    }
}