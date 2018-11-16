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
use app\admin\model\UserInformation as UserInformationModel;
use app\admin\model\User as UserModel;
use app\admin\model\Admin as AdminModel;
use app\admin\validate\Information as InformationValidate;

class Information extends BasisController {

    /* 声明信息模型 */
    protected $information_model;

    /* 声明用户模型 */
    protected $user_model;

    /* 声明管理员模型 */
    protected $admin_model;

    /* 声明用户消息模型 */
    protected $user_info_model;

    /* 声明信息验证器 */
    protected $information_validate;

    /* 声明信息分页 */
    protected $information_page;

    /* 声明默认构造函数 */
    public function __construct(Request $request = null) {
        parent::__construct($request);
        $this->information_model = new InformationModel();
        $this->user_info_model = new UserInformationModel();
        $this->user_model = new UserModel();
        $this->admin_model = new AdminModel();
        $this->information_validate = new InformationValidate();
        $this->information_page = config('pagination');
    }

    /* 消息列表 */
    public function listing() {

        /* 接收参数 */
        $id = request()->param('id');
        $status = request()->param('status');
        $title = request()->param('title');
        $publisher = request()->param('publisher');
        $create_start = request()->param('create_start');
        $create_end = request()->param('create_end');
        $update_start = request()->param('update_start');
        $update_end = request()->param('update_end');
        $publish_start = request()->param('publish_start');
        $publish_end = request()->param('publish_end');
        $page_size = request()->param('page_size', $this->information_page['PAGE_SIZE']);
        $jump_page = request()->param('jump_page' ,$this->information_page['JUMP_PAGE']);

        /* 验证参数 */
        $validate_data = [
            'id'            => $id,
            'status'        => $status,
            'title'         => $title,
            'publisher'     => $publisher,
            'create_start'  => $create_start,
            'create_end'    => $create_end,
            'update_start'  => $update_start,
            'update_end'    => $update_end,
            'publish_start' => $publish_start,
            'publish_end'   => $publish_end,
            'page_size'     => $page_size,
            'jump_page'     => $jump_page
        ];

        /* 验证结果 */
        $result = $this->information_validate->scene('listing')->check($validate_data);

        if (true !== $result) {
            return $this->return_message(Code::INVALID, $this->information_validate->getError());
        }

        /* 筛选条件 */
        $conditions = [];

        if ($id) {
            $conditions['id'] = $id;
        }

        if (is_null($status)) {
            $conditions['status'] = ['in',[0,1]];
        } else {
            switch ($status) {
                case 0:
                    $conditions['status'] = $status;
                    break;
                case 1:
                    $conditions['status'] = $status;
                    break;
                default:
                    break;
            }
        }

        if ($title) {
            $conditions['title'] = ['like', '%' . $title . '%'];
        }

        if ($publisher) {
            $conditions['publisher'] = ['like', '%' . $publisher . '%'];
        }

        if ($create_start && $create_end) {
            $conditions['create_time'] = ['between time', [$create_start, $create_end]];
        }

        if ($update_start && $update_end) {
            $conditions['update_time'] = ['between time', [$update_start, $update_end]];
        }

        if ($publish_start && $publish_end) {
            $conditions['publish_time'] = ['between time', [$publish_start, $publish_end]];
        }

        /* 返回结果 */
        $information = $this->information_model
            ->where($conditions)
            ->order('id', 'asc')
            ->paginate($page_size, false, ['page' => $jump_page]);

        if ($information) {
            return $this->return_message(Code::SUCCESS, '获取通知消息成功', $information);
        } else {
            return $this->return_message(Code::FAILURE, '获取通知消息失败');
        }
    }

    /* 消息保存添加 */
    public function save() {

        /* 接收参数 */
        $id = request()->param('id');
        $title = request()->param('title');
        $publisher = session('admin.id');
        $status = request()->param('status');
        $publish_time = date('Y-m-d H:i:s', time());
        $rich_text = request()->param('rich_text');

        /* 验证参数 */
        $validate_data = [
            'id'            => $id,
            'title'         => $title,
            'status'        => $status,
            'publisher'     => $publisher,
            'publish_time'  => $publish_time,
            'rich_text'     => $rich_text
        ];

        /* 验证结果 */
        $result = $this->information_validate->scene('save')->check($validate_data);

        if (true !== $result) {
            return $this->return_message(Code::INVALID, $this->information_validate->getError());
        }

        /* 返回结果 */
        if (!empty($id)) {
            /* 更新数据 */
            $information = $this->information_model->save($validate_data, ['id' => $id]);
        } else {
            /* 保存数据 */
            $information = $this->information_model->save($validate_data);
        }

        if ($information) {
            return $this->return_message(Code::SUCCESS, '数据操作成功');
        } else {
            return $this->return_message(Code::FAILURE, '数据操作失败');
        }
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

    /**
     * 发布人下拉列表
     */
    public function user_listing() {
        $publishers = $this->admin_model->column('nick_name', 'real_name', 'id');
        if (!empty($publishers)) {
            return json(['code' => '200', 'message' => '获取列表成功', 'data' => $publishers]);
        } else {
            return json(['code' => '404', 'message' => '获取列表失败']);
        }
    }


}