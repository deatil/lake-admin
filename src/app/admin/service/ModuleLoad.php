<?php

declare (strict_types = 1);

namespace app\admin\service;

use think\App;

/**
 * 模块事件
 *
 * @create 2020-7-27
 * @author deatil
 */
class ModuleLoad
{
    /** @var App */
    protected $app;
    
    /**
     * 构造函数
     *
     * @create 2020-7-27
     * @author deatil
     */
    public function __construct(App $app)
    {
        $this->app  = $app;
    }
    
    /**
     * 加载应用配置文件
     * @param string $appPath 应用路径
     * @return void
     *
     * @create 2020-7-27
     * @author deatil
     */
    public function loadApp($appPath)
    {
        if (is_file($appPath . 'common.php')) {
            include_once $appPath . 'common.php';
        }
        
        $files = [];
        $files = array_merge($files, glob($appPath . 'config' . DIRECTORY_SEPARATOR . '*' . $this->app->getConfigExt()));
        foreach ($files as $file) {
            $this->app->config->load($file, pathinfo($file, PATHINFO_FILENAME));
        }
        
        if (is_file($appPath . 'event.php')) {
            $events = include $appPath . 'event.php';
            if (is_array($events)) {
                $this->app->loadEvent($events);
            }
        }
        
        if (is_file($appPath . 'middleware.php')) {
            $this->app->middleware->import(include $appPath . 'middleware.php', 'app');
        }
        
        if (is_file($appPath . 'provider.php')) {
            $this->app->bind(include $appPath . 'provider.php');
        }
        
        // 加载应用默认语言包
        $this->loadLangPack($this->app->lang->defaultLangSet(), $appPath);
    }

    /**
     * 加载语言包
     * @param string $langset 语言
     * @return void
     */
    protected function loadLangPack($langset, $appPath)
    {
        if (empty($langset)) {
            return;
        }

        // 加载系统语言包
        $files = glob($appPath . 'lang' . DIRECTORY_SEPARATOR . $langset . '.*');
        $this->app->lang->load($files);
    }

}
