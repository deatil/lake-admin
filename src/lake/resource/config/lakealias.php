<?php

return [
    // 后台控制器
    "app\\admin\\controller\\AdminLog" => "Lake\\Admin\\Controller\\AdminLog",
    "app\\admin\\controller\\Attachments" => "Lake\\Admin\\Controller\\Attachments",
    "app\\admin\\controller\\Config" => "Lake\\Admin\\Controller\\Config",
    "app\\admin\\controller\\Error" => "Lake\\Admin\\Controller\\Error",
    "app\\admin\\controller\\Event" => "Lake\\Admin\\Controller\\Event",
    "app\\admin\\controller\\FieldType" => "Lake\\Admin\\Controller\\FieldType",
    "app\\admin\\controller\\File" => "Lake\\Admin\\Controller\\File",
    "app\\admin\\controller\\Index" => "Lake\\Admin\\Controller\\Index",
    "app\\admin\\controller\\Manager" => "Lake\\Admin\\Controller\\Manager",
    "app\\admin\\controller\\Menu" => "Lake\\Admin\\Controller\\Menu",
    "app\\admin\\controller\\Module" => "Lake\\Admin\\Controller\\Module",
    "app\\admin\\controller\\Passport" => "Lake\\Admin\\Controller\\Passport",
    "app\\admin\\controller\\Profile" => "Lake\\Admin\\Controller\\Profile",
    "app\\admin\\controller\\Role" => "Lake\\Admin\\Controller\\Role",
    "app\\admin\\controller\\RuleExtend" => "Lake\\Admin\\Controller\\RuleExtend",
    
    // 表单
    "HtmlForm" => "Lake\\Admin\\Service\\Form",
    
    // 模块基础别名
    "Lake\\Module" => "Lake\\Admin\\Module\\Module",
    "Lake\\Module\\Traits\\Jump" => "Lake\\Admin\\Http\\Traits\\Jump",
    "Lake\\Module\\Traits\\View" => "Lake\\Admin\\Http\\Traits\\View",
    "Lake\\Module\\Traits\\Json" => "Lake\\Admin\\Http\\Traits\\Json",
    "Lake\\Module\\Model\\ModelBase" => "Lake\\Admin\\Model\\ModelBase",
    "Lake\\Module\\Controller\\AdminBase" => "Lake\\Admin\\Module\\Controller\\AdminBase",
    "Lake\\Module\\Controller\\HomeBase" => "Lake\\Admin\\Module\\Controller\\HomeBase",
];
