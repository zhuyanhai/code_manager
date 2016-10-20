<?php

/**
 * 验证工具集合
 *
 * 实现各种校验的方法
 */
final class Utils_Validation_VerifyTools
{
    /**
     * 校验，抛出异常
     */
    const MODEL_VERIFY = 'verify';
    
    /**
     * 仅检测，不抛出异常，返回 boolean
     */
    const MODEL_TEST = 'test';

    /**
     * 需要校验的变量名字，在抛出异常时使用
     * 
     * @var string 
     */
    private $_key = null;

    /**
     * 需要校验的内容
     * 
     * @var mixed 
     */
    private $_val = null;

    /**
     * 校验模式
     * 
     * verify = 校验，错误抛异常，可链式访问，通过receive获取校验后的内容
     * test   = 仅校验，错误返回false，可链式访问，不返回校验后的内容
     * 
     * @var string
     */
    private $_validateMode = 'verify';

    /**
     * 记录最后校验的错误，主要是给 test 模式使用
     * 
     * @var array
     */
    private $_lastError = array();

    /**
     * 在执行校验前必须调用的，请勿在外部调用
     * 
     * @param mixed $content
     * @return \Utils_Validation_VerifyTools
     */
    public function init($mode, $key, $content)
    {
        $this->_validateMode = $mode;
        $this->_key = $key;
        if (is_array($content)) {
            $this->_val = (isset($content[$key]))?$content[$key]:'';
        } else {
            $this->_val = $content;
        }
        return $this;
    }

    /**
     * 获取最后一次校验时，出错的信息
     *  
     * @return array
     */
    public function getLastErrorOfTest()
    {
        return $this->_lastError;
    }

    /**
     * 接收全部校验完后内容
     * 
     * @return mixed
     */
    public function receive()
    {
        return $this->_val;
    }

    /**
     * 校验 - 值不能为空，必须存在
     *
     * @return boolean|\Utils_Validation_VerifyTools
     */
    public function required()
    {
        $val = trim($this->_val);
        if (false === isset($val[0])) {
            return $this->_throwError($this->_key, 'required');
        }
        return $this;
    }
    
    /**
     * 校验 - 值必须是全英文
     * 
     * @return boolean|\Utils_Validation_VerifyTools
     */
    public function english()
    {
        if(0 == preg_match('/^[A-Za-z]+$/i', $this->_val)) {
            return $this->_throwError($this->_key, 'english');
        }
        return $this;
    }
    
    /**
     * 校验 - 值必须是全中文
     * 
     * @return boolean|\Utils_Validation_VerifyTools
     */
    public function chinese()
    {
        if(0 == preg_match('/^[\x80-\xff]+$/i', $this->_val)) {
            return $this->_throwError($this->_key, 'chinese');
        }
        return $this;
    }
    
    /**
     * 校验 - 值必须是英文+下划线
     * 
     * @return boolean|\Utils_Validation_VerifyTools
     */
    public function englishAndUnderline()
    {
        if(0 == preg_match('/^[A-Za-z_]+$/i', $this->_val)) {
            return $this->_throwError($this->_key, 'englishAndUnderline');
        }
        return $this;
    }
    
    /**
     * 校验 - 值必须是url字符串
     * 
     * @return boolean|\Utils_Validation_VerifyTools
     */
    public function url()
    {
        if (!filter_var($this->_val, FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED)) {
            return $this->_throwError($this->_key, 'url');
        }
        return $this;
    }

    /**
     * 校验 - 值必须是整数
     *
     * @return boolean|\Utils_Validation_VerifyTools
     */
    public function int()
    {
        $val = $this->_val;
        if(!is_numeric($val)){
            return $this->_throwError($this->_key, 'int');
        }
        if (is_numeric($val) && $val != 0) {
            if (!filter_var($val, FILTER_VALIDATE_INT)) {
                return $this->_throwError($this->_key, 'int');
            }
        }
        $this->_val = intval($this->_val);
        return $this;
    }
    
    /**
     * 校验 - 值必须是浮点型
     *
     * @return boolean|\Utils_Validation_VerifyTools
     */
    public function float()
    {
        $val = $this->_val;
        if(is_string($val)){
            $val = floatval($val);
        }
        if(!is_float($val)){
            return $this->_throwError($this->_key, 'float');
        }     
        if (is_float($val) && $val != 0) {
            if (!filter_var($val, FILTER_VALIDATE_FLOAT)) {
                return $this->_throwError($this->_key, 'float');
            }
        }
        $this->_val = floatval($this->_val);
        return $this;
    }
    
