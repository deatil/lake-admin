<?php

namespace app\admin\module\controller;

use think\Controller;
use think\facade\Config;

class Homebase extends Controller
{
    use \app\admin\module\traits\controller\Home;
    
    // 初始化
    protected function initialize()
    {
        // 移除HTML标签
        $this->request->filter('trim,strip_tags,htmlspecialchars');
        parent::initialize();
    }
    
    // 空操作
    public function _empty()
    {
        $this->error('该页面不存在！');
    }
    
}
