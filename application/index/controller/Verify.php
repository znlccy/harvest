<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/15
 * Time: 14:22
 * Comment: 图形验证码控制器
 */
namespace app\index\controller;

use think\captcha\Captcha;
use think\Controller;

class Verify extends Controller {

    /**
     * 获取图形验证码api接口
     * @return \think\Response
     */
    public function attain() {
        ob_clean();
        $config = config('captcha');
        $captcha = new Captcha($config);
        return $captcha->entry();
    }

}