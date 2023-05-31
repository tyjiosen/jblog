<?php
/*
* @Descripttion: Request类 tp简化
* @Author: jiosen <4631458@qq.com>
* @Date: 2023-05-30 11:24:09
*/
namespace Jiosen\Lib;

class Request
{
    private static $instance;  
    
    //php://input
    private $input;

    //$_SERVER;
    private $server = [];

    //get
    private $get = [];

    //post
    private $post = [];

    //获取当前URL
    private $url = '';

    //获取当前URL 不含QUERY_STRING
    private $baseUrl = '';

    //协议
    private $scheme = '';

    //域名
    private $host = '';

    //根域名
    private $root = '';

    //端口
    private $port = '';

    //请求类型
    private $method = '';

    //header
    private $header = [];

    //input 转化
    private $inputData = [];

    //ip
    private $ip = '';


    /**
     * 构造函数
     * @return void
     */
    private function __construct() {

        $this->input = file_get_contents('php://input');
        $this->server = $_SERVER;
        $this->get     = $_GET;
        $this->post    = $_POST ?: [];
        
        
        if (function_exists('apache_request_headers') && $result = apache_request_headers()) {
            $header = $result;
        } else {
            $header = [];
            $server = $_SERVER;
            foreach ($server as $key => $val) {
                if (0 === strpos($key, 'HTTP_')) {
                    $key          = str_replace('_', '-', substr($key, 5));
                    $header[$key] = $val;
                }
            }
            if (isset($server['CONTENT_TYPE'])) {
                $header['CONTENT-TYPE'] = $server['CONTENT_TYPE'];
            }
            if (isset($server['CONTENT_LENGTH'])) {
                $header['CONTENT-LENGTH'] = $server['CONTENT_LENGTH'];
            }
        }

        $this->header = $header;
    }  
    
    public static function getInstance() {  
        if (!self::$instance) {  
            self::$instance = new self();  
        }  
        return self::$instance;  
    }

    /**
     * 获取当前包含协议的域名
     * @param  bool $port 是否需要去除端口号
     * @return string
     */
    public function domain(bool $port = false)
    {
        return $this->scheme() . '://' . $this->host($port);
    }

    /**
     * 获取当前根域名
     * @return string
     */
    public function rootDomain()
    {
        if($this->root=='')
        {
            $item  = explode('.', $this->host());
            $count = count($item);
            $this->root  = $count > 1 ? $item[$count - 2] . '.' . $item[$count - 1] : $item[0];
        }
        
        return $this->root;
    }

    /**
     * 获取当前完整URL 包括QUERY_STRING
     * @param  bool $complete 是否包含完整域名
     * @return string
     */
    public function url($complete = false)
    {
        if($this->url==''){

            if ($this->server('HTTP_X_REWRITE_URL')) {
                $this->url  = $this->server('HTTP_X_REWRITE_URL');
            } elseif ($this->server('REQUEST_URI')) {
                $this->url  = $this->server('REQUEST_URI');
            } elseif ($this->server('ORIG_PATH_INFO')) {
                $this->url  = $this->server('ORIG_PATH_INFO') . (!empty($this->server('QUERY_STRING')) ? '?' . $this->server('QUERY_STRING') : '');
            } elseif (isset($_SERVER['argv'][1])) {
                $this->url  = $_SERVER['argv'][1];
            } else {
                $this->url  = '';
            }
        }


        return $complete ? $this->domain() . $this->url : $this->url;
    }

    /**
     * 当前URL地址中的scheme参数
     * @return string
     */
    public function scheme()
    {
        if($this->scheme==''){

            $this->scheme = $this->isSsl() ? 'https' : 'http';
        }

        return $this->scheme;
    }

    /**
     * 当前是否ssl
     * @return bool
     */
    public function isSsl()
    {
        if ($this->server('HTTPS') && ('1' == $this->server('HTTPS') || 'on' == strtolower($this->server('HTTPS')))) {
            return true;
        } elseif ('https' == $this->server('REQUEST_SCHEME')) {
            return true;
        } elseif ('443' == $this->server('SERVER_PORT')) {
            return true;
        } elseif ('https' == $this->server('HTTP_X_FORWARDED_PROTO')) {
            return true;
        }

        return false;
    }

    /**
     * 获取当前URL 不含QUERY_STRING
     * @param  bool $complete 是否包含完整域名
     * @return string
     */
    public function baseUrl($complete = false)
    {
        if (!$this->baseUrl) {
            $str           = $this->url();
            $this->baseUrl = strpos($str, '?') ? strstr($str, '?', true) : $str;
        }

        return $complete ? $this->domain() . $this->baseUrl : $this->baseUrl;
    }

    /**
     * 获取当前请求的时间
     * @param  bool $float 是否使用浮点类型
     * @return integer|float
     */
    public function time($float = false)
    {
        return $float ? $this->server('REQUEST_TIME_FLOAT') : $this->server('REQUEST_TIME');
    }