    /**
     * 校验 - 值必须是字符型
     *
     * @return boolean|\Utils_Validation_VerifyTools
     */
    public function string()
    {
        $val = $this->_val;
        if(!is_string($val)){
            return $this->_throwError($this->_key, 'string');
        }
        $this->_val = strval($this->_val);
        return $this;
    }
    
    /**
     * 校验 - 值必须是布尔型
     *
     * @return boolean|\Utils_Validation_VerifyTools
     */
    public function boolean()
    {
        $val = $this->_val;
        if(!is_bool($val)){
            return $this->_throwError($this->_key, 'boolean');
        }
        return $this;
    }

    /**
     * 校验 - 值不能等于 0
     * 
     * 仅配合 int 使用，必须先调用 int，再调用 notZero
     *
     * @return boolean|\Utils_Validation_VerifyTools
     */
    public function notZero()
    {
        if ($this->_val === 0) {
            return $this->_throwError($this->_key, 'notZero');
        }
        return $this;
    }

    /**
     * 校验 - 值是数字
     *
     * @return boolean|\Utils_Validation_VerifyTools
     */
    public function number()
    {
        if (0 === preg_match('/^[0-9]+$/i', $this->_val)) {
            return $this->_throwError($this->_key, 'number');
        }
        return $this;
    }

    /**
     * 校验 - 值是手机号
     *
     * @return boolean|\Utils_Validation_VerifyTools
     */
    public function mobile()
    {
        $mobileSegment = Utan_Utils_Config::getOfPublic('/configs/mobile_segment.ini', 'php')->toArray();
        if (0 === preg_match($mobileSegment['condition'], $this->_val)) {
            return $this->_throwError($this->_key, 'mobile');
        }
        return $this;
    }
    
    /**
     * 检验 - 值是电话号码 021-56478142
     * @return boolean|\Utils_Validation_VerifyTools
     */
    public function telphone()
    {
        if (0 === preg_match('/^(\(\d{3,4}\)|\d{3,4}-|\s)?\d{7,8}$/i', $this->_val)) {
            return $this->_throwError($this->_key, 'telphone');
        }
        return $this;
    }
    
    /**
     * 检验 - 值是邮政编码
     * @return boolean|\Utils_Validation_VerifyTools
     */
    public function zipcode()
    {
        if (0 === preg_match('/^[0-9]{6}$/', $this->_val)) {
            return $this->_throwError($this->_key, 'zipcode');
        }
        return $this;        
    }
    
    /**
     * 检验 - 值是否json格式
     * @return \Utils_Validation_VerifyTools
     */
    public function json()
    {
        if (is_null(json_decode($this->_val))){
            return $this->_throwError($this->_key, 'json');
        }
        return $this;
    }
    
    /**
     * 检测邮箱字符串是否符合规范
     *
     * @return boolean|\Utils_Validation_VerifyTools
     */
    public function email()
    {
        $val = $this->_val;
        $atIndex = strrpos($val, "@");
        if (is_bool($atIndex) && !$atIndex) {
            return $this->_throwError($this->_key, 'email_format');
        }
        $domain = substr($val, $atIndex + 1);
        $local = substr($val, 0, $atIndex);
        $localLen = strlen($local);
        $domainLen = strlen($domain);
        if ($localLen < 1 || $localLen > 64) {
            return $this->_throwError($this->_key, 'email_format1');
        } else if ($domainLen < 1 || $domainLen > 255) {
            return $this->_throwError($this->_key, 'email_format2');
        } else if ($local[0] == '.' || $local[$localLen - 1] == '.') {
            return $this->_throwError($this->_key, 'email_format3');
        } else if (preg_match('/\\.\\./', $local)) {
            return $this->_throwError($this->_key, 'email_format4');
        } else if (!preg_match('/^[A-Za-z0-9\\-\\._]+$/', $domain)) {
            return $this->_throwError($this->_key, 'email_format5');
        } else if (preg_match('/\\.\\./', $domain)) {
            return $this->_throwError($this->_key, 'email_format6');
        } else if (!preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/', str_replace("\\\\", "", $local))) {
            if (!preg_match('/^"(\\\\"|[^"])+"$/', str_replace("\\\\", "", $local))) {
                return $this->_throwError($this->_key, 'email_format7');
            }
        }
        return $this;
    }

    /**
     * 检测邮箱是否正确 - @ 后的域名是否是邮件服务器
     *
     * @return boolean|\Utils_Validation_VerifyTools
     */
    public function emailDns()
    {
        $val = $this->_val;
        $atIndex = strrpos($val, "@");
        if (is_bool($atIndex) && !$atIndex) {
            return $this->_throwError($this->_key, 'email_format');
        }
        $domain = substr($val, $atIndex + 1);
        if (!(checkdnsrr($domain, "MX"))) {
            return $this->_throwError($this->_key, 'email_errordns');
        }
        return $this;
    }

