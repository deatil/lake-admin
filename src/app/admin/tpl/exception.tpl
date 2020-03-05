<?php
if(\think\facade\App::isDebug()) {
	$tpl = env('think_path') . 'tpl/think_exception.tpl';
	include_once $tpl;
} else {
	include_once __DIR__ . "/lake_exception.tpl";
}
?>