<?php

namespace Lake\Admin\Auth;

/**
 * 解析
 *
 * @create 2020-8-27
 * @author deatil
 */
class Parser
{
    /** 
     * @param string
     */
    protected $url = '';
    
    /** 
     * @param string
     */
    protected $path = '';
    
    /** 
     * @param array
     */
    protected $param = [];
    
    /**
     * 设置
     * @param string $url 链接地址
     * @return object
     *
     * @create 2020-8-27
     * @author deatil
     */
    public function withUrl($url)
    {
        $this->url = $url;
        return $this;
    }
    
    /**
     * 获取链接
     * @return string
     *
     * @create 2020-8-27
     * @author deatil
     */
    public function getUrl()
    {
        return $this->url;
    }
    
    /**
     * 获取地址
     * @return string
     *
     * @create 2020-8-27
     * @author deatil
     */
    public function getPath()
    {
        return $this->path;
    }
    
    /**
     * 获取参数
     * @return array
     *
     * @create 2020-8-27
     * @author deatil
     */
    public function getParam()
    {
        return $this->param;
    }
    
    /**
     * 解析
     * @return Parser
     *
     * @create 2020-8-27
     * @author deatil
     */
    public function parse()
    {
        $param = [];
        
        $path = preg_replace('/\?.*$/U', '', $this->url);
        $query = preg_replace('/^.+\?/U', '', $this->url);
        if ($this->url != $path) {
            parse_str($query, $param);
        }
        
        $this->path = $path;
        $this->param = $param;
        
        return $this;
    }
    
    /**
     * 解析
     * @return Parser
     *
     * @create 2020-8-27
     * @author deatil
     */
    public function parseUrl()
    {
        $param = [];
        
        $parseInfo = parse_url($this->url);
        if (isset($parseInfo['path'])) {
            $path = $parseInfo['path'];
        } else {
            $path = '';
        }
        
        if (isset($parseInfo['query'])) {
            $query = $parseInfo['query'];
        } else {
            $query = '';
        }
        
        if ($this->url != $path) {
            parse_str($query, $param);
        }
        
        $this->path = $path;
        $this->param = $param;
        
        return $this;
    }
}
