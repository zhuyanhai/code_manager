<?php 
/**
 * 获取后台全部菜单列表
 * 
 * 后台“菜单列表”中使用
 */
class C_View_Helper_GetMenuListOfAll
{
    /**
     * 获取后台全部菜单列表
     * 
     * @return array
     */
    public function getMenuListOfAll()
    {
        $list = Bll_PrivilegeModule_Query::getInstance()->getListOfByUserid(0);

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
            $fstyle = '';
            if ($v['childCount'] > 0) {
                $l = '1';
                $bgcolor = 'background-color:#eee;';
                $paddingL = '';
                if (intval($v['data']['status']) === 1) {
                    $fstyle = 'color:#fff;';
                }
            } else {
                $l = '0';
                $bgcolor = 'background-color:#fff;';
                $paddingL = 'padding-left:40px';
                if (intval($v['data']['status']) === 1) {
                    $fstyle = 'color:#eee;';
                }
            }
            
            $menuStr .= '<tr id="item_'.$v['data']['id'].'" style="'.$bgcolor.$fstyle.'" data-l="'.$l.'">'; 
            $menuStr .= '<td>'.$v['data']['id'].'</td>';
            $menuStr .= '<td>'.(($v['data']['___isMenu'])?'菜单/权限':'仅权限').'</td>';
            $menuStr .= '<td style="'.$paddingL.'">'.$v['data']['___showMenuType'].'</td>';
            $menuStr .= '<td style="'.$paddingL.'">'.$v['data']['name'].'</td>';
            $menuStr .= '<td>'.$v['data']['url'].'</td>';
            if ($v['childCount'] > 0) {
                $menuStr .= '<td>父级</td>';
            } else {
                $menuStr .= '<td style="'.$paddingL.'">子级</td>';
            }
            $menuStr .= '<td><a href="/privilege/edit?iId='.$v['data']['id'].'">编辑</a><b style="color:#000">&nbsp;|&nbsp;</b>';
            if (intval($v['data']['status']) === 1) {
                $displayDel = 'none';
                $displayRevert = 'inline-block';
            } else {
                $displayDel = 'inline-block';
                $displayRevert = 'none';
            }
            $menuStr .= '<a href="###" id="opRevert_'.$v['data']['id'].'" onclick="revert('.$v['data']['id'].');return false;" style="display:'.$displayRevert.';">恢复</a>';
            $menuStr .= '<a href="###" id="opDel_'.$v['data']['id'].'" onclick="del('.$v['data']['id'].');return false;" style="display:'.$displayDel.';">删除</a></td>';
            $menuStr .= '</tr>';
            if ($v['childCount'] > 0) {
                $menuStr .= $this->_build($v['childData']);
            }
        }
        return $menuStr;
    }
}