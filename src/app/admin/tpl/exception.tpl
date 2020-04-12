<?php
if(\think\facade\App::isDebug()) {
    $tpl = app()->getThinkPath() . 'tpl/think_exception.tpl';
    include_once $tpl;
} else {
    include_once __DIR__ . "/lake_exception.tpl";
}
?>