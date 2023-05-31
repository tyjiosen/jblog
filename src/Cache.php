<?php
/*
 * @Descripttion: 简单文件缓存
 * @Author: jiosen <4631458@qq.com>
 * @Date: 2023-05-26 10:03:31
 */

namespace Jiosen\Lib;

class Cache
{
    
    private $cachePath = '';
    
    /**
     * 构造函数
     * @param string $cachePath 缓存目录
     */
    public function __construct($cachePath='')
    {
        if($cachePath == '')
        {
            if(empty($_SERVER['SCRIPT_FILENAME'])){
                list($childClass, $caller) = debug_backtrace(false, 2);
                if(isset($childClass['file']))
                {
                    $cachePath = dirname($childClass['file']) . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR;
                }
            }else{
                $cachePath = dirname($_SERVER['SCRIPT_FILENAME']) . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR;
            }
        }

        if (substr($cachePath, -1) != DIRECTORY_SEPARATOR) {
            $cachePath .= DIRECTORY_SEPARATOR;
        }

        if (!is_dir($cachePath)) {
            try {
                mkdir($cachePath, 0755, true);
            } catch (\Exception $e) {
                throw new \Exception($e->getMessage());
            }
        }

        $this->cachePath = $cachePath; 

    }

    /**
     * 获取缓存
     * @param string $key 缓存key
     * @param mixed $default 默认值
     * @return mixed
     */
    public function get($key,$default=null)
    {
        $res = $this->getInfo($key);
        return !is_null($res)?$res['content']:$default;
    }

    /**
     * 设置缓存
     * @param string $key 缓存key
     * @param mixed $data 缓存内容
     * @return bool
     */
    public function set($key,$data=[],$expire=0)
    {
        $filename = $this->getCacheKey($key);
        if(!file_exists(dirname($filename))){
            try {
                mkdir(dirname($filename), 0755, true);
            } catch (\Exception $e) {
                throw new \Exception($e->getMessage());
            }
        }

        if (!is_numeric($data)) {
            $data = serialize($data);
        }

        $data   = "<?php\n//" . sprintf('%012d', $expire) . "\n exit();?>\n" . $data;

        $result = file_put_contents($filename, $data);

        if ($result) {
            clearstatcache();
            return true;
        }

        return false;

    }

    /**
     * 缓存自增
     * @param string $key 缓存key
     * @param int $num 步长
     * @return int|bool
     */
    public function inc($key,$num=1)
    {
        $res = $this->getInfo($key);
        if(is_null($res) || !is_numeric($res['content'])){
            return false;
        }

        $num = $res['content'] + $num;

        return $this->set($key,$num,$res['expire'])?$num:false;
    }

    /**
     * 缓存自减
     * @param string $key 缓存key
     * @param int $num 步长
     * @return int|bool
     */
    public function dec($key,$num=1)
    {
        return $this->inc($key,0-$num);
    }

    /**
     * 删除缓存
     * @param string $key 缓存key
     * @return bool
     */
    public function del($key)
    {
        $filename = $this->getCacheKey($key);

        try {
            return is_file($filename) && unlink($filename);
        } catch (\Exception $e) {
            return false; 
        }
    }

    /**
     * 获取缓存文件
     * @param string $name 缓存key
     * @return string
     */
    private function getCacheKey($name)
    {
        $name = md5($name);

        return $this->cachePath . substr($name, 0, 2) . DIRECTORY_SEPARATOR . substr($name, 2) . '.php';
    }

    /**
     * 获取缓存内容
     * @param string $key 缓存key
     * @return mixed
     */
    private function getInfo($key)
    {
        $filename = $this->getCacheKey($key);
        if(!file_exists($filename)){
            return null;
        }

        $content = @file_get_contents($filename);

        if (false !== $content) {
            $expire = (int) substr($content, 8, 12);
            if (0 != $expire && time() - $expire > filemtime($filename)) {
                //缓存过期删除缓存文件
                @unlink($filename);
                return null;
            }

            $content = substr($content, 32);

            return ['content'=>is_numeric($content)?$content:unserialize($content),'expire'=>$expire];
        }

        return null;

    }
}