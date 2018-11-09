<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/9
 * Time: 15:45
 * Comment: 消息控制器
 */

namespace app\admin\controller;

use app\admin\response\Code;
use think\Request;
use app\admin\model\Information as InformationModel;
use app\admin\validate\Information as InformationValidate;

class Information extends BasisController {

    /* 声明信息模型 */
    protected $information_model;

    /* 声明信息验证器 */
    protected $information_validate;

    /* 声明信息分页 */
    protected $information_page;

    /* 声明默认构造函数 */
    public function __construct(Request $request = null) {
        parent::__construct($request);
        $this->information_model = new InformationModel();
        $this->information_validate = new InformationValidate();
        $this->information_page = config('pagination');
    }

    /* 消息列表 */
    public function listing() {

        /* 接收参数 */
        $id = request()->param('id');
    }

    /* 消息保存添加 */
    public function save() {

    }

    /* 消息详情 */
    public function detail() {

        /* 接收参数 */
        $id = request()->param('id');

        /* 验证参数 */
        $validate_data = [
            'id'        => $id
        ];

        /* 验证结果 */
        $result = $this->information_validate->scene('detail')->check($validate_data);

        if (true !== $result) {
            return $this->return_message(Code::INVALID, $this->information_validate->getError());
        }

        /* 返回结果 */
        $information = $this->information_model->where('id', $id)->find();

        if ($information) {
            return $this->return_message(Code::SUCCESS, '获取消息详情成功', $information);
        } else {
            return $this->return_message(Code::FAILURE, '获取消息详情失败');
        }
    }

    /* 消息删除 */
    public function delete() {

        /* 接收参数 */
        $id = request()->param('id');

        /* 验证参数 */
        $validate_data = [
            'id'        => $id
        ];

        /* 验证结果 */
        $result = $this->information_validate->scene('delete')->check($validate_data);

        if (true !== $result) {
            return $this->return_message(Code::INVALID, $this->information_validate->getError());
        }

        /* 返回结果 */
        $information = $this->information_model->where('id', $id)->delete();

        if ($information) {
            return $this->return_message(Code::SUCCESS, '删除消息成功');
        } else {
            return $this->return_message(Code::FAILURE, '删除消息失败');
        }
    }


}