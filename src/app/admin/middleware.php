<?php
// 全局中间件定义文件
return [
    // 全局请求缓存
    // \think\middleware\CheckRequestCache::class,
    
    // 多语言加载
    // \think\middleware\LoadLangPack::class,
    
    // Session初始化
    \think\middleware\SessionInit::class,
    
    // 权限检测
    \app\admin\middleware\AdminAuthCheck::class,

    // 锁屏检测
    \app\admin\middleware\AdminScreenLockCheck::class,
];
