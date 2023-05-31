<?php

include '../vendor/autoload.php';

use Jiosen\Lib\Captcha;
use Jiosen\Lib\Cache;
use Jiosen\Lib\Db;
use Jiosen\Lib\Http;
use Jiosen\Lib\Request;
use Jiosen\Lib\Upload;
use Jiosen\Lib\Validate;

// $captcha = new Captcha(['fontSize'=>50,'pixelNum'=>3,'bgImg'=>'D:\phpstudy_pro\WWW\captcha\bg\2.jpg','useZh'=>true,'math'=>false]);

// if(isset($_GET['check'])){
    
//     var_dump($captcha->check($_GET['check'],$id));
// }else{

//     $captcha->show($id);
// }
// new Cache(dirname(__FILE__) . '/cache/');
// $cache = new Cache();
// dump($cache->set('name111',['s'=>11]));
// dump($cache->get('name'));

// dump($cache->set('age',18));
// dump($cache->get('age',18));
// dump($cache->inc('age',20));
// dump($cache->get('age',18));
// dump($cache->dec('age',8));
// dump($cache->get('age',18));

// dump($cache->get('age'));
// dump($cache->del('age'));
// dump($cache->get('age'));

// dump(cache()->set('age',10));
// dump(cache()->get('age'));

// $mod = new Db(['dbname'=>'jtest','prefix'=>'j_']);
// dump($mod->name('user')->find());
// dump($mod->table('j_user')->find());

// $data = [
//     'uid' => rand(1000000,9999999),
//     'username' => 'dbtest',
//     'content'  => 'dbtest',
//     'create_time' => time()
// ];

// dump($mod->name('say')->insert($data));
// dump($mod->name('say')->insert($data,true));


// dump($mod->name('say')->insertAll([$data,$data,$data,$data]));
// dump($mod->name('say')->insertAll([$data,$data,$data,$data],true));


// dump($mod->name('say')->count());
// dump($mod->name('say')->field('id,content')->limit(0,10)->order('id desc')->select());


// dump(db(['dbname'=>'jtest','prefix'=>'j_'])->name('say')->count());
// dump(db(['dbname'=>'jtest','prefix'=>'j_'])->name('say')->field('id,content')->limit(0,10)->order('id desc')->select());

// try {
//     $files = Upload::file(dirname(__FILE__) . '/cache/file','music,file',['fileExt'=>'txt,mp3,webp']);
//     dump($files);
// } catch (\Exception $e) {
//     echo $e->getMessage();
// }

header("Access-Control-Allow-Origin:*");
// dump(Request::getInstance()->input,Request::getInstance()->server,Request::getInstance()->get,Request::getInstance()->post);
// dump(Request::getInstance()->server('SCRIPT_NAME'),Request::getInstance()->host(),Request::getInstance()->domain(true));
// dump(Request::getInstance()->baseUrl(true),Request::getInstance()->url(),Request::getInstance()->url(true));
// dump(Request::getInstance()->scheme());
// dump(Request::getInstance()->host(),Request::getInstance()->port());
// dump(date('Y-m-d H:i:s',Request::getInstance()->time()),Request::getInstance()->time(true));
// dump(Request::getInstance()->method(),Request::getInstance()->method(true));
// dump(Request::getInstance()->isGet(),Request::getInstance()->isPost(),Request::getInstance()->isAjax(),Request::getInstance()->server('HTTP_X_REQUESTED_WITH'));
// dump(Request::getInstance()->param('post.'),Request::getInstance()->post('aaa','0','trim'),Request::getInstance()->input('aaa','0','trim'));
// dump(Request::getInstance()->session(),Request::getInstance()->session('name'));
// dump(Request::getInstance()->session('test'),Request::getInstance()->session('test.test1.test2.test3'));
// dump(Request::getInstance()->cookie(''),Request::getInstance()->cookie('author','','trim'),Request::getInstance()->cookie('test',0,'intval'));
// dump(Request::getInstance()->file(),Request::getInstance()->file('file'),Request::getInstance()->file('filetest'));
// dump(Request::getInstance()->ip(),Request::getInstance()->isMobile());

// dump(request()->param());

// $res = Http::request('https://v1.hitokoto.cn/');

// if($res['code']==200 && $res['result'])
// {
//     $result = json_decode($res['result'],true);
//     dump($result['hitokoto']);
// }

// dump(Validate::ip('127.0.0.1','ipv6'),Validate::ip('127.0.0.1'));
// dump(Validate::email('4631458@qq.com'));
// dump(Validate::url('http://www.baidu.com'));
// dump(Validate::alpha('wwwbaiducom'),Validate::alphaNum('ww3co4m'),Validate::chs('新年好誰說的'));
// dump(Validate::mobile('13800138000'),Validate::mobile('19400138000'));
// dump(Validate::idCard('440000200001011234'));

dump(validate('mobile',123132),validate('mobile','15889932750'),validate('xx','15889932750'));


