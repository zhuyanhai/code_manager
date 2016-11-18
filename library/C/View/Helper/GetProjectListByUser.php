<?php 
/**
 * 获取登陆用户的项目列表，并构建显示
 * 
 */
class C_View_Helper_GetProjectListByUser
{
    /**
     * 获取登陆用户的项目列表
     * 
     * @param array $user 用户信息数组
     * @return array
     */
    public function getProjectListByUser($user)
    {
        if (Bll_PrivilegeModule_Query::getInstance()->isSuperAdminByUserid($user['userid'])) {//超级管理员
            $list = Bll_ProjectModule_Query::getInstance()->getListOfAll();
        } else {//指定用户
            $projectIdsResultSet = Bll_ProjectModule_UserPrivilege::getInstance()->getProjectIdsByUserid($user['userid']);
            if ($projectIdsResultSet->isError() || $projectIdsResultSet->isEmpty()) {
                return '';
            }
            $list = Bll_ProjectModule_Query::getInstance()->getListByIds($projectIdsResultSet->projectIds);
        }

        if ($list->isError() || $list->isEmpty()) {
            return '';
        }
        $projectHtml = $this->_build($list->getResult());
        return $projectHtml;
    }
    
    /**
     * 组装html
     * 
     * @param array $list
     * @return string
     */
    private function _build($list)
    {
        $returnStr = '<h3>项目管理</h3><ul class="toggle">';
        foreach ($list as $v) {
            $returnStr .= '<ul class="toggle"><li><a href="/project/?iProjectId='.$v['id'].'" class="PROGRAM-menu_a">'.$v['name'].'</a></li>';
        }
        $returnStr .= '</ul><hr/>';
        return $returnStr;
    }
}