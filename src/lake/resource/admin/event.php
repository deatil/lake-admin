<?php
// 事件定义文件
return [
    'bind'      => [
    ],

    'listen'    => [
        'AppInit'  => [],
        'HttpRun'  => [],
        'HttpEnd'  => [
            // 操作记录
            \Lake\Admin\Listener\AdminLog::class,
        ],
        'LogLevel' => [],
        'LogWrite' => [],
    
        // 自定义后台首页
        'LakeAdminMainUrl' => [
            // 自定义后台首页
            \Lake\Admin\Listener\AdminMainUrl::class,
        ],
    ],

    'subscribe' => [
    ],
];
