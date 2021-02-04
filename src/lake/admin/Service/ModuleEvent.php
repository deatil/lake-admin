<?php

declare (strict_types = 1);

namespace Lake\Admin\Service;

use think\App;
use think\Event;

/**
 * 模块事件
 *
 * @create 2020-7-27
 * @author deatil
 */
class ModuleEvent
{
    /** @var Event */
    protected $event;
    
    /**
     * 构造函数
     *
     * @create 2020-7-27
     * @author deatil
     */
    public function __construct(App $app)
    {
        $this->event  = new Event($app);
    }

    /**
     * 注册应用事件
     * @access public
     * @param array $event 事件数据
     * @return void
     *
     * @create 2020-7-27
     * @author deatil
     */
    public function load(array $event): ModuleEvent
    {
        if (isset($event['bind'])) {
            $this->event->bind($event['bind']);
        }

        if (isset($event['listen'])) {
            $this->event->listenEvents($event['listen']);
        }

        if (isset($event['subscribe'])) {
            $this->event->subscribe($event['subscribe']);
        }
        
        return $this;
    }
    
    /**
     * 触发事件
     * @access protected
     * @param array $event 事件数据
     * @return string|array|etc
     *
     * @create 2020-7-27
     * @author deatil
     */
    public function trigger($event, $params = null, bool $once = false)
    {
        return $this->event->trigger($event, $params, $once);
    }

}
