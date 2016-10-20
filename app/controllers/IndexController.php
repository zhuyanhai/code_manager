<?php
/**
 * 全站首页
 * 
 */
class IndexController extends AbstractController
{
    /**
     * 首页
     */
    public function indexAction()
    {
        if ($_SERVER['PHP_AUTH_USER'] === 'hefu') {
            //获取目录树
            $trees = C_Md_Organize::getTree('/data/hefu');
        } else {
            //获取目录树
            $trees = C_Md_Organize::getTree();
        }
        

        //构建导航树 - 根据目录树
        $this->view->navTrees = C_Md_Organize::buildNav($trees);
        
        $this->view->authUser = $_SERVER['PHP_AUTH_USER'];
    }
    
}