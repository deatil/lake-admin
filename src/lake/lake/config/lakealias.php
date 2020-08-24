<?php

return [
    // 后台控制器
    "app\\admin\\controller\\AdminLog" => "lake\\admin\\controller\\AdminLog",
    "app\\admin\\controller\\Attachments" => "lake\\admin\\controller\\Attachments",
    "app\\admin\\controller\\AuthManager" => "lake\\admin\\controller\\AuthManager",
    "app\\admin\\controller\\Config" => "lake\\admin\\controller\\Config",
    "app\\admin\\controller\\Error" => "lake\\admin\\controller\\Error",
    "app\\admin\\controller\\Event" => "lake\\admin\\controller\\Event",
    "app\\admin\\controller\\FieldType" => "lake\\admin\\controller\\FieldType",
    "app\\admin\\controller\\Index" => "lake\\admin\\controller\\Index",
    "app\\admin\\controller\\Manager" => "lake\\admin\\controller\\Manager",
    "app\\admin\\controller\\Menu" => "lake\\admin\\controller\\Menu",
    "app\\admin\\controller\\Module" => "lake\\admin\\controller\\Module",
    "app\\admin\\controller\\Passport" => "lake\\admin\\controller\\Passport",
    "app\\admin\\controller\\Profile" => "lake\\admin\\controller\\Profile",
    "app\\admin\\controller\\RuleExtend" => "lake\\admin\\controller\\RuleExtend",
    
    // 模块基础别名
    "lake\\Module" => "lake\\admin\\module\\Module",
    "lake\\module\\traits\\Jump" => "lake\\admin\\http\\Jump",
    "lake\\module\\traits\\View" => "lake\\admin\\http\\View",
    "lake\\module\\traits\\Json" => "lake\\admin\\http\\Json",
    "lake\\module\\model\\ModelBase" => "lake\\admin\\model\\ModelBase",
    "lake\\module\\controller\\AdminBase" => "lake\\admin\\module\\controller\\AdminBase",
    "lake\\module\\controller\\HomeBase" => "lake\\admin\\module\\controller\\HomeBase",
];
