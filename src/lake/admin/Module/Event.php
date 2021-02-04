<?php

namespace Lake\Admin\Module;

use Lake\Admin\Model\Event as EventModel;

/**
 * Event
 *
 * @create 2020-9-15
 * @author deatil
 */
class Event
{
    /**
     * 安装模块事件类
     * @param type $name 模块名称
     * @param type $events 事件类信息
     * @return boolean
     */
    public static function install($name = '', $events = [])
    {
        if (empty($name)) {
            return false;
        }
        
        if (empty($events)) {
            return false;
        }
        
        foreach ($events as $event) {
            EventModel::create([
                'module' => $name,
                'name' => $event['name'],
                'class' => $event['class'],
                'description' => $event['description'],
                'listorder' => isset($event['listorder']) ? $event['listorder'] : 100,
                'status' => (isset($event['status']) && $event['status'] == 1) ? 1 : 0,
            ]);
        }
        
    }
    
    /**
     * 卸载摸板事件
     * @param type $name
     * @return boolean
     */
    public static function uninstall($name = '')
    {
        if (empty($name)) {
            $this->error = '模块名称不能为空！';
            return false;
        }
        
        EventModel::where([
            'module' => $name,
        ])->delete();
        
        return true;
    }
    
    /**
     * 启用
     */
    public static function enable($name = '')
    {
        if (empty($name)) {
            return false;
        }
        
        $status = EventModel::where([
            'module' => $name,
        ])->update([
            'status' => 1,
        ]);
        if ($status === false) {
            return false;
        }
        
        return true;
    }
    
    /**
     * 禁用
     */
    public static function disable($name = '')
    {
        if (empty($name)) {
            return false;
        }
        
        $status = EventModel::where([
            'module' => $name,
        ])->update([
            'status' => 0,
        ]);
        if ($status === false) {
            return false;
        }
        
        return true;
    }
    
}