    /**
     * 当前的请求类型
     * @param  bool $origin 是否获取原始请求类型
     * @return string
     */
    public function method($origin = false)
    {
        if ($origin) {
            // 获取原始请求类型
            return $this->server('REQUEST_METHOD') ?: 'GET';
        } elseif (!$this->method) {
            if ($this->server('HTTP_X_HTTP_METHOD_OVERRIDE')) {
                $this->method = strtoupper($this->server('HTTP_X_HTTP_METHOD_OVERRIDE'));
            } else {
                $this->method = $this->server('REQUEST_METHOD') ?: 'GET';
            }
        }

        return $this->method;
    }

    /**
     * 是否为GET请求
     * @return bool
     */
    public function isGet()
    {
        return $this->method() == 'GET';
    }

    /**
     * 是否为POST请求
     * @return bool
     */
    public function isPost()
    {
        return $this->method() == 'POST';
    }

    /**
     * 是否为PUT请求
     * @return bool
     */
    public function isPut()
    {
        return $this->method() == 'PUT';
    }

    /**
     * 是否为DELTE请求
     * @return bool
     */
    public function isDelete()
    {
        return $this->method() == 'DELETE';
    }

    /**
     * 是否为HEAD请求
     * @return bool
     */
    public function isHead()
    {
        return $this->method() == 'HEAD';
    }

    /**
     * 是否为PATCH请求
     * @return bool
     */
    public function isPatch()
    {
        return $this->method() == 'PATCH';
    }

    /**
     * 是否为OPTIONS请求
     * @return bool
     */
    public function isOptions()
    {
        return $this->method() == 'OPTIONS';
    }

    /**
     * 是否为cli
     * @return bool
     */
    public function isCli()
    {
        return PHP_SAPI == 'cli';
    }

    /**
     * 是否为cgi
     * @return bool
     */
    public function isCgi()
    {
        return strpos(PHP_SAPI, 'cgi') === 0;
    }

    /**
     * 当前是否Ajax请求
     * @return bool
     */
    public function isAjax()
    {
        $value  = $this->server('HTTP_X_REQUESTED_WITH');
        $result = $value && 'xmlhttprequest' == strtolower($value) ? true : false;

        return $result;
    }

    /**
     * 获取当前请求的参数
     * @param  array $data 数据
     * @param  string $name 变量名
     * @param  mixed        $default 默认值
     * @param  string $filter 过滤方法
     * @return mixed
     */
    public function getData($data,$name='',$default=null,$filter='')
    {
        // if($name){
        //     return isset($data[$name])?call_user_func($filter,$data[$name]):$default;
        // }else{
        //     return array_map(function($one) use($filter){
        //         return call_user_func($filter,$one);
        //     },$data);
        // }

        if($name){
            return isset($data[$name])?($filter?call_user_func($filter,$data[$name]):$data[$name]):$default;
        }else{
            if($filter==''){
                return $data;
            }
            return array_map(function($one) use($filter){
                return call_user_func($filter,$one);
            },$data);
        }
    }

    /**
     * 获取header参数 
     * @param  string|array $name 变量名
     * @param  mixed $default 默认值
     * @return mixed
     */
    public function header($name='',$default=null)
    {
        return $name == '' ? $this->header : (isset($this->header[$name]) ? $this->header[$name] : $default );
    }

    /**
     * 当前请求 HTTP_CONTENT_TYPE
     * @return string
     */
    public function contentType()
    {
        $contentType = $this->header('Content-Type');

        if ($contentType) {
            if (strpos($contentType, ';')) {
                [$type] = explode(';', $contentType);
            } else {
                $type = $contentType;
            }
            return trim($type);
        }

        return '';
    }

    /**
     * 获取php//input参数
     * @return array
     */
    public function getInputData()
    {
        if(empty($this->inputData)){

            $contentType = $this->contentType();
            if ('application/x-www-form-urlencoded' == $contentType) {
                parse_str($this->input, $data);
                $this->inputData =  $data;
            } elseif (false !== strpos($contentType, 'json')) {
                $this->inputData =  (array) json_decode($this->input, true);
            }
        }
        

        return $this->inputData;
    }

    /**
     * 获取当前请求的参数
     * @param  string $name 变量名
     * @param  mixed        $default 默认值
     * @param  string $filter 过滤方法
     * @return mixed
     */
    public function param($name='',$default=null,$filter='trim')
    {
        // xx get.xx post.xx
        if(strpos($name,'.')){
            $arr = explode(".",$name);
            if(count($arr) != 2){
                return [];
            }
            $method = strtoupper(trim($arr[0]));
            $name = trim($arr[1]);
        }else{
            $method = $this->method(true);
        }

        switch($method)
        {
            case 'GET':
                $data = $this->get;
                break;
            case 'POST':
                $data = $this->post;
                break;
            default:
                $data = $this->getInputData();
                break;    
        }

        return $this->getData($data,$name,$default,$filter);
    }

    /**
     * 获取GET参数
     * @param  string $name 变量名
     * @param  mixed        $default 默认值
     * @param  string $filter 过滤方法
     * @return mixed
     */
    public function get($name = '', $default = null, $filter = '')
    {
        return $this->getData($this->get,$name,$default,$filter);
    }

