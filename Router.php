<?php
/*
 * Router - A mirco-framework for manage entry point of applications
 *
 * @package Router
 * @version 1.3
 * @link http://github.com/redcatphp/Router/
 * @author Jo Surikat <jo@surikat.pro>
 * @website http://redcatphp.com
 */
namespace RedCat\Route;
use RedCat\Wire\Di;
use RedCat\Route\Match\Regex;
use RedCat\Route\Match\Path;
class Router implements \ArrayAccess{
	private $routes = [];
	private $groups = [];
	private $groupKey;
	private $route;
	private $routeParams;
	private $di;
	private $index;
	private $group;
	function __construct(Di $di = null){
		$this->di = $di;
		$this->setGroup();
		$this->setIndex();
	}
	function map($map,$index=null,$prepend=false,$group=null){
		if(is_string($prepend)){
			$tmp = $prepend;
			if(is_bool($group))
				$prepend = $group;
			else
				$prepend = false;
			$group = $tmp;
		}
		foreach($map as list($match,$route)){
			$this->route($match,$route,$index,$group,$prepend);
		}
		return $this;
	}
	function append($match,$route,$index=null,$group=null){
		return $this->route($match,$route,$index,$group);
	}
	function prepend($match,$route,$index=null,$group=null){
		return $this->route($match,$route,$index,$group,true);
	}
	function find($uri,$server=null){
		$uri = ltrim($uri,'/');
		ksort($this->routes);
		foreach($this->routes as $groupKey=>$group){
			foreach($group as $indexGroup){
				foreach($indexGroup as list($match,$route)){
					$routeParams = call_user_func($this->objectify($match),$uri,$server);
					if($routeParams!==null){
						$this->groupKey = $groupKey;
						$this->route = $route;
						$this->routeParams = $routeParams;
						return true;
					}
				}
			}
		}
	}
	function group($group=null,$callback=null,$prepend=false){
		if(is_null($group))
			$group = $this->group;
		if(!is_string($group)){
			$callback = $group;
			$group = $this->group;
		}
		if($prepend)
			array_unshift($this->groups[$group],$callback);
		else
			$this->groups[$group][] = $callback;
		
	}
	function display(){
		$route = $this->route;
		if(isset($this->groups[$this->groupKey])){
			foreach($this->groups[$this->groupKey] as $call){
				if(call_user_func($call,$this->routeParams,$route)===false)
					return;
			}
		}
		while(is_callable($route=$this->objectify($route))){
			$route = call_user_func($route,$this->routeParams);
		}
	}
	function route($match,$route,$index=null,$group=null,$prepend=false,$subindex=null){
		if(is_string($index)){
			$tmp = $index;
			if(is_integer($group))
				$index = $group;
			else
				$index = false;
			$group = $tmp;
		}
		if(is_null($group))
			$group = $this->group;
		if(is_null($index))
			$index = $this->index;
		$pair = [$this->matchType($match),$route];
		if(!isset($this->routes[$group][$index]))
			$this->routes[$group][$index] = [];
		if(!is_null($subindex))
			$this->routes[$group][$index][$subindex] = $pair;
		elseif($prepend)
			array_unshift($this->routes[$group][$index],$pair);
		else
			$this->routes[$group][$index][] = $pair;
		return $this;
	}
	private function matchType($match){
		if(is_string($match)){
			if(strpos($match,'/^')===0&&strrpos($match,'$/')-strlen($match)===-2){
				return ['new:'.Regex::class,$match];
			}
			else{
				return ['new:'.Path::class,$match];
			}
		}
		return $match;
	}
	function setIndex($index=0){
		$this->index = $index;
	}
	function setGroup($group='default'){
		$this->group = $group;
	}
	function objectify($a){
		if($this->di)
			return $this->di->objectify($a);
		if(is_object($a))
			return $a;
		if(is_array($a)){
			if(is_array($a[0])){
				$a[0] = $this->objectify($a[0]);
				return $a;
			}
			else{
				$args = $a;
				$s = array_shift($args);
			}
		}
		else{
			$args = [];
			$s = $a;
		}
		if(is_string($s)&&strpos($s,'new:')===0)
			$a = (new \ReflectionClass(substr($s,4)))->newInstanceArgs($args);
		return $a;
	}
	function offsetSet($k,$v){
		list($match,$route) = $v;
		$this->route($match,$route,$this->index,null,false,$k);
	}
	function offsetGet($k){
		if(!isset($this->routes[$this->group][$this->index][$k]))
			$this->routes[$this->group][$this->index][$k] = [];
		return $this->routes[$this->group][$this->index][$k];
	}
	function offsetExists($k){
		return isset($this->routes[$this->group][$this->index][$k]);
	}
	function offsetUnset($k){
		if(isset($this->routes[$this->group][$this->index][$k]))
			unset($this->routes[$this->group][$this->index][$k]);
	}
}