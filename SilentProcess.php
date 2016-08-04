<?php
namespace RedCat\Route;
class SilentProcess{
	protected $debug;
	protected $stack = [];
	protected $cwd;
	function __construct($debug = false){
		$this->debug = $debug;
		$this->cwd = getcwd();
		register_shutdown_function($this);
		header("Content-Encoding: none");
		header("Connection: close");
	}
	function debug($debug = true){
		$this->debug = $debug;
	}
	function register($callback, $key = null){
		if($key){
			$this->stack[$key] = $callback;
		}
		else{
			$this->stack[] = $callback;
		}
	}
	function unregister($callback, $key = null){
		if(is_null($key)){
			$key = array_search($callback, $this->stack, true);
		}
		if($key!==false&&isset($this->stack[$key])){
			unset($this->stack[$key]);
		}
	}
	function __invoke(){		
		if(!$this->debug){
			$size = ob_get_length();
			header("Content-Length: {$size}");
			ob_end_flush();
			ob_flush();
			flush();
		}
		chdir($this->cwd);
		foreach($this->stack as $callback){
			call_user_func($callback);
		}
	}
}