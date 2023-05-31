<?php
/*
* @Descripttion: http 类
* @Author: jiosen <4631458@qq.com>
* @Date: 2023-05-31 11:05:59
*/
namespace Jiosen\Lib;

class Http
{
    /**
     * curl 请求
     * @param  string $url 请求地址
     * @param  array $data 请求参数
     * @param  int $timeout 超时时间
     * @param  array $userHeader header
     * @param  string $userAgent user-agent
     * @param  string $proxy 代理
     * @param  string $cookieFile cookie文件
     * @param  array $files 上传文件
     * @return array
     */
    static public function request($url, $data = [], $timeout = 30, $userHeader = [], $userAgent = '',$proxy = '', $cookieFile = '', $files = [])
	{
		
		$curl = curl_init();
		$header = array(
			'Accept-Language: zh-cn',
			'Connection: Keep-Alive',
			'Cache-Control: no-cache'
		);

		if ($userHeader) {
			$header = array_merge($header, $userHeader);
		}

		if ($proxy) {
			curl_setopt($curl, CURLOPT_PROXY, $proxy);
		}

		if ($cookieFile) {
			curl_setopt($curl, CURLOPT_COOKIEFILE, $cookieFile);
			curl_setopt($curl, CURLOPT_COOKIEJAR, $cookieFile);
		}



		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $userAgent == '' && $userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/113.0.0.0 Safari/537.36';

		curl_setopt($curl, CURLOPT_USERAGENT, $userAgent);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $header);

		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);

		if ($timeout > 0) {
			curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
			curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $timeout);
		}

		curl_setopt($curl, CURLINFO_HEADER_OUT, true);//header

		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($curl, CURLOPT_MAXREDIRS, 10);
		curl_setopt($curl, CURLOPT_AUTOREFERER, true);//自动跳转


		$isPost = false;
		$isFile = false;
		if (!empty($data)) {
			$isPost = true;
		}
		if(!empty($files)){
			foreach($files as $name => $file){
				$data[$name] = curl_file_create(realpath($file['path']), $file['type'], $file['name']);
			}
			$isPost = false;
			$isFile = true;
		}


		if($isPost && is_array($data)){
			curl_setopt($curl, CURLOPT_POST, true);
			if(is_array($data)) $data = http_build_query($data);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
		}
        if($isPost && is_string($data)){
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }

		if($isFile){
			curl_setopt($curl, CURLOPT_POST, true);
			curl_setopt($curl, CURLOPT_SAFE_UPLOAD, true);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
		}

		$result['result'] = curl_exec($curl);
		$responseHeader = '';

		$result['code'] = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		if ($result['result'] === false) {
			$result['result'] = curl_error($curl);
			$result['code'] = -curl_errno($curl);
		}

		curl_close($curl);
		return $result;
	}

    //todo 
}