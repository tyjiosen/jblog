<?php

use Jiosen\Lib\Cache;
use Jiosen\Lib\Db;
use Jiosen\Lib\Request;
use Jiosen\Lib\Upload;
use Jiosen\Lib\Validate;

if(!function_exists('dump'))
{
    /**
     * 浏览器打印数据
     * @param mixed $vars 参数
     * @return void
     */
    function dump(...$vars)
    {
        ob_start();
        var_dump(...$vars);

        $output = ob_get_clean();
        $output = preg_replace('/\]\=\>\n(\s+)/m', '] => ', $output);

        if (!extension_loaded('xdebug')) {
            $output = htmlspecialchars($output, ENT_SUBSTITUTE);
        }
        $output = '<pre>' . $output . '</pre>';

        echo $output;
    }
}

if(!function_exists('db'))
{
    /**
     * 连接数据库
     * @param array $config 配置
     * @return Db
     */
    function db($config)
    {
        return new Db($config);
    }
}

if(!function_exists('cache'))
{
    /**
     * 缓存实例
     * @param string $cachePath 缓存保存路径
     * @return Cache
     */
    function cache($cachePath='')
    {
        return new Cache($cachePath);
    }
}

if(!function_exists('get_file_extension'))
{
    /**
     * 获取文件后缀
     * @param string $filename 文件名
     * @return string
     */
    function get_file_extension($filename)
    {
        $pathinfo = pathinfo($filename);
        return strtolower($pathinfo['extension']);
    }
}

if(!function_exists('upload_files'))
{
    /**
     * 上传文件
     * @param string $path 保存路径
     * @param string $name 要上传的文件名 留空则全部上传
     * @param array $config fileExt-允许后缀 fileSize-允许上传最大字节
     * @return array|Exception 
     */
    function upload_files($path,$name='',$config=[])
    {
        try {
            $files = Upload::file($path,$name,$config);
            
        } catch (\Exception $e) {
            // echo $e->getMessage();
            $files = [];
        }

        return $files;
    }
}

if(!function_exists('request'))
{
    /**
     * 请求类
     * @return Request
     */
    function request()
    {
        return Request::getInstance();
    }
}

if(!function_exists('validate'))
{
    /**
     * 验证
     * @return bool
     */
    function validate($type,$value)
    {
        return call_user_func('\Jiosen\Lib\Validate::' . $type, $value);
    }
}

