<?php
/*
* @Descripttion: 通用验证码类
* @Author: jiosen <4631458@qq.com>
* @Date: 2023-05-26 14:34:30
*/

namespace Jiosen\Lib;

class Captcha
{
    //字符串
    private $codeSet = '2345678abcdefhijkmnpqrstuvwxyzABCDEFGHJKLMNPQRTUVWXY';

    //中文字符串 千字文
    private $zhSet = '天地玄黄宇宙洪荒日月盈昃辰宿列张寒来暑往秋收冬藏闰余成岁律吕调阳云腾致雨露结为霜金生丽水玉出昆冈剑号巨阙珠称夜光果珍李柰菜重芥姜海咸河淡鳞潜羽翔龙师火帝鸟官人皇始制文字乃服衣裳推位让国有虞陶唐吊民伐罪周发殷汤坐朝问道垂拱平章爱育黎首臣伏戎羌遐迩一体率宾归王鸣凤在竹白驹食场化被草木赖及万方盖此身发四大五常恭惟鞠养岂敢毁伤女慕贞洁男效才良知过必改得能莫忘罔谈彼短靡恃己长信使可覆器欲难量墨悲丝染诗赞羔羊景行维贤克念作圣德建名立形端表正空谷传声虚堂习听祸因恶积福缘善庆尺璧非宝寸阴是竞资父事君曰严与敬孝当竭力忠则尽命临深履薄夙兴温凊似兰斯馨如松之盛川流不息渊澄取映容止若思言辞安定笃初诚美慎终宜令荣业所基籍甚无竟学优登仕摄职从政存以甘棠去而益咏乐殊贵贱礼别尊卑上和下睦夫唱妇随外受傅训入奉母仪诸姑伯叔犹子比儿孔怀兄弟同气连枝交友投分切磨箴规仁慈隐恻造次弗离节义廉退颠沛匪亏性静情逸心动神疲守真志满逐物意移坚持雅操好爵自縻都邑华夏东西二京背邙面洛浮渭据泾宫殿盘郁楼观飞惊图写禽兽画彩仙灵丙舍旁启甲帐对楹肆筵设席鼓瑟吹笙升阶纳陛弁转疑星右通广内左达承明既集坟典亦聚群英杜稿钟隶漆书壁经府罗将相路侠槐卿户封八县家给千兵高冠陪辇驱毂振缨世禄侈富车驾肥轻策功茂实勒碑刻铭磻溪伊尹佐时阿衡奄宅曲阜微旦孰营桓公匡合济弱扶倾绮回汉惠说感武丁俊义密勿多士实宁晋楚更霸赵魏困横假途灭虢践土会盟何遵约法韩弊烦刑起翦颇牧用军最精宣威沙漠驰誉丹青九州禹迹百郡秦并岳宗泰岱禅主云亭雁门紫塞鸡田赤城昆池碣石钜野洞庭旷远绵邈岩岫杳冥治本于农务兹稼穑俶载南亩我艺黍稷税熟贡新劝赏黜陟孟轲敦素史鱼秉直庶几中庸劳谦谨敕聆音察理鉴貌辨色贻厥嘉猷勉其祗植省躬讥诫宠增抗极殆辱近耻林皋幸即两疏见机解组谁逼索居闲处沉默寂寥求古寻论散虑逍遥欣奏累遣戚谢欢招渠荷的历园莽抽条枇杷晚翠梧桐蚤凋陈根委翳落叶飘摇游鹍独运凌摩绛霄耽读玩市寓目囊箱易輶攸畏属耳垣墙具膳餐饭适口充肠饱饫烹宰饥厌糟糠亲戚故旧老少异粮妾御绩纺侍巾帷房纨扇圆洁银烛炜煌昼眠夕寐蓝笋象床弦歌酒宴接杯举觞矫手顿足悦豫且康嫡后嗣续祭祀烝尝稽颡再拜悚惧恐惶笺牒简要顾答审详骸垢想浴执热愿凉驴骡犊特骇跃超骧诛斩贼盗捕获叛亡布射僚丸嵇琴阮啸恬笔伦纸钧巧任钓释纷利俗并皆佳妙毛施淑姿工颦妍笑年矢每催曦晖朗曜璇玑悬斡晦魄环照指薪修祜永绥吉劭矩步引领俯仰廊庙束带矜庄徘徊瞻眺孤陋寡闻愚蒙等诮谓语助者焉哉乎也';

    //默认配置
    private $config = [

        'width' => 0,
        'height'=> 0,
        'useZh' => false,
        'fontSize' => 25,
        'length' => 4,
        'fontttf'=> '',
        'bgColor' => [0, 153, 0],
        'bgImg' => '',
        'math' => false,
        'noiseNum' => 25,
        'pixelNum' => 10,
        'expire' => 1000
    ];

    // im
    private $image;

    /**
     * 构造函数
     * @param array $config 配置
     */
    public function __construct($config=[])
    {
        session_start();
        $this->init($config);
    }

    /**
     * 显示验证码
     * @param string $id 缓存后缀
     * @return Response
     */
    public function show($id='')
    {
        
        $this->create($id);

        header("Content-Type: image/png"); 

        imagepng($this->image); 
        imagedestroy($this->image); 
        
    }

