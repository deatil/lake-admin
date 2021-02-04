<?php

namespace Lake\Admin\Http\Traits;

use think\Response;
use think\exception\HttpResponseException;

/**
 * JSON数据返回
 *
 * @create 2020-8-22
 * @author deatil
 *
 * 用法：
 * class index
 * {
 *     use \Lake\Admin\Http\Traits\Json;
 *     public function index(){
 *         $this->errorJson();
 *         $this->successJson();
 *     }
 * }
 */
trait Json
{
    // 跨域
    protected $isAllowOrigin = false;
    
    // 允许跨域域名
    protected $allowOrigin = '*';
    
    // 是否允许后续请求携带认证信息（cookies）,该值只能是true,否则不返回
    protected $allowCredentials = false; // true or false
    
    // 预检结果缓存时间,缓存
    protected $maxAge = '';
    
    // 该次请求的请求方式
    protected $allowMethods = 'GET,POST,PATCH,PUT,DELETE,OPTIONS';
    
    // 该次请求的自定义请求头字段
    protected $allowHeaders = 'X-Requested-With,X_Requested_With,Content-Type';
    
    /*
     * 是否允许跨域域名
     *
     * @create 2020-8-12
     * @author deatil
     */
    protected function setIsAllowOrigin($isAllowOrigin = false)
    {
        if ($isAllowOrigin === true) {
            $this->isAllowOrigin = true;
        } else {
            $this->isAllowOrigin = false;
        }
        
        return $this;
    }
    
    /*
     * 允许跨域域名
     *
     * @create 2020-8-25
     * @author deatil
     */
    protected function setAllowOrigin($allowOrigin = '*')
    {
        $this->allowOrigin = $allowOrigin;
        
        return $this;
    }
    
    /*
     * 允许后续请求携带认证信息
     *
     * @create 2020-8-25
     * @author deatil
     */
    protected function setAllowCredentials($allowCredentials = false)
    {
        if ($allowCredentials === true) {
            $this->allowCredentials = true;
        } else {
            $this->allowCredentials = false;
        }
        
        return $this;
    }
    
    /*
     * 预检结果缓存时间
     *
     * @create 2020-8-25
     * @author deatil
     */
    protected function setMaxAge($maxAge = false)
    {
        $this->maxAge = $maxAge;
        
        return $this;
    }
    
    /*
     * 该次请求的请求方式
     *
     * @create 2020-8-25
     * @author deatil
     */
    protected function setAllowMethods($allowMethods = false)
    {
        $this->allowMethods = $allowMethods;
        
        return $this;
    }
    
    /*
     * 该次请求的自定义请求头字段
     *
     * @create 2020-8-25
     * @author deatil
     */
    protected function setAllowHeaders($allowHeaders = false)
    {
        $this->allowHeaders = $allowHeaders;
        
        return $this;
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
    
    /**
     * 输出响应
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
        if ($this->isAllowOrigin == 1) {
            $header['Access-Control-Allow-Origin']  = $this->allowOrigin;
            $header['Access-Control-Allow-Headers'] = $this->allowHeaders;
            $header['Access-Control-Allow-Methods'] = $this->allowMethods;
            
            if ($this->allowCredentials === true) {
                $header['Access-Control-Allow-Credentials'] = $this->allowCredentials;
            }
            
            if (!empty($this->maxAge)) {
                $header['Access-Control-Max-Age'] = $this->maxAge;
            }
        }
        
        $header = array_merge($header, $userHeader);
        
        $response = Response::create($result, $type)->header($header);
        throw new HttpResponseException($response);
    }
}