    /**
     * 【检测必须小于】字符长度大于指定的【字符长度最大边界】
     * 
     * @param int $length 字符长度最大边界
     * @param string $encode 字符编码【默认utf8】
     * @return \Utils_Validation_VerifyTools
     */
    public function sLessThen($length, $encoding = 'utf8')
    {
        $valLength = mb_strlen($this->_val, $encoding);
        if ($valLength > $length) {
            return $this->_throwError($this->_key, 'sLessThen');
        }
        return $this;
    }

    /**
     * 【检测必须大于】字符长度小于指定的【字符长度最大边界】
     * 
     * @param int $length 字符长度最小边界
     * @param string $encode 字符编码【默认utf8】
     * @return \Utils_Validation_VerifyTools
     */
    public function sGreaterThen($length, $encoding = 'utf8')
    {
        $valLength = mb_strlen($this->_val, $encoding);
        if ($valLength < $length) {
            return $this->_throwError($this->_key, 'sGreaterThen');
        }
        return $this;
    }

    /**
     * 【检测字符长度范围】字符长度必须符在给定的范围内
     * 
     * @param int $minLength
     * @param int $maxLenght
     * @param string $encoding
     */
    public function srange($minLength, $maxLength, $encoding = 'utf8')
    {
        $valLength = mb_strlen($this->_val, $encoding);
        if ($valLength < $minLength || $valLength > $maxLength) {
            return $this->_throwError($this->_key, 'srange');
        }
        return $this;
    }

    /**
     * 检测 0-9A-Za-z_-
     */
    public function neu()
    {
        if (0 === preg_match('/^[0-9A-Za-z_-]+$/i', $this->_val)) {
            return $this->_throwError($this->_key, 'neu');
        }
        return $this;
    }

    /**
     * 检测 qq 号
     * 
     * @return \Utils_Validation_VerifyTools
     */
    public function qq()
    {
        if (0 === preg_match('/^[1-9]\d{4,9}$/i', $this->_val)) {
            return $this->_throwError($this->_key, 'qq');
        }
        return $this;
    }

