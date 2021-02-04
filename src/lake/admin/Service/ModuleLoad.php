<?php

declare (strict_types = 1);

namespace Lake\Admin\Service;

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
        $routePath = $appPath . 'route' . DIRECTORY_SEPARATOR;
        if (is_dir($routePath)) {
            $this->app->http->setRoutePath($routePath);
        }
        
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
        
        // 检测并且导入语言包
        $this->checkLoadLangPack($appPath);
    }

    /**
     * 加载服务
     *
     * @create 2020-7-31
     * @author deatil
     */
    public function loadService($appPath)
    {
        if (is_file($appPath . 'service.php')) {
            $services = include $appPath . 'service.php';
            if (!empty($services) && is_array($services)) {
                foreach ($services as $service) {
                    $this->app->register($service);
                }
            }
        }
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

    /**
     * 检测并且导入语言包
     *
     * @create 2020-7-29
     * @author deatil
     */
    protected function checkLoadLangPack($appPath)
    {
        $loadLangPack = $this->app->config->get('app.load_lang_pack', 0);
        if (!$loadLangPack) {
            return false;
        }
        
        // 自动侦测当前语言
        $langset = $this->app->lang->detect($this->app->request);

        if ($this->app->lang->defaultLangSet() != $langset) {
            $this->LoadLangPack($langset, $appPath);
        }
    }

}
