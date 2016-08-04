<?php
namespace RedCat\Route;
static class SilentProcess{
	static function register($callback, $debug=false){
		if($debug){
			register_shutdown_function($callback);
			return;
		}
		
		header("Content-Encoding: none");
		header("Connection: close");
		register_shutdown_function(function()use($callback){			
			$size = ob_get_length();
			header("Content-Length: {$size}");
			ob_end_flush();
			ob_flush();
			flush();
			call_user_func($callback);
		});
	}
}