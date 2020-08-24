<?php

namespace lake\admin\controller;

/**
 * 错误
 *
 * @create 2019-10-10
 * @author deatil
 */
class Error
{
    /**
     * 空操作
     *
     * @create 2019-10-10
     * @author deatil
     */
    public function index()
    {
        abort(404, '该页面不存在！');
    }
}
