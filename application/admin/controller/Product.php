<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/12
 * Time: 13:09
 * Comment: 成果控制器
 */

namespace app\admin\controller;

use app\admin\response\Code;
use think\Request;
use app\admin\model\Product as ProductModel;
use app\admin\validate\Product as ProductValidate;

class Product extends BasisController {

    /* 声明成果模型 */
    protected $product_model;

    /* 声明成果验证器 */
    protected $product_validate;

    /* 声明成果分页 */
    protected $product_page;

    /* 默认构造函数 */
    public function __construct(Request $request = null) {
        parent::__construct($request);
        $this->product_model = new ProductModel();
        $this->product_validate = new ProductValidate();
        $this->product_page = config('pagination');
    }

    /* 成果列表 */
    public function listing() {

        /* 接收参数 */
    }

    /* 成果添加更新 */
    public function save() {

        /* 接收参数 */
        $id = request()->param('id');
        $name = request()->param('name');
        $description = request()->param('description');
        $detail = request()->file('detail');

        /* 移动文件 */
        if ($detail) {
            $config = [
                'ext'       => 'rar,zip'
            ];
            $info = $detail->validate($config)->move(ROOT_PATH  . 'public' . DS . 'images');
            if ($info) {
                $sub_path = str_replace('\\', '/', $info->getSaveName());
                $detail = '/images/' . $sub_path;
            } else {
                return $this->return_message(Code::INVALID, '文件格式不正确，只允许rar和zip格式');
            }
        }

        /* 验证参数 */
        $validate_data = [
            'id'            => $id,
            'name'          => $name,
            'description'   => $description,
            'detail'        => $detail
        ];

        /* 验证结果 */
        $result = $this->product_validate->scene('save')->check($validate_data);

        if (true !== $result) {
            return $this->return_message(Code::INVALID, $this->product_validate->getError());
        }

        /* 返回数据 */
        if (empty($id)) {
            $product = $this->product_model->save($validate_data);
        } else {
            if (empty($validate_data['detail'])) {
                unset($validate_data['detail']);
            }
            $product = $this->product_model->save($validate_data, ['id' => $id]);
        }

        if ($product) {
            return $this->return_message(Code::SUCCESS, '数据操作成功');
        } else {
            return $this->return_message(Code::FAILURE, '数据操作失败');
        }
    }

    /* 成果详情 */
    public function detail() {

        /* 接收参数 */
        $id = request()->param('id');

        /* 验证参数 */
        $validate_data = [
            'id'        => $id
        ];

        /* 验证结果 */
        $result = $this->product_validate->scene('detail')->check($validate_data);

        if (true !== $result) {
            return $this->return_message(Code::INVALID, $this->product_validate->getError());
        }

        /* 返回结果 */
        $product = $this->product_model->where('id', $id)->find();

        if ($product) {
            return $this->return_message(Code::SUCCESS, '获取成果详情成功', $product);
        } else {
            return $this->return_message(Code::FAILURE, '获取成果详情失败');
        }

    }

    /* 成果删除 */
    public function delete() {

        /* 接收参数 */
        $id = request()->param('id');

        /* 验证参数 */
        $validate_data = [
            'id'        => $id
        ];

        /* 验证结果 */
        $result = $this->product_validate->scene('delete')->check($validate_data);

        if (true !== $result) {
            return $this->return_message(Code::INVALID, $this->product_validate->getError());
        }

        /* 返回结果 */
        $product = $this->product_model->where('id', $id)->delete();

        if ($product) {
            return $this->return_message(Code::SUCCESS, '删除成果信息成功');
        } else {
            return $this->return_message(Code::FAILURE, '删除成果信息失败');
        }
    }

}