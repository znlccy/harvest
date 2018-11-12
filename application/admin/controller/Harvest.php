<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/9
 * Time: 14:57
 * Comment: 成果控制器
 */

namespace app\admin\controller;

use app\admin\response\Code;
use think\Request;
use app\admin\model\Harvest as HarvestModel;
use app\admin\validate\Harvest as HarvestValidate;

class Harvest extends BasisController {

    /* 声明成果模型 */
    protected $harvest_model;

    /* 声明成果验证器 */
    protected $harvest_validate;

    /* 声明称过分页 */
    protected $harvest_page;

    /* 声明成果默认构造函数 */
    public function __construct(Request $request = null) {
        parent::__construct($request);
        $this->harvest_model = new HarvestModel();
        $this->harvest_validate = new HarvestValidate();
        $this->harvest_page = config('pagination');
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
        $page_size = request()->param('page_size', $this->harvest_page['PAGE_SIZE']);
        $jump_page = request()->param('jump_page', $this->harvest_page['JUMP_PAGE']);

        /* 验证数据 */
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
        $result = $this->harvest_validate->scene('listing')->check($validate_data);

        if (true !== $result) {
            return $this->return_message(Code::INVALID, $this->harvest_validate->getError());
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
        $harvest = $this->harvest_model
            ->where($conditions)
            ->order('id', 'asc')
            ->paginate($page_size, false, ['page' => $jump_page]);

        if ($harvest) {
            return $this->return_message(Code::SUCCESS, '获取成果列表成功', $harvest);
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
        $picture = request()->file('picture');
        $rich_text = request()->param('rich_text');

        /* 移动图片 */
        if ($picture) {
            $config = [
                'ext'       => 'jpg,gif,png,bmp,jpeg'
            ];
            $info = $picture->validate($config)->move(ROOT_PATH  . 'public' . DS . 'images');
            if ($info) {
                $sub_path = str_replace('\\', '/', $info->getSaveName());
                $picture = '/images' . $sub_path;
            } else {
                return $this->return_message(Code::INVALID, '图片格式不正确');
            }
        }

        /* 验证数据 */
        $validate_data = [
            'id'            => $id,
            'name'          => $name,
            'description'   => $description,
            'rich_text'     => $rich_text,
            'picture'       => $picture
        ];

        /* 验证结果 */
        $result = $this->harvest_validate->scene('save')->check($validate_data);

        if (true !== $result) {
            return $this->return_message(Code::INVALID, $this->harvest_validate->getError());
        }

        /* 返回结果 */
        if (empty($id)) {
            $harvest = $this->harvest_model->save($validate_data);
        } else {
            if (empty($validate_data['picture'])) {
                unset($validate_data['picture']);
            }
            $harvest = $this->harvest_model->save($validate_data, ['id' => $id]);
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
        $result = $this->harvest_validate->scene('detail')->check($validate_data);

        if (true !== $result) {
            return $this->return_message(Code::INVALID, $this->harvest_validate->getError());
        }

        /* 返回结果 */
        $harvest = $this->harvest_model->where('id', $id)->find();

        if ($harvest) {
            return $this->return_message(Code::SUCCESS, '获取成果详情成功', $harvest);
        } else {
            return $this->return_message(Code::FAILURE, '获取成果详情失败');
        }

        /* 上一页数据 */
        /*$prev = Db::table('tb_review')
            ->field('richtext,recommend',true)
            ->where('id', '<', $id)
            ->order('id desc')
            ->find();*/

        /* 下一页数据 */
        /*$next = Db::table('tb_review')
            ->field('richtext,recommend',true)
            ->where('id', '>', $id)
            ->order('id asc')
            ->find();*/

        /* 最新活动回顾 */
        /*$lastreview = Db::table('tb_review')
            ->field('richtext,recommend',true)
            ->where('id', '<>', $id)
            ->order('recommend', 'desc')
            ->order('id', 'desc')
            ->limit(10)
            ->select();

        $data = array_merge(['prev' => $prev], ['next' => $next], ['detail' => $return_review], ['lastreview' => $lastreview]);*/
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
        $result = $this->harvest_validate->scene('delete')->check($validate_data);

        if (true !== $result) {
            return $this->return_message(Code::INVALID, $this->harvest_validate->getError());
        }

        /* 返回结果 */
        $harvest = $this->harvest_model->where('id', $id)->delete();

        if ($harvest) {
            return $this->return_message(Code::SUCCESS, '删除成果成功');
        } else {
            return $this->return_message(Code::FAILURE, '删除成果失败');
        }
    }

}