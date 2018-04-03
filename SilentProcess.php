<?php
namespace RedCat\Route;
class SilentProcess{
	protected $debug;
	protected $stack = [];
	protected $cwd;
	protected $registred = false;
	function __construct($debug = false){
		$this->debug = $debug;
		$this->cwd = getcwd();
		header("Content-Encoding: none");
		header("Connection: close");
	}
	function debug($debug = true){
		$this->debug = $debug;
	}
	function register($callback, $key = null){
		if(!$this->registred){
			$this->registred = true;
			register_shutdown_function($this);
		}
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
		if(!$this->debug && !headers_sent()){
			$size = ob_get_length();
			header("Content-Length: {$size}");
			ob_end_flush();
			if(ob_get_length()){
				ob_flush();
			}
			flush();
		}
		chdir($this->cwd);
		foreach($this->stack as $callback){
			call_user_func($callback);
		}
	}
}
