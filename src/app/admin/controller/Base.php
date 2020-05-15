<?php

namespace app\admin\controller;

use think\facade\View;

use app\admin\boot\Jump;
use app\admin\boot\BaseController;

/**
 * 后台基础类
 *
 * @create 2019-7-15
 * @author deatil
 */
abstract class Base extends BaseController
{
    use Jump;

    /**
     * 控制器中间件
     * @var array
     */
    protected $middleware = [
        // 权限检测
        '\\app\\admin\\middleware\\AdminAuthCheck',
    ];
    
    /**
     * 当前登录账号信息
     *
     * @create 2019-10-10
     * @author deatil
     */
    protected $adminInfo;
    
    /**
     * 空操作
     *
     * @create 2019-10-10
     * @author deatil
     */
    public function _empty()
    {
        $this->error('该页面不存在！');
    }
    
    /**
     * 初始化
     *
     * @create 2019-10-10
     * @author deatil
     */
    protected function initialize()
    {
        parent::initialize();
    }
    
    /**
     * 生成查询所需要的条件,排序方式
     *
     * @create 2019-10-10
     * @author deatil
     */
    protected function buildparams()
    {
        $search_field = $this->request->param('search_field/s', '', 'trim');
        $keyword = $this->request->param('keyword/s', '', 'trim');
       
        View::assign("search_field", $search_field);
        View::assign("keyword", $keyword);

        $filter_time = $this->request->param('filter_time/s', '', 'trim');
        $filter_time_range = $this->request->param('filter_time_range/s', '', 'trim');
       
        View::assign("filter_time", $filter_time);
        View::assign("filter_time_range", $filter_time_range);

        $map = [];
        // 关键词搜索
        if ($search_field != '' && $keyword !== '') {
            $map[] = [$search_field, 'like', "%$keyword%"];
        }

        // 时间范围搜索
        if ($filter_time && $filter_time_range) {
            $filter_time_range = str_replace(' - ', ',', $filter_time_range);
            $arr = explode(',', $filter_time_range);
            !empty($arr[0]) ? $arr[0] : date("Y-m-d", strtotime("-1 day"));
            !empty($arr[1]) ? $arr[1] : date('Y-m-d', time());
            $map[] = [$filter_time, 'between time', [$arr[0] . ' 00:00:00', $arr[1] . ' 23:59:59']];
        }
        
        return $map;
    }

}
