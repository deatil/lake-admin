<?php

/**
 * lake-admin 模块引入command配置
 *
 * @create 2019-10-6
 * @author deatil
 */
 
use think\facade\Hook as ThinkHook;

// 监听command引入，需直接引入command类文件
ThinkHook::listen('lake_admin_commands');
