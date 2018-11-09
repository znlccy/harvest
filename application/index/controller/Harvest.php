<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/9
 * Time: 13:04
 * Comment：成果控制器
 */

namespace app\index\controller;

use app\index\response\Code;
use think\Request;
use app\index\model\Harvest as HarvestModel;
use app\index\validate\Harvest as HarvestValidate;

class Harvest extends BasicController {

    /* 声明成果模型 */
    protected $harvest_model;

    /* 声明成果验证器 */
    protected $harvest_validate;

    /* 声明成果分页 */
    protected $harvest_page;

    /* 声明默认构造函数 */
    public function __construct(Request $request = null) {
        parent::__construct($request);
        $this->harvest_model = new HarvestModel();
        $this->harvest_validate = new HarvestValidate();
        $this->harvest_page = config('pagination');
    }

    /* 声明成果列表 */
    public function index() {

        /* 接收参数 */
        $page_size = request()->param('page_size', $this->harvest_page['PAGE_SIZE']);
        $jump_page = request()->param('jump_page', $this->harvest_page['JUMP_PAGE']);

        /* 验证参数 */
        $validate_data = [
            'page_size'     => $page_size,
            'jump_page'     => $jump_page
        ];

        /* 验证结果 */
        $result = $this->harvest_validate->scene('index')->check($validate_data);

        if (true !==  $result) {
            return $this->return_message(Code::INVALID, $this->harvest_validate->getError());
        }

        /* 返回数据 */
        $harvest = $this->harvest_model
            ->order('id', 'desc')
            ->where('status', '=', '1')
            ->paginate($page_size, false, ['page' => $jump_page]);

        if ($harvest) {
            return $this->return_message(Code::SUCCESS, '获取成果列表成功', $harvest);
        } else {
            return $this->return_message(Code::FAILURE, '获取成果列表失败');
        }
    }

    /* 声明成果详情 */
    public function detail() {

        /* 接收参数 */
        $id = request()->param('id');

        /* 验证数据 */
        $validate_data = [
            'id'        => $id
        ];

        /* 验证结果 */
        $result = $this->harvest_validate->scene('detail')->check($validate_data);

        if (true !== $result) {
            return $this->return_message(Code::INVALID, $this->harvest_validate->getError());
        }

        /* 返回结果 */
        $harvest = $this->harvest_model
            ->where('id', $id)
            ->find();

        if ($harvest) {
            return $this->return_message(Code::SUCCESS, '获取成果详情成功', $harvest);
        } else {
            return $this->return_message(Code::FAILURE, '获取成果详情失败');
        }
    }
}