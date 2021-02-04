<?php

namespace Lake\Admin\Http\Traits;

use think\facade\View as ViewFacade;

/**
 * 页面视图
 *
 * @create 2020-7-21
 * @author deatil
 *
 * 用法：
 * class index
 * {
 *     use \Lake\Admin\Http\Traits\View;
 *     public function index(){
 *         $this->assign();
 *         return $this->fetch();
 *     }
 * }
 */
trait View
{

    /**
     * 获取模板引擎
     * @access protected
     * @param string $type 模板引擎类型
     * @return think\facade\View
     *
     * @create 2020-7-24
     * @author deatil
     */
    protected function engine($type = null)
    {
        return ViewFacade::engine($type);
    }

    /**
     * 模板变量赋值
     * @access protected
     * @param string|array $name  模板变量
     * @param mixed        $value 变量值
     * @return $this
     *
     * @create 2020-7-24
     * @author deatil
     */
    protected function assign($name, $value = null)
    {
        ViewFacade::assign($name, $value);
        return $this;
    }

    /**
     * 视图过滤
     * @access protected
     * @param Callable $filter 过滤方法或闭包
     * @return $this
     *
     * @create 2020-7-24
     * @author deatil
     */
    protected function filter($filter = null)
    {
        ViewFacade::filter($filter);
        return $this;
    }

    /**
     * 解析和获取模板内容 用于输出
     * @access protected
     * @param string $template 模板文件名或者内容
     * @param array  $vars     模板变量
     * @return string
     * @throws \Exception
     *
     * @create 2020-7-24
     * @author deatil
     */
    protected function fetch($template = '', $vars = [])
    {
        return ViewFacade::fetch($template, $vars);
    }

    /**
     * 渲染内容输出
     * @access protected
     * @param string $content 内容
     * @param array  $vars    模板变量
     * @return string
     *
     * @create 2020-7-24
     * @author deatil
     */
    protected function display($content, $vars = [])
    {
        return ViewFacade::display($content, $vars);
    }

    /**
     * 返回json
     * @access protected
     * @param mixed $data    返回的数据
     * @param int   $code    状态码
     * @param array $header  头部
     * @param array $options 参数
     * @return \think\response\Json
     *
     * @create 2020-7-24
     * @author deatil
     */
    protected function json($data = [], $code = 200, $header = [], $options = [])
    {
        return json($data, $code, $header, $options);
    }

    /**
     * 返回jsonp
     * @param mixed $data    返回的数据
     * @param int   $code    状态码
     * @param array $header  头部
     * @param array $options 参数
     * @return \think\response\Jsonp
     *
     * @create 2020-7-24
     * @author deatil
     */
    protected function jsonp($data = [], $code = 200, $header = [], $options = [])
    {
        return jsonp($data, $code, $header, $options);
    }

}
