<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/7
 * Time: 14:34
 * Comment: 轮播控制器
 */

namespace app\admin\controller;

use app\admin\response\Code;
use think\Request;
use app\admin\model\Carousel as CarouselModel;
use app\admin\validate\Carousel as CarouselValidate;

class Carousel extends BasisController {

    /* 声明轮播模型 */
    protected $carousel_model;

    /* 声明轮播验证器 */
    protected $carousel_validate;

    /* 声明轮播分页器 */
    protected $carousel_page;

    /* 声明默认构造函数 */
    public function __construct(Request $request = null) {
        parent::__construct($request);
        $this->carousel_model = new CarouselModel();
        $this->carousel_validate = new CarouselValidate();
        $this->carousel_page = config('pagination');
    }

    /* 轮播列表 */
    public function listing() {

        /* 接收参数 */
        $id = request()->param('id');
        $title = request()->param('title');
        $url = request()->param('url');
        $sort = request()->param('sort');
        $status = request()->param('status');
        $description = request()->param('description');
        $create_start = request()->param('create_start');
        $create_end = request()->param('create_end');
        $update_start = request()->param('update_start');
        $update_end = request()->param('update_end');
        $publish_start = request()->param('publish_start');
        $publish_end = request()->param('publish_end');
        $page_size = request()->param('page_size', $this->carousel_page['PAGE_SIZE']);
        $jump_page = request()->param('jump_page', $this->carousel_page['JUMP_PAGE']);

        /* 验证参数 */
        $validate_data = [
            'id'            => $id,
            'title'         => $title,
            'url'           => $url,
            'sort'          => $sort,
            'status'        => $status,
            'description'   => $description,
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
        $result = $this->carousel_validate->scene('listing')->check($validate_data);

        if (true !== $result) {
            return $this->return_message(Code::INVALID, $this->carousel_validate->getError());
        }

        /* 筛选条件 */
        $conditions = [];

        if ($id) {
            $conditions['id '] = $id;
        }

        if ($title) {
            $conditions['title'] = ['like', '%' . $title . '%'];
        }

        if ($sort) {
            $conditions['sort'] = $sort;
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

        if ($description) {
            $conditions['description'] = ['like', '%' . $description . '%'];
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
        $carousel = $this->carousel_model
            ->where($conditions)
            ->order('id', 'asc')
            ->paginate($page_size, false, ['page' => $jump_page]);

        if ($carousel) {
            return $this->return_message(Code::SUCCESS, '获取轮播列表成功', $carousel);
        } else {
            return $this->return_message(Code::FAILURE, '获取轮播列表失败');
        }
    }

    /* 轮播添加更新 */
    public function save() {

        /* 接收参数 */
        $id = request()->param('id');
        $title = request()->param('title');
        $url = request()->param('url');
        $sort = request()->param('sort');
        $picture = request()->file('picture');
        $status = request()->param('status');
        $description = request()->param('description');
        $publish_time = date('Y-m-d H:i:s',time());

        /* 验证参数 */
        $validate_data = [
            'id'            => $id,
            'title'         => $title,
            'url'           => $url,
            'sort'          => $sort,
            'picture'       => $picture,
            'status'        => $status,
            'description'   => $description,
            'publish_time'  => $publish_time
        ];

        /* 移动图片 */
        if ($picture) {
            $config = [
                'ext'  => 'jpg,gif,png,bmp,jpeg'
            ];
            $info = $picture->validate($config)->move(ROOT_PATH . 'public' . DS . 'images');
            if ($info) {
                $sub_path = str_replace('\\', '/', $info->getSaveName());
                $picture = '/images/' . $sub_path;
            } else {
                return $this->return_message(Code::INVALID, '文件图片格式不对');
            }
        }

        /* 验证结果 */
        $result = $this->carousel_validate->scene('save')->check($validate_data);

        if (true !== $result) {
            return $this->return_message(Code::INVALID, $this->carousel_validate->getError());
        }

        /* 返回结果 */
        if (empty($id)) {
            $carousel = $this->carousel_model->save($validate_data);
        } else {
            if (empty($validate_data['picture'])) {
                unset($validate_data['picture']);
            }
            $carousel = $this->carousel_model->save($validate_data, ['id' => $id]);
        }

        if ($carousel) {
            return $this->return_message(Code::SUCCESS, '数据操作成功');
        } else {
            return $this->return_message(Code::FAILURE, '数据操作失败');
        }
    }

    /* 轮播详情 */
    public function detail() {

        /* 接收参数 */
        $id = request()->param('id');

        /* 验证参数 */
        $validate_data = [
            'id'        => $id
        ];

        /* 验证结果 */
        $result = $this->carousel_validate->scene('detail')->check($validate_data);

        if (true !== $result) {
            return $this->return_message(Code::INVALID, $this->carousel_validate->getError());
        }

        /* 返回结果 */
        $carousel = $this->carousel_model->where('id', $id)->find();

        if ($carousel) {
            return $this->return_message(Code::SUCCESS, '获取轮播详情成功',$carousel);
        } else {
            return $this->return_message(Code::FAILURE, '获取轮播详情失败');
        }
    }

    /* 轮播删除 */
    public function delete() {

        /* 接收参数 */
        $id = request()->param('id');

        /* 验证参数 */
        $validate_data = [
            'id'        => $id
        ];

        /* 验证结果 */
        $result = $this->carousel_validate->scene('delete')->check($validate_data);

        if (true !== $result) {
            return $this->return_message(Code::INVALID, $this->carousel_validate->getError());
        }

        /* 返回结果 */
        $carousel = $this->carousel_model->where('id', $id)->delete();

        if ($carousel) {
            return $this->return_message(Code::SUCCESS, '删除轮播成功');
        } else {
            return $this->return_message(Code::FAILURE,'删除轮播失败');
        }
    }

}