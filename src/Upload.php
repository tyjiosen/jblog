<?php
/*
* @Descripttion: 上传文件
* @Author: jiosen <4631458@qq.com>
* @Date: 2023-05-29 11:06:06
*/
namespace Jiosen\Lib;

use Exception;

class Upload
{
     /**
     * 上传文件
     * @param string $path 保存路径
     * @param string $name 要上传的文件名 留空则全部上传
     * @param array $config fileExt-允许后缀 fileSize-允许上传最大字节
     * @return array|Exception 
     */
    static public function file($path,$name='',$config=[])
    {

        if(!is_dir($path)){
            throw new Exception("目录不合法:{$path}");
        }

        if(substr($path,-1)!=DIRECTORY_SEPARATOR){
            $path .= DIRECTORY_SEPARATOR;
        }

        $fileSize = isset($config['fileSize'])?intval($config['fileSize']):0;
        $fileExt = isset($config['fileExt'])?explode(",",trim($config['fileExt'])):[];

        $files = [];

        if($name!=''){
            $keys = explode(",",$name);
            foreach($keys as $value)
            {       
                if(!isset($_FILES[$value])){
                    throw new Exception("找不到文件:{$value}");
                }
                $files[$value] = $_FILES[$value];
            }
            
        }else{
            $files = $_FILES;
        }

        $moveFiles = [];

        foreach($files as $key => $value){
            //$this->addFile($value['tmp_name'], $value['name'], $value['type'], $value['error']);
            //检查是否post
            if(!is_uploaded_file($value['tmp_name'])){
                throw new Exception("非上传文件:{$key}");
            }
            //检查错误
            if($value['error'] > 0 ){
                self::throwUploadFileError($value['error']);
            }

            //$file = new \SplFileInfo($path);

            //检查文件大小
            if($fileSize > 0 && filesize($value['tmp_name']) > $fileSize)
            {
                throw new Exception("文件大小不合法:{$key}");
            }

            //检查后缀
            $extension = get_file_extension($value['name']);

            if($extension=='php' || ($fileExt && !in_array($extension,$fileExt)))
            {
                throw new Exception("文件后缀不合法:{$key}");
            }

            $moveFiles[$key] = [
                'path' => $value['tmp_name'],
                'extension' => $extension
            ]; 
        }
        
        //检查完再保存到目录
        return self::move($path,$moveFiles);

    }

    /**
     * 保存文件
     * @param string $savePath 保存路径
     * @param array $files 要保存的文件
     * @return array|Exception 
     */
    static public function move($savePath,$files)
    {
        
       
       if(count($files)==0){
            return [];
       } 

       if(!is_dir($savePath)){
            throw new Exception("目录不合法:{$savePath}"); 
       }

       $dir = date('Y-m') . DIRECTORY_SEPARATOR;

       if(!is_dir($savePath . $dir)){
            try {
                mkdir($savePath . $dir, 0755, true);
            } catch (Exception $e) {
                throw new Exception($e->getMessage());
            }
        }

        $res = [];

        foreach($files as $key => $value)
        {
            $fielname = uniqid($key) . '.' . $value['extension'];

            if(!rename($value['path'], $savePath . $dir . $fielname)){
                throw new Exception("文件上传失败:{$key}");
            }

            $res[$key] = $dir . $fielname;
        }

        return $res;

    }

    /**
     * 错误信息
     * @param int $error 错误码
     * @return Exception 
     */
    static private function throwUploadFileError($error)
    {
        static $fileUploadErrors = [
            1 => '上传文件大小超过了最大值！',
            2 => '上传文件大小超过了最大值！',
            3 => '文件只有部分被上传！',
            4 => '没有文件被上传！',
            6 => '找不到临时文件夹！',
            7 => '文件写入失败！',
        ];

        throw new Exception($fileUploadErrors[$error]);
    }

}