    /**
     * 初始化
     * @param array $config 配置
     */
    private function init($config)
    {
        $this->config = array_merge($this->config,$config);

        if($this->config['math']){ 
            $this->config['length'] = 5;
        }

        if(!$this->config['width']){
            $this->config['width'] = $this->config['length'] * $this->config['fontSize'] * 1.5;
        }

        if(!$this->config['height']){
            $this->config['height'] = $this->config['fontSize'] * 2.5;
        }

        if(!is_array($this->config['bgColor']) || count($this->config['bgColor']) != 3)
        {
            $this->config['bgColor'] = [0, 153, 0];
        }
        
        if($this->config['fontttf']=='' || !is_file($this->config['fontttf']))
        {
            $path = dirname(__FILE__) . '/assets/font/';
            $dir  = dir($path);

            $ttfs = [];
            while (false !== ($file = $dir->read())) {
                if (substr($file, -4) === '.ttf' || substr($file, -4) === '.otf') {
                    $ttfs[] = $file;
                }
            }
            $dir->close();

            $this->config['fontttf'] = $path . $ttfs[array_rand($ttfs)];
        }
    }

    /**
     * 创建验证码
     * @param string $id 缓存后缀
     */
    private function create($id='')
    {
        $code = '';

        if ($this->config['math']) {
            
            $x   = random_int(10, 30);
            $y   = random_int(1, 9);

            $code = "{$x} + {$y} = ";
            $re = $x + $y;

        } else {
            if ($this->config['useZh']) {
                $characters = preg_split('/(?<!^)(?!$)/u', $this->zhSet);
            } else {
                $characters = str_split($this->codeSet);
            }

            for ($i = 0; $i < $this->config['length']; $i++) {
                $code .= $characters[random_int(0, count($characters) - 1)];
            }

            $re = mb_strtolower($code, 'UTF-8');
        }

        $_SESSION['captcha'.$id] = ['code'=>$re,'time'=>time()+$this->config['expire']];

        $this->image = imagecreate($this->config['width'], $this->config['height']);

        // 首次调用为设置背景颜色
        imagecolorallocate($this->image, $this->config['bgColor'][0], $this->config['bgColor'][1], $this->config['bgColor'][2]); 

        //背景
        if($this->config['bgImg'])
        {
            $this->bg();
        }

        //是否有背景图

        //绘制验证码
        $this->text($code);

        //嘈杂点
        if($this->config['noiseNum'])
        {
            $this->noise();
        }
        
        //干扰线
        if($this->config['pixelNum'])
        {
            $this->pixel();
        }

    }

    /**
     * 验证
     * @param string $code 验证码
     * @param string $id 缓存后缀
     * @return bool
     */
    public function check($code,$id='')
    {
        $session = $_SESSION['captcha'.$id];

        $res =  false;

        if(!empty($session) && $session['time'] >= time() && $session['code'] == $code){

            $res =  true;
        }

        unset($_SESSION['captcha'.$id]);

        return $res;

    }

    /**
     * 绘制验证码文字
     * @param string $code 验证码
     * @return Captcha
     */
    private function text($code)
    {
        $text = $this->config['useZh'] ? preg_split('/(?<!^)(?!$)/u', $code) : str_split($code);

        foreach($text as $key => $value)
        {
            $fontColor = imagecolorallocate($this->image, mt_rand(150, 225), mt_rand(150, 225), mt_rand(150, 225));

            $x = ($key+1) * $this->config['fontSize'];
            $y = $this->config['fontSize'] + random_int(10,20);
            $angle = $this->config['math']?0:random_int(-50,50);
            
            imagettftext($this->image, $this->config['fontSize'],$angle, $x, $y, $fontColor, $this->config['fontttf'], $value);
        }

        return $this;
    }

    /**
     * 绘制嘈杂点
     * @return Captcha
     */
    private function noise()
    {
        
        $codeSet = str_split('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ');

        for ($i = 0; $i < intval($this->config['noiseNum']); $i++) {
            //杂点颜色
            $noiseColor = imagecolorallocate($this->image, mt_rand(150, 225), mt_rand(150, 225), mt_rand(150, 225));
            imagestring($this->image, 4, mt_rand(0, $this->config['width']), mt_rand(0, $this->config['height']), $codeSet[mt_rand(0, count($codeSet) - 1)], $noiseColor);
        }

        return $this;
    }

    /**
     * 绘制干扰线
     * @return Captcha
     */
    private function pixel()
    {
        $arr = [];
        for ($i = 0; $i < intval($this->config['pixelNum']); $i++) {
            $x = random_int(-10, $this->config['width']+10);
            $y = random_int(-10, $this->config['height']+10);
            $color = imagecolorallocate($this->image, mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255)); // 随机颜色
            imagesetpixel($this->image, $x, $y, $color); // 在画布上绘制点
            if($arr){
                imageline($this->image, $x, $y, $arr[0], $arr[1], $color); // 在画布上连接点
            }
            $arr = [$x,$y];
        }

        return $this;
    }

    /**
     * 设置背景
     * @return Captcha
     */
    private function bg()
    {

        if($this->config['bgImg'] && file_exists($this->config['bgImg']))
        {
            list($width, $height) = @getimagesize($this->config['bgImg']);
            $bgImage = @imagecreatefromjpeg($this->config['bgImg']);
            @imagecopyresampled($this->image, $bgImage, 0, 0, 0, 0, $this->config['width'], $this->config['height'], $width, $height);
            @imagedestroy($bgImage);
        }

        return $this;
        
    }

}