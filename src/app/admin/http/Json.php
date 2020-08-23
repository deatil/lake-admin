<?php

/**
 * 用法：
 * class index
 * {
 *     use \app\admin\http\Json;
 *     public function index(){
 *         $this->errorJson();
 *         $this->successJson();
 *     }
 * }
 */
namespace app\admin\http;

use think\Response;
use think\exception\HttpResponseException;

/**
 * JSON数据返回
 *
 * @create 2020-8-22
 * @author deatil
 */
trait Json
{
    // 跨域
    protected $allowOrigin = 0;
    
    // 允许跨域域名
    protected $allowOriginUrl = '*';
    
    /*
     * 返回错误json
     *
     * @create 2020-8-12
     * @author deatil
     */
    protected function setAllowOrigin($allowOrigin = false)
    {
        if ($allowOrigin === true) {
            $this->allowOrigin = 1;
        } else {
            $this->allowOrigin = 0;
        }
    }
    
    /*
     * 返回错误json
     *
     * @create 2020-8-12
     * @author deatil
     */
    protected function setAllowOriginUrl($allowOriginUrl = '')
    {
        if (empty($allowOriginUrl)) {
            $allowOriginUrl = '*';
        }
        
        $this->allowOriginUrl = $allowOriginUrl;
    }
    
    /*
     * 返回错误json
     *
     * @create 2020-8-12
     * @author deatil
     */
    protected function errorJson(
        $msg = null, 
        $code = 1, 
        $data = [],
        $header = []
    ) {
        return $this->httpResponse(false, $code, $msg, $data, $header);
    }
    
    /*
     * 返回成功json
     *
     * @create 2020-8-12
     * @author deatil
     */
    protected function successJson(
        $msg = '获取成功', 
        $data = [], 
        $code = 0,
        $header = []
    ) {
        return $this->httpResponse(true, $code, $msg, $data, $header);
    }
    
    /*
     * 公用
     *
     * @create 2020-8-12
     * @author deatil
     */
    protected function httpResponse(
        $success = true, 
        $code, 
        $msg = "", 
        $data = [], 
        $userHeader = []
    ) {
        $result['success'] = $success;
        $result['code'] = $code;
        $msg ? $result['msg'] = $msg : null;
        $data ? $result['data'] = $data : null;

        $type = 'json';

        $header = [];
        if ($this->allowOrigin == 1) {
            $header['Access-Control-Allow-Origin']  = $this->allowOriginUrl;
            $header['Access-Control-Allow-Headers'] = 'X-Requested-With,X_Requested_With,Content-Type';
        }
        
        $header['Access-Control-Allow-Methods'] = 'GET,POST,PATCH,PUT,DELETE,OPTIONS';
        
        $header = array_merge($header, $userHeader);
        
        $response = Response::create($result, $type)->header($header);
        throw new HttpResponseException($response);
    }
}