    /**
     * 验证 ip 格式
     * 
     * @param string $type null=仅验证IP格式 | ipv4=验证ipv4格式 格式 | ipv6=验证ipv6格式
     * @return \Utils_Validation_VerifyTools
     */
    public function ip($type = null)
    {
        if ($type === 'ipv4') {
            $flag = filter_var($this->_val, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
        } elseif ($type === 'ipv6') {
            $flag = filter_var($this->_val, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6);
        } else {
            $flag = filter_var($this->_val, FILTER_VALIDATE_IP);
        }
        if (!$flag) {
            return $this->_throwError($this->_key, 'ip');
        }
        return $this;
    }

    /**
     * 验证 ip 非私有IP 范围
     * 
     * @return \Utils_Validation_VerifyTools
     */
    public function ippr($type = null)
    {
        if (!filter_var($this->_val, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE)) {
            return $this->_throwError($this->_key, 'ippr');
        }
        return $this;
    }

    /**
     * 验证 ip 非保留的 IP 范围
     * 
     * @return \Utils_Validation_VerifyTools
     */
    public function iprr($type = null)
    {
        if (!filter_var($this->_val, FILTER_VALIDATE_IP, FILTER_FLAG_NO_RES_RANGE)) {
            return $this->_throwError($this->_key, 'iprr');
        }
        return $this;
    }

    /**
     * 整个优谈 - 注册密码的校验
     * 
     * @return \Utils_Validation_VerifyTools
     */
    public function pwd()
    {
        if (0 === preg_match('/^[a-zA-Z0-9]{6,20}$/i', $this->_val)) {
            return $this->_throwError($this->_key, 'format');
        }
        return $this;
    }

    /**
     * 检测违禁词
     * 
     * @param string $module 使用哪种违禁词模块进行核查
     * @return \Utils_Validation_VerifyTools
     */
    public function word($module)
    {
        //统一走Java 处理 提高性能
        $data['method']='garbage';
        $data['content']=$this->_val;
        $res = Utan_Utils_Curl::post("http://hbase.utan.com:8080/utan-server/Api", http_build_query($data));
        if($res=='true'){
            return $this->_throwError($this->_key, 'word');
        }
         return $this;
        
        
        $words = Utan_Logic_ShieldWordFilter::getInstance()->getAllWordsByType($module);
        if ($words && is_array($words)) {
            foreach ($words as $word) {
                if (strpos($this->_val, $word) !== false) {
                    return $this->_throwError($this->_key, 'word');
                }
            }
        }
        return $this;
    }

    /**
     * 整个优谈网 - 注册用户名校验
     * 
     * @param boolean $noCheckRegWord 是否检测违禁词
     * @return \Utils_Validation_VerifyTools
     */
    public function realname($noCheckRegWord = false)
    {
        $this->required();

        $realname = str_replace(".", "", $this->_val);
        if (0 === preg_match('/^[\s0-9A-Za-z\x80-\xff\-]+$/i', $realname)) {
            return $this->_throwError($this->_key, 'format');
        }

        $this->sLessThen(20);

        $this->sGreaterThen(1);

        if (!$noCheckRegWord) {
            $this->word('reg');
        }

        return $this;
    }

    /**
     * 整个优谈网 - 性别校验
     * 
     * @return \Utils_Validation_VerifyTools
     */
    public function sex()
    {
        if (empty($this->_val)) {
            $this->_val = 0;
        }

        $this->int();

        return $this;
    }
    
    /**
     * 日期格式
     * 
     * @param string $format
     * @return Utils_Validation_VerifyTools
     */
    public function date($format)
    {
        if (gettype($this->_val) != 'string') {
            return $this->_throwError($this->_key, 'format');
        }
        $flag = true;
        switch ($format) {
            case 'ymd-0':
                $reg = "/^(?:19|20)[0-9]{2}(?:[1-9]|1[0-2])(?:[1-9]|1[0-9]|2[0-9]|3[0-1])$/i";
                if(0 == preg_match($reg, $this->_val)) {
                    $flag = false;
                }
                break;
            case 'ymd':
                $reg = "/^(?:19|20)[0-9]{2}(?:0[1-9]|1[0-2])(?:0[1-9]|1[0-9]|2[0-9]|3[0-1])$/i";
                if(0 == preg_match($reg, $this->_val)) {
                    $flag = false;
                }
                break;
            case 'y-m-d-0':
                $reg = "/^(?:19|20)[0-9]{2}-(?:0[1-9]|1[0-2])-(?:0[1-9]|1[0-9]|2[0-9]|3[0-1])$/i";
                if(0 == preg_match($reg, $this->_val)) {
                    $flag = false;
                }
                break;
            case 'y-m-d':
                $reg = "/^(?:19|20)[0-9]{2}-(?:[1-9]|1[0-2])-(?:[1-9]|1[0-9]|2[0-9]|3[0-1])$/i";
                if(0 == preg_match($reg, $this->_val)) {
                    $flag = false;
                }
                break;
            case 'y-m-d h:i:s':
                $reg = "/^(?:19|20)[0-9]{2}-(?:[1-9]|1[0-2])-(?:[1-9]|1[0-9]|2[0-9]|3[0-1])\s{1}(?:0[1-9]|1[0-9]|2[0-3]):(?:0[0-9]|1[0-9]|2[0-9]|3[0-9]|4[0-9]|5[0-9]):(?:0[0-9]|1[0-9]|2[0-9]|3[0-9]|4[0-9]|5[0-9])$/i";
                if(0 == preg_match($reg, $this->_val)) {
                    $flag = false;
                }
                break;
            case 'y-m-d h:i:s-0':
                $reg = "/^(?:19|20)[0-9]{2}-(?:0[1-9]|1[0-2])-(?:0[1-9]|1[0-9]|2[0-9]|3[0-1])\s{1}(?:00|0[1-9]|1[0-9]|2[0-3]):(?:00|0[0-9]|1[0-9]|2[0-9]|3[0-9]|4[0-9]|5[0-9]):(?:00|0[0-9]|1[0-9]|2[0-9]|3[0-9]|4[0-9]|5[0-9])$/i";
                if(0 == preg_match($reg, $this->_val)) {
                    $flag = false;
                }
                break;
        }
        if (false === $flag) {
            return $this->_throwError($this->_key, 'format');
        }
        return $this;
    }

//-----以下为 protected private

    /**
     * 处理每个校验函数的错误
     * 
     * @param string $errorKey
     * @param string $errorMessage
     * @return boolean
     * @throws Utan_Utils_Validation_Exception
     */
    private function _throwError($errorKey, $errorMsg)
    {
        $this->_lastError = array(
            'errorKey' => $errorKey,
            'errorMsg' => $errorMsg,
        );
        if ($this->_validateMode === 'verify') {
            throw new Utils_Validation_Exception($errorKey, $errorMsg);
        } else {
            return false;
        }
    }

}