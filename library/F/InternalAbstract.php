<?php
/**
 * 内部逻辑类的 基类
 * 
 * 所有内部逻辑类必须继承此类，否则报错
 * 
 * @category F
 * @package F_InternalAbstract
 * @author allen <allenifox@163.com>
 * 
 */
abstract class F_InternalAbstract
{
    /**
     * 检查调用
     */
    protected function checkCall()
    {
        $classArray = explode('_', get_called_class());
        $traceArray = debug_backtrace(2,2);
        $traceFile  = preg_replace('%'.APPLICATION_PATH.'/models/%i', '', $traceArray[1]['file']);
        $checkArray = explode('/', $traceFile);
        if ($checkArray[0] !== $classArray[0] || $checkArray[1] !== $classArray[1]) {
            throw new F_Application_Exception('内部逻辑不允许外部['.$traceFile.']直接调用', 4444);
        }
    }
}