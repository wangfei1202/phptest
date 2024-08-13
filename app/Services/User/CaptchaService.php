<?php

namespace App\Services\User;

use Hyperf\Redis\Redis;
use Hyperf\Utils\ApplicationContext;

class CaptchaService
{
    const CODE_LENGTH = 4;  // 验证码长度固定为 4，可以根据实际需要修改
    const LINE_COUNT = 5;   // 干扰线个数
    const DOT_COUNT = 50;  // 干扰点个数
    private $image = NULL;  // 图像对象
    private $code = "";     // 验证码
    private $width;     // 图像长度
    private $height;    // 图像宽度
    // 验证码备选字符
    static $CANDIDATES = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";

    public function __construct($width = 150, $height = 40)
    {
        $this->width = $width;
        $this->height = $height;
        // 创建图像对象
        $this->image = imagecreatetruecolor($width, $height);
        // 创建验证码
        $this->generateCaptchaCode();
    }

    public function __destruct()
    {
        // 销毁图像对象
        imagedestroy($this->image);
    }

    public function paint()
    {
        // 绘制背景
        $this->paintBackground();
        // 绘制验证码
        $this->paintText();
        // 绘制干扰
        $this->paintDirty();
    }

    public function get($key)
    {
        $key = 'warehouse:login_code:' . $key;
        // 设置头部为PNG图片
        $this->paint();
        ob_start();
        // 输出到浏览器
        imagejpeg($this->image);
        ApplicationContext::getContainer()->get(Redis::class)->set($key, strtolower($this->code), ['EX' => 300]);
        return ob_get_clean();
    }

    public function code()
    {
        return $this->code;
    }

    private function paintBackground()
    {
        // 背景颜色设置为白色
        $color = imagecolorallocate($this->image, 255, 255, 255);
        // 填充背景
        imagefill($this->image, 0, 0, $color);
    }

    private function paintText()
    {
        $font = BASE_PATH . '/storage/font/captcha0.ttf';
        $y = 35;
        // 遍历验证码，一个字符一个字符地绘制
        for ($i = 0; $i < strlen($this->code); ++$i) {
            $color = imagecolorallocate($this->image, rand(0, 250), rand(0, 250), rand(0, 250));
            $x = $i * 35 + 10;
            imagettftext($this->image, 30, 0, $x, $y, $color, $font, $this->code[$i]);//设置字体大小为10
        }
    }

    private function paintDirty()
    {
        // 绘制点
        for ($i = 0; $i < self::DOT_COUNT; ++$i) {
            // 点的颜色
            $pointcolor = imagecolorallocate($this->image, rand(100, 200), rand(100, 200), rand(100, 200));
            // 画点
            imagesetpixel($this->image, rand(1, 99), rand(1, 29), $pointcolor);
        }
        // 绘制线条
        for ($i = 0; $i < self::LINE_COUNT; $i++) {
            // 线的颜色
            $linecolor = imagecolorallocate($this->image, rand(100, 200), rand(100, 200), rand(100, 200));
            // 画线
            imageline($this->image, rand(1, $this->width - 1), rand(1, 29), rand(1, 99), rand(1, 29), $linecolor);
        }
    }

    private function generateCaptchaCode()
    {
        // 从备选字符串中随机选取字符
        for ($i = 0; $i < self::CODE_LENGTH; ++$i) {
            $len = strlen(self::$CANDIDATES);
            $pos = rand(0, $len - 1);
            $ch = self::$CANDIDATES[$pos];
            $this->code .= $ch;
        }
        $this->code = strtoupper($this->code);
    }

}