<?php
namespace app\index\controller;

use app\index\response\Code;
use think\Request;
use app\index\model\Harvest as HarvestModel;
use app\index\model\Carousel as CarouselModel;
use app\index\validate\Index as IndexValidate;

class Index extends BasicController {

    /* 声明科技成果模型 */
    protected $harvest_model;

    /* 声明轮播模型 */
    protected $carousel_model;

    /* 声明科技成果分页 */
    protected $harvest_page;

    /* 声明首页验证器 */
    protected $index_validate;

    /* 声明默认构造函数 */
    public function __construct(Request $request = null) {
        parent::__construct($request);
        $this->harvest_model = new HarvestModel();
        $this->carousel_model = new CarouselModel();
        $this->harvest_page = config('pagination');
        $this->index_validate = new IndexValidate();
    }

    /* 首页返回数据 */
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
        $result = $this->index_validate->scene('index')->check($validate_data);

        if (true !== $result) {
            return $this->return_message(Code::INVALID, $this->index_validate->getError());
        }

        /* 接收参数 */
        $carousel = $this->carousel_model
            ->where('status', '=', '1')
            ->order('id', 'desc')
            ->limit(4)
            ->select();

        $harvest = $this->harvest_model
            ->where(['status' => 1, 'recommend' => 1])
            ->order('id', 'desc')
            ->paginate($page_size, false, ['page' => $jump_page]);

        $data = array_merge(['carousel' => $carousel], ['harvest' => $harvest]);

        if ($data) {
            return $this->return_message(Code::SUCCESS, '获取首页数据成功',$data);
        } else {
            return $this->return_message(Code::FAILURE, '获取首页数据失败');
        }

    }
}
