<?php
/**
 * 调试 各种程序使用
 */
class TestController extends AbstractController
{
    public function indexAction()
    {
        Dao_CodeManager_UserPrivilege::getDelete()->delete('privilege_id in(:privilege_id)', array(1,2,3));
        $var = '<b class="aa" style="fdfd"><script>alert("ok")</script>&#63;Bill Gates bb?？:&~!@#$%^&*()_+}|{":?><<b>';
        $var = Utils_Validation::filter($var)->xss(function($configInstance){
            //$configInstance->set('Attr.ForbiddenClasses','');
            
        })->receive();
        //var_dump(chr(29), true);
        
        var_dump($var, true);
        //var_dump(filter_var($var, FILTER_SANITIZE_STRING));
        //var_dump(filter_var($var, FILTER_UNSAFE_RAW, FILTER_FLAG_STRIP_LOW));
        exit;
    }

}
