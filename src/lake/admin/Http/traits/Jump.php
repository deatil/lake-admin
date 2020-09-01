<?php

namespace Lake\Admin\Http\Traits;

use think\exception\HttpResponseException;
use think\Response;

/**
 * 页面跳转
 *
 * @create 2019-7-7
 * @author deatil
 *
 * 用法：
 * class index
 * {
 *     use \Lake\Admin\Http\Traits\Jump;
 *     public function index(){
 *         $this->error();
 *         $this->redirect();
 *     }
 * }
 */
trait Jump
{

    /**
     * 操作成功跳转的快捷方法
     * @access protected
     * @param  mixed     $msg 提示信息
     * @param  string    $url 跳转的URL地址
     * @param  mixed     $data 返回的数据
     * @param  integer   $wait 跳转等待时间
     * @param  array     $header 发送的Header信息
     * @return void
     */
    protected function success(
        $msg = '', 
        $url = null, 
        $data = '', 
        $wait = 3, 
        array $header = []
    ) {
        if (is_null($url) && isset($_SERVER["HTTP_REFERER"])) {
            $url = $_SERVER["HTTP_REFERER"];
        } elseif ($url) {
            $url = (strpos($url, '://') || 0 === strpos($url, '/')) ? $url : url($url);
        }

        $result = [
            'code' => 1,
            'msg'  => $msg,
            'data' => $data,
            'url'  => (string) $url,
            'wait' => $wait,
        ];

        $type = $this->getResponseType();
        if ($type == 'html'){
            $response = view(app('config')->get('app.dispatch_success_tmpl'), $result);
        } else if ($type == 'json') {
            $response = json($result);
        }

        throw new HttpResponseException($response);
    }

    /**
     * 操作错误跳转的快捷方法
     * @access protected
     * @param  mixed     $msg 提示信息
     * @param  string    $url 跳转的URL地址
     * @param  mixed     $data 返回的数据
     * @param  integer   $wait 跳转等待时间
     * @param  array     $header 发送的Header信息
     * @return void
     */
    protected function error(
        $msg = '', 
        $url = null, 
        $data = '', 
        $wait = 3, 
        array $header = []
    ) {
        $type = $this->getResponseType();
        if (is_null($url)) {
            $url = request()->isAjax() ? '' : 'javascript:history.back(-1);';
        } elseif ($url) {
            $url = (strpos($url, '://') || 0 === strpos($url, '/')) ? $url : url($url);
        }

        $result = [
            'code' => 0,
            'msg'  => $msg,
            'data' => $data,
            'url'  => (string) $url,
            'wait' => $wait,
        ];

        $type = $this->getResponseType();
        if ($type == 'html'){
            $response = view(app('config')->get('app.dispatch_error_tmpl'), $result);
        } else if ($type == 'json') {
            $response = json($result);
        }

        throw new HttpResponseException($response);
    }

    /**
     * 返回封装后的API数据到客户端
     * @access protected
     * @param  mixed     $data 要返回的数据
     * @param  integer   $code 返回的code
     * @param  mixed     $msg 提示信息
     * @param  string    $type 返回数据格式
     * @param  array     $header 发送的Header信息
     * @return void
     */
    protected function result(
        $data, 
        $code = 0, 
        $msg = '', 
        $type = '', 
        array $header = []
    ) {
        $result = [
            'code' => $code,
            'msg'  => $msg,
            'data' => $data,
            'time' => time(),
        ];

        $type     = $type ?: $this->getResponseType();
        $response = Response::create($result, $type)->header($header);

        throw new HttpResponseException($response);
    }

    /**
     * URL重定向
     * @access protected
     * @param  string         $url 跳转的URL表达式
     * @param  array|integer  $params 其它URL参数
     * @param  integer        $code http code
     * @param  array          $with 隐式传参
     * @return void
     */
    protected function redirect($url, $code = 302, $with = [])
    {
        $url = (strpos($url, '://') || 0 === strpos($url, '/')) ? $url : url($url);

        return Response::create((string) $url, 'redirect', $code)->with($with);
    }

    /**
     * 获取当前的response 输出类型
     * @access protected
     * @return string
     */
    protected function getResponseType()
    {
        $type = (request()->isJson() || request()->isAjax()) ? 'json' : 'html';

        return $type;
    }
}
