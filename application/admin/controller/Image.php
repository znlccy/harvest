<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/14
 * Time: 13:32
 * Comment: 富文本图片上传
 */

namespace app\admin\controller;

use app\admin\response\Code;

class Image extends BasisController {

    /* 富文本图片上传 */
    public function upload() {

        /* 接收参数 */
        $picture = request()->file('picture');

        /* 移动图片 */
        if ($picture) {
            $config = [
                'ext'   => 'jpg,jpeg,png,bmp,gif'
            ];

            $info = $picture->validate($config)->move(ROOT_PATH . 'public' . DS  . 'images');

            if ($info) {
                $sub_path = str_replace('\\', '/', $info->getSaveName());
                $picture = '/images/' . $sub_path;
                return $this->return_message(Code::SUCCESS, '上传图片成功');
            } else {
                return $this->return_message(Code::INVALID, '上传图片格式不正确，只允许jpg,jpeg,png,bmp,gif');
            }
        }
    }
}