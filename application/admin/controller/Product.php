<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/12
 * Time: 13:09
 * Comment: 成果控制器
 */

namespace app\admin\controller;

use app\admin\model\UserProduct;
use app\admin\response\Code;
use think\Request;
use app\admin\model\Product as ProductModel;
use app\admin\model\User as UserModel;
use app\admin\model\UserProduct as UserProductModel;
use app\admin\validate\Product as ProductValidate;

class Product extends BasisController {

    /* 声明成果模型 */
    protected $product_model;

    /* 声明用户模型 */
    protected $user_model;

    /* 声明用户产品模型 */
    protected $user_product_model;

    /* 声明成果验证器 */
    protected $product_validate;

    /* 声明成果分页 */
    protected $product_page;

    /* 默认构造函数 */
    public function __construct(Request $request = null) {
        parent::__construct($request);
        $this->product_model = new ProductModel();
        $this->user_model = new UserModel();
        $this->user_product_model = new UserProductModel();
        $this->product_validate = new ProductValidate();
        $this->product_page = config('pagination');
    }

    /* 成果列表 */
    public function listing() {

        /* 接收参数 */
        $id = request()->param('id');
        $name = request()->param('name');
        $description = request()->param('description');
        $create_start = request()->param('create_start');
        $create_end = request()->param('create_end');
        $update_start = request()->param('update_start');
        $update_end = request()->param('update_end');
        $page_size = request()->param('page_size', $this->product_page['PAGE_SIZE']);
        $jump_page = request()->param('jump_page', $this->product_page['JUMP_PAGE']);

        /* 验证参数 */
        $validate_data = [
            'id'            => $id,
            'name'          => $name,
            'description'   => $description,
            'create_start'  => $create_start,
            'create_end'    => $create_end,
            'update_start'  => $update_start,
            'update_end'    => $update_end,
            'page_size'     => $page_size,
            'jump_page'     => $jump_page
        ];

        /* 验证结果 */
        $result = $this->product_validate->scene('listing')->check($validate_data);

        if (true !== $result) {
            return $this->return_message(Code::INVALID, $this->product_validate->getError());
        }

        /* 筛选条件 */
        $conditions = [];

        if ($id) {
            $conditions['id'] = $id;
        }

        if ($name) {
            $conditions['name'] = ['like', '%' . $name . '%'];
        }

        if ($description) {
            $conditions['description'] = ['like', '%' . $description . '%'];
        }

        if ($create_start && $create_end) {
            $conditions['create_time'] = ['between time', [$create_start, $create_end]];
        }

        if ($update_start && $update_end) {
            $conditions['update_time'] = ['between time', [$update_start, $update_end]];
        }

        /* 返回结果 */
        $product = $this->product_model
            ->where($conditions)
            ->order('id', 'asc')
            ->paginate($page_size, false, ['page' => $jump_page]);

        if ($product) {
            return $this->return_message(Code::SUCCESS, '获取成果列表成功', $product);
        } else {
            return $this->return_message(Code::FAILURE, '获取成果列表失败');
        }

    }

    /* 成果添加更新 */
    public function save() {

        /* 接收参数 */
        $id = request()->param('id');
        $name = request()->param('name');
        $description = request()->param('description');
        $recommend = request()->param('recommend');
        $detail = request()->file('detail');
        $status = request()->param('status', 0);

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
            'status'        => $status,
            'recommend'     => $recommend,
            'detail'        => $detail
        ];

        /* 验证结果 */
        $result = $this->product_validate->scene('save')->check($validate_data);

        if (true !== $result) {
            return $this->return_message(Code::INVALID, $this->product_validate->getError());
        }


        /* 返回数据 */
        if (empty($id)) {
            if ($validate_data['status'] !== 0) {
                $validate_data['status'] = 0;
            }
            $product = $this->product_model->save($validate_data);
        } else {
            if (empty($validate_data['detail'])) {
                unset($validate_data['detail']);
            }
            if ($validate_data['status'] !== 0) {
                $validate_data['status'] = 0;
            }
            $validate_data['update_time'] = date('Y-m-d H:i:s',time());
            $product = $this->product_model->where('id', $id)->update($validate_data);
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

    /* 成果审核 */
    public function auditor() {

        /* 接收参数 */
        $id = request()->param('id');
        $status = request()->param('status');

        /* 验证参数 */
        $validate_data = [
            'id'        => $id,
            'status'    => $status
        ];

        /* 验证结果 */
        $result = $this->product_validate->scene('auditor')->check($validate_data);

        if (true !== $result) {
            return $this->return_message(Code::INVALID, $this->product_validate->getError());
        }

        /* 返回数据 */
        $product = $this->product_model->where('id', $id)->find();
        if (empty($product)) {
            return $this->return_message(Code::FAILURE, '产品不存在');
        } else {
            /* 此处状态为1,2 */
            if ($status == 0) {
                return $this->return_message(Code::FORBIDDEN, '审核状态错误');
            } else {
                $auditing = $this->product_model->where('id', '=', $id)->update(['status' => $status]);

                if ($auditing) {

                    if ($status == 1) {
                        return $this->return_message(Code::SUCCESS, '审核通过成功');
                    }
                    if ($status == 2) {
                        return $this->return_message(Code::SUCCESS, '审核拒绝成功');
                    }
                } else {
                    return $this->return_message(Code::FAILURE, '已经审核了');
                }
            }
        }

    }

    /* 成果分配 */
    public function allocation() {

        /* 接收参数 */
        $pid = request()->param('pid');
        $uid = request()->param('uid');

        /* 验证数据 */
        $validate_data = [
            'pid'       => $pid,
            'uid'       => $uid
        ];

        /* 验证结果 */
        $result = $this->product_validate->scene('allocation')->check($validate_data);

        if (true !== $result) {
            return $this->return_message(Code::INVALID, $this->product_validate->getError());
        }

        /* 返回数据 */
        $user_product = $this->user_product_model->where(['user_id' => $uid, 'product_id' => $pid])->find();

        if ($user_product) {
            return $this->return_message(Code::INVALID, '该产品已经分配给该用户了');
        } else {
            $user = $this->user_model->where('id', $uid)->find();
            if (is_null($user) || empty($user)) {
                return $this->return_message(Code::FAILURE, '不存在该用户');
            }

            $product = $this->product_model->where('id', $pid)->find();
            if (is_null($product) || empty($product)) {
                return $this->return_message(Code::FAILURE, '不存在该产品');
            }

            $distribute = $this->user_product_model->save(['user_id' => $uid, 'product_id' => $pid]);

            if ($distribute) {
                return $this->return_message(Code::SUCCESS, '分配成果成功');
            } else {
                return $this->return_message(Code::FAILURE, '分配成果失败');
            }
        }
    }

    /* 用户下拉列表 */
    public function user_listing() {

        /* 返回数据 */
        $user = $this->user_model
            ->order('id', 'asc')
            ->where('status', '=', '1')
            ->select();

        if ($user) {
            return $this->return_message(Code::SUCCESS, '获取用户下拉列表成功', $user);
        } else {
            return $this->return_message(Code::FAILURE, '获取用户列表失败');
        }
    }
}