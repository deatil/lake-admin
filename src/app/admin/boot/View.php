<?php

/**
 * 用法：
 * class index
 * {
 *     use \app\admin\boot\View;
 *     public function index(){
 *         $this->assign();
 *         return $this->fetch();
 *     }
 * }
 */
namespace app\admin\boot;

use think\facade\View as ViewFacade;

/**
 * 页面视图
 *
 * @create 2020-7-21
 * @author deatil
 */
trait View
{

    /**
     * 获取模板引擎
     * @access public
     * @param string $type 模板引擎类型
     * @return $this
     */
    public function engine($type = null)
    {
        return ViewFacade::engine($type);
    }

    /**
     * 模板变量赋值
     * @access public
     * @param string|array $name  模板变量
     * @param mixed        $value 变量值
     * @return $this
     */
    public function assign($name, $value = null)
    {
        ViewFacade::assign($name, $value);
    }

    /**
     * 视图过滤
     * @access public
     * @param Callable $filter 过滤方法或闭包
     * @return $this
     */
    public function filter($filter = null)
    {
        ViewFacade::filter($filter);
    }

    /**
     * 解析和获取模板内容 用于输出
     * @access public
     * @param string $template 模板文件名或者内容
     * @param array  $vars     模板变量
     * @return string
     * @throws \Exception
     */
    public function fetch($template = '', $vars = [])
    {
        return ViewFacade::fetch($template, $vars);
    }

    /**
     * 渲染内容输出
     * @access public
     * @param string $content 内容
     * @param array  $vars    模板变量
     * @return string
     */
    public function display($content, $vars = [])
    {
        return ViewFacade::display($content, $vars);
    }

}
