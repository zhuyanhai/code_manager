<?php
/**
 * Bootstrap 抽象基类
 *
 * @author zhuyanhai
 */
abstract class F_Application_BootstrapAbstract
{
    /**
     * 构造函数
     */
    public function __construct()
    {
        // 自动调用 Bootstrap(引导脚本) 中的带有单[_下划线]开头的方法,按照定义的顺序依次执行
        $methods = get_class_methods($this);
        foreach ($methods as $method) {
            if (preg_match('%^_[^_]{1}%', $method)) {
                $this->$method();
            }
        }
    }
}
