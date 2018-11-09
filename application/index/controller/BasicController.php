<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/9
 * Time: 13:11
 * Comment: 基础控制器
 */

namespace app\index\controller;

use think\Controller;

class BasicController extends Controller {

    /* 返回信息 */
    public function return_message($code = 200, $message = '', $data = []) {

        if (is_null($data) || empty($data)) {
            return json([
                'code'      => $code,
                'message'   => $message
            ]);
        } else {
            return json([
                'code'      => $code,
                'message'   => $message,
                'data'      => $data
            ]);
        }
    }

}