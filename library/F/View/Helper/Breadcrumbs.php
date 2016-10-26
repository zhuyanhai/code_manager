<?php 
/**
 * 获取后台用户菜单列表
 */
final class F_View_Helper_Breadcrumbs
{
    private $_outStr = '';
    
    /**
     * 设置后台用户菜单 - 面包屑
     * 
     * 参数任意，按顺序排序
     * 
     * @return string
     */
    public function breadcrumbs()
    {
        //<article class="breadcrumbs"><a href="index.html">Website Admin</a> <div class="breadcrumb_divider"></div> <a class="current">Dashboard</a></article>
        $argList = func_get_args();
        if (empty($argList)) {
            echo $this->_outStr;
        } else {
            $this->_outStr = '<article class="breadcrumbs">';
            $total = count($argList) - 1;
            foreach ($argList as $k=>$arg) {
                if ($k > 0) {
                    $this->_outStr .= '<div class="breadcrumb_divider"></div>';
                }
                $class = '';
                if($k === $total) {
                    $class = 'class="current"';
                }
                if (isset($arg['url']) && !empty($arg['url'])) {
                    $this->_outStr .= '<a href="'.$arg['url'].'" '.$class.'>'.$arg['title'].'</a>';
                } else {
                    $this->_outStr .= '<a '.$class.'>'.$arg['title'].'</a>';
                }
            }
            $this->_outStr .= '</article>';
        }
    }
    
}