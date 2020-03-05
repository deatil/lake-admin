<?php

namespace app\admin\module\controller;

use app\admin\controller\Base;

/**
 * 插件后台
 *
 * @create 2019-7-4
 * @author deatil
 */
class AdminBase extends Base
{
	use \app\admin\module\traits\controller\Admin;
	
    protected function initialize()
    {
        parent::initialize();
    }

}
