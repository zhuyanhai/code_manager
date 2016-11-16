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
     * @return array
     */
    public function getProjectListByUser()
    {
        $list = Bll_ProjectModule_Query::getInstance()->getList();

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