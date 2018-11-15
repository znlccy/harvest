<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/9
 * Time: 19:16
 * Comment: 跨域空值器
 */

namespace app\index\behavior;

use think\Request;

class Cross {
    /**
     * 响应发送
     * @param $params
     */
    public function responseSend()
    {
        $request = Request::instance();
        header("Access-Control-Allow-Credentials:true");
        header("Access-Control-Allow-Origin:" . $request->domain());//");//注意修改这里填写你的前端的域名
        header("Access-Control-Max-Age:3600");
        header("Access-Control-Allow-Headers:DNT,X-Mx-ReqToken,Keep-Alive,User-Agent,X-Requested-With,If-Modified-Since,Cache-Control,Content-Type,Authorization,SessionToken");
        header('Access-Control-Allow-Methods: GET, POST, PUT,DELETE');
        //设置头部前置刷新
        header('Cache-Control: no-cache, no-store, max-age=0, must-revalidate');
    }
}