<?php
/**
 * 过滤工具集合
 *
 * 实现各种过滤的方法
 */
final class Utils_Validation_FilterTools
{   
    /**
     * 需要过滤的内容
     * 
     * @var mixed 
     */
    private $_val = null;
    
    /**
     * 在执行过滤前必须调用的，请勿在外部调用
     * 
     * @param mixed $content
     * @return \Utils_Validation_FilterTools
     */
    public function init($content)
    {
        $this->_val = $content;
        return $this;
    }
    
    /**
     * 接收全部过滤完后内容
     * 
     * @return mixed
     */
    public function receive()
    {
        return $this->_val;
    }
    
    /**
     * 保留html标签，去除 或 编码特殊字符。剔除ASCII 32以下字符
     * 
     * @return \Utils_Validation_FilterTools
     */
    public function removeStr()
    {
        if (gettype($this->_val) === 'string') {
            $this->_val = filter_var($this->_val, FILTER_UNSAFE_RAW, FILTER_FLAG_STRIP_LOW);
        }
        return $this;
    }
    
    /**
     * 如果存在HTML标签，剔除所有标签，保留其中文本，并且转换一些特殊字符成其他形式【浏览器可识别形式 &lt;】
     * 
     * @return \Utils_Validation_FilterTools
     */
    public function removeHtml()
    {
        if (gettype($this->_val) === 'string') {
            $this->_val = filter_var($this->_val, FILTER_SANITIZE_STRING);
        }
        return $this;
    }
    
    /**
     * 去除[\t | 空格]
     * 
     * @return \Utils_Validation_FilterTools
     */
    public function removeEmpty()
    {
        if (gettype($this->_val) === 'string') {
            $trans = array(
                "\t" => '',
                " " => '',
                "　" => '',
            );
            $this->_val = strtr($this->_val, $trans);
        }
        return $this;
    }
    
    /**
     * 移除 &#[a-z0-9A-Z]+; 例如：&#063;
     * 
     * @return \Utils_Validation_FilterTools
     */
    public function removeCode()
    {
        if (gettype($this->_val) === 'string') {
            $this->_val = preg_replace('/(&#[a-zA-Z0-9]+;)*/i', '', $this->_val);
        }
        return $this;
    }
    
    /**
     * 如果存在HTML标签，将作为文本输出，并且转换一些特殊字符成其他形式【浏览器可识别形式 &#60;】
     * 
     * @return \Utils_Validation_FilterTools
     */
    public function convertChar()
    {
        if (gettype($this->_val) === 'string') {
            $trans = array(
                "'" => '&#39;',
                '"' => '&#34;',
                '(' => '&#40;',
                ')' => '&#41;',
                '?' => '&#63;',
            );
            $this->_val = strtr($this->_val, $trans);
        }
        return $this;
    }
    
    /**
     * 转换一些特殊字符成中文形式
     * 
     * @return \Utils_Validation_FilterTools
     */
    public function convertToChinese()
    {
        if (gettype($this->_val) === 'string') {
            $trans = array(
                "'" => "’",
                '"' => '“',
                ',' => '，',
            );
            $this->_val = strtr($this->_val, $trans);
        }
        return $this;
    }
    
    /**
     * 把换行转换成<br>
     * 
     * @return \Utils_Validation_FilterTools
     */
    public function convertSpace()
    {
        if (gettype($this->_val) === 'string') {
            $this->_val = nl2br($this->_val);
            $trans = array(
                '\r\n'=> '<br/>',
            );
            $this->_val = strtr($this->_val, $trans);
        }
        return $this;
    }
    
    /**
     * 如果存在HTML标签，过滤掉有威胁的HTML标签【适用于所见即所得的BLOG等】
     * 
     * @return \Utils_Validation_FilterTools
     */
    public function filterHtml()
    {
        if (gettype($this->_val) === 'string') {
            $searchEncode = array (
                "'(<|%3C)script([^>]|[^%3E])*?(>|%3E).*?(<|%3C)(/|%2F)script(>|%3E)'si",
                "'(<|%3C)html([^>]|[^%3E])*?[>|%3E].*?[<|%3C]body([^>]|[^%3E])*?[>|%3E]'si",
                "'(<|%3C)(/|%2F)body(>|%3E).*?(<|%3C)(/|%2F)html(>|%3E)'si",
                "'(<|%3C)style([^>]|[^%3E])*?(>|%3E).*?(<|%3C)(/|%2F)style(>|%3E)'si",
                "'(<|%3C)link([^>]|[^%3E])*?\s*(/|%2F)?(>|%3E)'si",
                "'(<|%3C)iframe([^>]|[^%3E])*?(>|%3E).*?(<|%3C)(/|%2F)iframe(>|%3E)'si",
                "'(<|%3C)form([^>]|[^%3E])*?(>|%3E).*?(<|%3C)(/|%2F)form(>|%3E)'si",
                "'(<|%3C)textarea([^>]|[^%3E])*?>.*?(<|%3C)(/|%2F)textarea(>|%3E)'si",
                "'(\s*|\+*)id(\s*|\+*)(=|%3D)(\s*|\+*)(\"|\'|%22|%27).*?(\"|\'|%22|%27)'si",
                "'(\s*|\+*)clas(\s*|\+*)s(\s*|\+*)(=|%3D)(\s*|\+*)(\"|\'|%22|%27).*?(\"|\'|%22|%27)'si",
                "'(<|%3C)(!|%21)--.*?--(>|%3E)'si",
            );

            $replace = array ("","","","","","","","","","","");

            $this->_val = preg_replace($searchEncode, $replace, $this->_val);
            //避免双重转义代码，所以需要过滤两次
            $this->_val = preg_replace($searchEncode, $replace, $this->_val);

            $searchEncode = array (
                "'(<|%3C)script([^>]|[^%3E])*?(>|%3E)'si",
                "'(<|%3C)iframe([^>]|[^%3E])*?(>|%3E)'si",
            );

            $this->_val = preg_replace($searchEncode, $replace, $this->_val);
            //避免双重转义代码，所以需要过滤两次
            $this->_val = preg_replace($searchEncode, $replace, $this->_val);

            $trans = array(
                '?' => '&#63;',
            );

            $this->_val = strtr($this->_val, $trans);
        }
        return $this;
    }

    /**
     * xssA
     * 
     * @return \Utils_Validation_FilterTools
     */
    public function xssA()
    {
//        $this->filterHtml();
//        require_once(ROOT_PATH . '/UtanGlobal/library/Third/htmlpurifier/library/HTMLPurifier.auto.php');
//        //标签和属性白名单
//        $simpleConfigStr = '
//        a[href]
//        ';
//        $config = HTMLPurifier_Config::createDefault();
//        $config->set('Core.Encoding', 'UTF-8'); // replace with your encoding
//        $config->set('HTML.Doctype', 'XHTML 1.0 Transitional'); // replace with your doctype
//        $config->set('HTML.Allowed', $simpleConfigStr);
//        //添加连接的target属性
//        $config->set('HTML.DefinitionID', 'enduser-customize.html tutorial');
//        $config->set('HTML.DefinitionRev', 1);
//        $config->set('Cache.DefinitionImpl', null); // remove this later!
//        $def = $config->getHTMLDefinition(true);
//        $def->addAttribute('a', 'target', 'Enum#_blank,_self,_target,_top');
//        $purifier = new HTMLPurifier($config);
//        $this->_val = $purifier->purify($this->_val);
//        return $this;
    }
}