    /**
     * 获取POST参数
     * @param  string $name 变量名
     * @param  mixed        $default 默认值
     * @param  string $filter 过滤方法
     * @return mixed
     */
    public function post($name = '', $default = null, $filter = '')
    {
        return $this->getData($this->post,$name,$default,$filter);
    }

    /**
     * 获取INPUT参数
     * @param  string $name 变量名
     * @param  mixed        $default 默认值
     * @param  string $filter 过滤方法
     * @return mixed
     */
    public function input($name = '', $default = null, $filter = '')
    {
        return $this->getData($this->getInputData(),$name,$default,$filter);
    }

    /**
     * 获取session数据
     * @param  string $name 数据名称
     * @param  string $default 默认值
     * @return mixed
     */
    public function session($name = '', $default = null)
    {
        session_start();

        if(strpos($name,'.')){
            $arr = explode(".",$name);
            if(!isset($_SESSION[$arr[0]])){
                return $default;
            }

            $data = $_SESSION[$arr[0]];
            
            for($i=1;$i<=count($arr);$i++)
            {
                $key = trim($arr[$i]);

                if($key==''){
                    break;
                }
                
                if(empty($data[$key]))
                {
                    $data = $default;
                    break;
                }
                

                $data = $data[$arr[$i]];
            }

            return $data;

        }else{
            return $name == '' ? $_SESSION : (isset($_SESSION[$name])?$_SESSION[$name]:$default);
        }

        
    }

    /**
     * 获取cookie参数
     * @param  string $name 数据名称
     * @param  string $default 默认值
     * @param  string $filter 过滤方法
     * @return mixed
     */
    public function cookie($name = '', $default = null, $filter = 'trim')
    {
        return $this->getData($_COOKIE,$name,$default,$filter);
    }

    /**
     * 获取上传的文件信息
     * @param  string $name 名称
     * @return mixed
     */
    public function file($name = '')
    {
        return $name == '' ? $_FILES : (isset($_FILES[$name])?$_FILES[$name]:null);
    }

    /**
     * 获取客户端IP地址
     * @return string
     */
    public function ip()
    {
        if($this->ip == ''){
            $this->ip = $this->server('REMOTE_ADDR', '');
        }
        
        if (!$this->isValidIP($this->ip)) {
            $this->ip = '0.0.0.0';
        }

        return $this->ip;
    }

    /**
     * 检测是否是合法的IP地址
     * @param string $ip   IP地址
     * @param string $type IP地址类型 (ipv4, ipv6)
     *
     * @return boolean
     */
    public function isValidIP($ip, $type = '')
    {
        switch (strtolower($type)) {
            case 'ipv4':
                $flag = FILTER_FLAG_IPV4;
                break;
            case 'ipv6':
                $flag = FILTER_FLAG_IPV6;
                break;
            default:
                $flag = 0;
                break;
        }

        return boolval(filter_var($ip, FILTER_VALIDATE_IP, $flag));
    }

    /**
     * 检测是否使用手机访问
     * @return bool
     */
    public function isMobile()
    {
        if ($this->server('HTTP_VIA') && stristr($this->server('HTTP_VIA'), "wap")) {
            return true;
        } elseif ($this->server('HTTP_ACCEPT') && strpos(strtoupper($this->server('HTTP_ACCEPT')), "VND.WAP.WML")) {
            return true;
        } elseif ($this->server('HTTP_X_WAP_PROFILE') || $this->server('HTTP_PROFILE')) {
            return true;
        } elseif ($this->server('HTTP_USER_AGENT') && preg_match('/(blackberry|configuration\/cldc|hp |hp-|htc |htc_|htc-|iemobile|kindle|midp|mmp|motorola|mobile|nokia|opera mini|opera |Googlebot-Mobile|YahooSeeker\/M1A1-R2D2|android|iphone|ipod|mobi|palm|palmos|pocket|portalmmm|ppc;|smartphone|sonyericsson|sqh|spv|symbian|treo|up.browser|up.link|vodafone|windows ce|xda |xda_)/i', $this->server('HTTP_USER_AGENT'))) {
            return true;
        }

        return false;
    }

    /**
     * 获取当前域名
     * @param  bool $strict 是否去掉端口
     * @return string
     */
    public function host($strict=false)
    {
        if($this->host == ''){
            $this->host = strval($this->server('HTTP_X_FORWARDED_HOST') ?: $this->server('HTTP_HOST'));
        }
        
        return true === $strict && strpos($this->host, ':') ? strstr($this->host, ':', true) : $this->host;
    }

    /**
     * 获取当前端口
     * @return string
     */
    public function port()
    {
        if($this->port==''){
            $this->port = $this->server('HTTP_X_FORWARDED_PORT') ?: $this->server('SERVER_PORT', '');
        }
        return (int) $this->port;
    }

    /**
     * 获取server参数
     * @param  string $name 数据名称
     * @param  string $default 默认值
     * @return mixed
     */
    public function server($name='',$default='')
    {
        return $name == '' ? $this->server : (isset($this->server[$name]) ? $this->server[$name] : $default);
    }
    

}
