<?php
define ("BASE_PATH", __DIR__);

spl_autoload_register(function ($class) {
	require_once str_replace('\\', '/', $class). '.php'; 
});