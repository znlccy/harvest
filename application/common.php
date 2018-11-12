<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
use think\captcha\Captcha;
use think\Config;
use app\index\model\Sms as SmsModel;
/**
 * 发送短信业务
 * @param $mobile
 * @return mixed
 * @throws \think\Exception
 * @throws \think\db\exception\DataNotFoundException
 * @throws \think\db\exception\ModelNotFoundException
 * @throws \think\exception\DbException
 * @throws \think\exception\PDOException
 */
function send_code($mobile)
{
    $config = Config::get('sms');

    /* 发送的URL地址 */
    $url = $config['url'];

    /* 短信模板id */
    $appid = $config['appid'];

    /* 发送的手机号 */
    $to = $mobile;

    /* 生成的验证码 */
    $random = general_random();

    /* 要发送的验证码 */
    $vars = json_encode([
        'code' => $random,
    ]);

    /* 发送的内容 */
    $project = $config['project'];

    /* 短信模板appkey */
    $signature = $config['signature'];

    /* 发送数据格式 */
    /*$header = "Content-Type:application/json";*/

    /* 封装发送的数据 */
    $data = json_encode([
        'appid'     => $appid,
        'to'        => $to,
        'project'   => $project,
        'vars'      => $vars,
        'signature' => $signature,
    ]);

    $result = http_post($url, $data);

    $code = SmsModel::where('mobile', '=', $mobile)
        ->find();

    /* 把验证码封装到数据库中 */
    $smsCode = [
        'mobile'         => $mobile,
        'code'           => $random,
        'expiration_time' => date('Y-m-d H:i:s', time() + 600),
    ];

    /*  */
    if ($code == null) {
        $sms = SmsModel::create($smsCode);
    } else {
        $sms = SmsModel::update($smsCode,['mobile' => $mobile]);
    }

    return $result;
}

function send_success_code($mobile)
{
    $config = Config::get('auth_pass_sms');

    /* 发送的URL地址 */
    $url = $config['url'];

    /* 短信模板id */
    $appid = $config['appid'];

    /* 发送的手机号 */
    $to = $mobile;

    /* 要发送的验证码 */
    $vars = json_encode([
        'yId' => $mobile,
    ]);

    /* 发送的内容 */
    $project = $config['project'];

    /* 短信模板appkey */
    $signature = $config['signature'];

    /* 发送数据格式 */
    /*$header = "Content-Type:application/json";*/

    /* 封装发送的数据 */
    $data = json_encode([
        'appid'     => $appid,
        'to'        => $to,
        'project'   => $project,
        'vars'       => $vars,
        'signature' => $signature,
    ]);

    $result = http_post($url, $data);

    if ($result) {
        return json([
            'code'      => '200',
            'message'   => '发送成功'
        ]);
    } else {
        return json([
            'code'      => '404',
            'message'   => '发送失败'
        ]);
    }
}

function send_fail_code($mobile, $reason)
{
    $config = Config::get('auth_fail_sms');

    /* 发送的URL地址 */
    $url = $config['url'];

    /* 短信模板id */
    $appid = $config['appid'];

    /* 发送的手机号 */
    $to = $mobile;

    /* 要发送的验证码 */
    $vars = json_encode([
        'yId'   => $mobile,
        'reason'=> $reason
    ]);

    /* 发送的内容 */
    $project = $config['project'];

    /* 短信模板appkey */
    $signature = $config['signature'];

    /* 发送数据格式 */
    /*$header = "Content-Type:application/json";*/

    /* 封装发送的数据 */
    $data = json_encode([
        'appid' => $appid,
        'to' => $to,
        'project' => $project,
        'vars' => $vars,
        'signature' => $signature,
    ]);

    $result = http_post($url, $data);

    if ($result) {
        return json([
            'code' => '200',
            'message' => '发送成功'
        ]);
    } else {
        return json([
            'code' => '404',
            'message' => '发送失败'
        ]);
    }
}

/**
 * 发送post请求
 * @param $url
 * @param $postData
 * @return bool|string
 */
function send_post($url, $postData)
{

    /* 截取post数据 */
    $postdata = http_build_query($postData);

    /* 封装post请求 */
    $options = array(
        'http' => array(
            'method'  => 'POST',
            'header'  => 'Content-Type:application/json',
            'content' => $postdata,
            'timeout' => 15 * 60, //超时时间，单位:秒
        ),
    );

    /* 获取上下文环境 */
    $context = stream_context_create($options);
    $result  = file_get_contents($url, false, $context);
    return $result;
}

/**
 * 发送Get请求
 * @param $url
 * @param $params
 * @return mixed
 */
function http_get($url, $params)
{
    $url = "{url}?" . http_build_query($params);
    $ch  = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}

/**
 * 发送post请求
 * @param $url
 * @param $param
 * @param $header
 * @return mixed
 */
function http_post($url, $param)
{
    /*$ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_POSTFIELDS, $param);
    curl_setopt($ch, CURLOPT_HEADER, $header);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    $result = curl_exec($ch);
    curl_close($ch);*/

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $param);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Content-Length: ' . strlen($param),
    ));

    $result = curl_exec($ch);
    if (curl_errno($ch)) {
        print curl_error($ch);
    }
    curl_close($ch);
    return $result;
}

/**
 * 生成随机验证码
 */
function general_random()
{

    /* 生成随机数 */
    $code = rand(100000, 999999);

    /* 返回随机数 */
    return $code;
}

/**
 * 验证验证码
 * @param $verifyCode
 * @return bool
 */
function check_code($code)
{
    $config  = Config::get('captcha');
    $captcha = new Captcha($config);
    if ($captcha->check($code)) {
        $result = true;
    } else {
        $result = false;
    }
    return $result;
}

/**
 * 生成token令牌
 * @param $mobile
 * @param $password
 * @return string
 */
function general_token($mobile, $password)
{
    $token = md5($mobile . $password . time());
    return $token;
}

/**
 *加密函数
 */
function passport_encrypt($txt, $key) {
    srand((double)microtime() * 1000000);
    $encrypt_key = md5(rand(0, 32000));
    $ctr = 0;
    $tmp = '';
    for($i = 0;$i < strlen($txt); $i++) {
        $ctr = $ctr == strlen($encrypt_key) ? 0 : $ctr;
        $tmp .= $encrypt_key[$ctr].($txt[$i] ^ $encrypt_key[$ctr++]);
    }
    return base64_encode(passport_key($tmp, $key));
}
/**
 *解密函数
 */
function passport_decrypt($txt, $key) {
    $txt = passport_key(base64_decode($txt), $key);
    $tmp = '';
    for($i = 0;$i < strlen($txt); $i++) {
        $md5 = $txt[$i];
        $tmp .= $txt[++$i] ^ $md5;
    }
    return $tmp;
}

function passport_key($txt, $encrypt_key) {
    $encrypt_key = md5($encrypt_key);
    $ctr = 0;
    $tmp = '';
    for($i = 0; $i < strlen($txt); $i++) {
        $ctr = $ctr == strlen($encrypt_key) ? 0 : $ctr;
        $tmp .= $txt[$i] ^ $encrypt_key[$ctr++];
    }
    return $tmp;
}
?>