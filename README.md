## 安装

```php
composer require jiosen/lib
```

## 使用

```php
include './vendor/autoload.php';


use Jiosen\Lib\Captcha;
use Jiosen\Lib\Cache;
use Jiosen\Lib\Db;
use Jiosen\Lib\Http;
use Jiosen\Lib\Request;
use Jiosen\Lib\Upload;
use Jiosen\Lib\Validate;

//验证码
$captcha = new Captcha();
$captcha->show($id);


//缓存
$cahce = Cache(dirname(__FILE__) . '/cache/');
dump($cache->set('name111',['s'=>11]));
dump($cache->get('name'));
dump(cache()->set('age',10));

//数据库
$mod = new Db(['dbname'=>'jtest','prefix'=>'j_']);
dump($mod->name('tablename')->find());
dump($mod->table('tablename')->order('id desc')->limit(10)->select());

//上传文件

try {
    $files = Upload::file(dirname(__FILE__) . '/cache/file','music,file',['fileExt'=>'txt,mp3,webp']);
    dump($files);
} catch (\Exception $e) {
    echo $e->getMessage();
}

//请求

dump(Request::getInstance()->param(),Request::getInstance()->isMobile());

dump(request()->param('id',0,'intval'));

//验证

dump(Validate::ip('127.0.0.1','ipv6'),Validate::ip('127.0.0.1'));
dump(Validate::email('4631458@qq.com'));


更多例子看 test/index.php
```