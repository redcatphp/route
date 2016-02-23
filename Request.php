<?php
namespace RedCat\Route;
class Request implements \ArrayAccess,\Countable{
	protected $data;
	function __construct($data=null,$emptyStringAsNull=true,$trim=true){
		if(!isset($data))
			$data = $_REQUEST;
		$this->data = $data;
		
		if($trim) $this->data = self::trim($this->data);
		if($emptyStringAsNull) $this->data = self::emptyStringAsNull($this->data);
	}
	function __get($k){
		return isset($this->data[$k])?$this->data[$k]:null;
	}
	function __set($k,$v){
		$this->data[$k] = $v;
	}
	function __isset($k){
		return isset($this->data[$k]);
	}
	function __unset($k){
		if(isset($this->data[$k]))
			unset($this->data[$k]);
	}
	function offsetGet($k){
		return isset($this->data[$k])?$this->data[$k]:null;
	}
	function offsetSet($k,$v){
		$this->data[$k] = $v;
	}
	function offsetExists($k){
		return isset($this->data[$k]);
	}
	function offsetUnset($k){
		if(isset($this->data[$k]))
			unset($this->data[$k]);
	}
	function count(){
		return count($this->data);
	}
	function getArray(){
		return (array)$this->data;
	}
	function dot($param){
		$param = explode('.',$param);
		$k = array_shift($param);
		if(!isset($this->data[$k]))
			return;
		$v = $this[$k];
		while(null !== $k=array_shift($param)){
			if(!isset($v[$k]))
				return;
			$v = $v[$k];
		}
		return $v;
	}
	function __invoke(){
		switch(func_num_args()){
			case 0:
				return count($this->data)?(array)$this->data:null;
			break;
			case 1:
				$arg = func_get_arg(0);
				if(is_array($arg)){
					$r = [];
					foreach($arg as $k){
						if(isset($this[$k]))
							$r[$k] = $this->data[$k];
					}
					return $r;
				}
				else{
					return $this[$arg];
				}
			break;
			default:
				return $this(func_get_args());
			break;
		}
	}
	static function trim($str){
		if(is_array($str))
			return array_map([__CLASS__,__FUNCTION__], $str);
		return trim($str);
	}
	static function emptyStringAsNull($str){
		if(is_array($str))
			return array_map([__CLASS__,__FUNCTION__], $str);
		return $str===''?null:$str;
	}
}