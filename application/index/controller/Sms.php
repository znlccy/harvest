<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/15
 * Time: 14:22
 * Comment: 短信控制器
 */
namespace app\index\controller;

use app\index\response\Code;
use think\Cache;
use think\Controller;
use app\index\model\Sms as SmsModel;
use app\index\validate\Sms as SmsValidate;
use app\index\model\User as UserModel;
use think\Request;
use DateTime;

class Sms extends BasicController {

    /**
     * 声明短信验证码模型
     * @var
     */
    protected $sms_model;

    /**
     * 声明前台用户模型
     * @var
     */
    protected $user_model;

    /**
     * 声明短信验证码验证器
     * @var
     */
    protected $sms_validate;

    /**
     * 默认构造函数
     * Sms constructor.
     * @param Request|null $request
     */
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        //实例化变量
        $this->sms_model = new SmsModel();
        $this->user_model = new UserModel();
        $this->sms_validate = new SmsValidate();
    }

    /**
     * 短信验证码获取api接口
     */
    public function attain() {
        //获取客户端提供的数据
        $mobile = request()->param('mobile');

        //验证数据
        $validate_data = [
            'mobile'            => $mobile
        ];

        //验证结果
        $result = $this->sms_validate->scene('attain')->check($validate_data);
        if (!$result) {
            return $this->return_message(Code::INVALID, $this->sms_validate->getError());
        }

        //单个用户60秒不能连续发送
        $sms = $this->sms_model->where('mobile', $mobile)->find();
        if (time() - strtotime($sms['create_time']) < 60) {
            return $this->return_message(Code::FAILURE, '操作过于频繁, 请在一分钟后重试');
        }

        // 单个用户一天时间最多发送10条
        // 当天日期
        $today = date("Y-m-d");
        $key = $mobile . '-' . $today;
        if (Cache::has($key)) {
            if (Cache::get($key) == 10) {
                return $this->return_message(Code::FAILURE, '对不起,同一用户一天最多只能发送10次验证码');
            }
        } else {
            Cache::set($key, 0, new DateTime('+1 day 00:00:00'));
        }

        $result = json_decode(send_code($mobile), true);

        if ($result['status'] == 'success') {
            return $this->return_message(Code::SUCCESS, '发送成功');
            //当天该用户的发送次数+1
            Cache::inc($key);
        } else {
            return $this->return_message(Code::FAILURE, '发送失败');
        }
    }


}