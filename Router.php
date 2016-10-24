<?php
/*
 * Router - A mirco-framework for manage entry point of applications
 *
 * @package Router
 * @version 1.6
 * @link http://github.com/redcatphp/Router/
 * @author Jo Surikat <jo@surikat.pro>
 * @website http://redcatphp.com
 */
namespace RedCat\Route;
use RedCat\Strategy\Di;
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
	function getRoutes(){
		return $this->routes;
	}
	function getGroups(){
		return $this->groups;
	}
	function map($map,$index=null,$prepend=false,$group=null,$continue=false){
		if(is_string($prepend)){
			$tmp = $prepend;
			if(is_bool($group))
				$prepend = $group;
			else
				$prepend = false;
			$group = $tmp;
		}
		foreach($map as list($match,$route)){
			$this->route($match,$route,$index,$group,$continue,$prepend);
		}
		return $this;
	}
	function append($match,$route,$index=null,$group=null,$continue=false){
		return $this->route($match,$route,$index,$group,$continue);
	}
	function prepend($match,$route,$index=null,$group=null,$continue=false){
		return $this->route($match,$route,$index,$group,$continue,true);
	}
	function find($uri,$server=null){
		$uri = ltrim($uri,'/');
		ksort($this->routes);
		foreach($this->routes as $routeGroup){
			foreach($routeGroup as list($match,$route,$groupKey,$continue)){
				$routeParams = call_user_func_array($this->objectify($match),[&$uri,&$server]);
				if($routeParams!==null){
					$this->groupKey = $groupKey;
					$this->route = $route;
					$this->routeParams = $routeParams;
					if($continue){
						$this->execute();
					}
					else{
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
	protected function execute(){
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
	function display(){
		$this->execute();
	}
	function route($match,$route,$index=null,$group=null,$continue=false,$prepend=false,$subindex=null){
		if(is_string($index)){
			$tmp = $index;
			if(is_integer($group))
				$index = $group;
			else
				$index = false;
			if(is_bool($group))
				$continue = $group;
			$group = $tmp;
		}
		elseif(is_bool($index)){
			$continue = $index;
			$index = null;
		}
		elseif(is_bool($group)){
			$continue = $group;
			$group = null;
		}
		if(is_null($group))
			$group = $this->group;
		if(is_null($index))
			$index = $continue?$this->index-100:$this->index;
		$pair = [$this->matchType($match),$route,$group,$continue];
		if(!isset($this->routes[$index]))
			$this->routes[$index] = [];
		if(!is_null($subindex))
			$this->routes[$index][$subindex] = $pair;
		elseif($prepend)
			array_unshift($this->routes[$index],$pair);
		else
			$this->routes[$index][] = $pair;
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
		$this->route($match,$route,$this->index,null,false,false,$k);
	}
	function offsetGet($k){
		if(!isset($this->routes[$this->index][$k]))
			$this->routes[$this->index][$k] = [];
		return $this->routes[$this->index][$k];
	}
	function offsetExists($k){
		return isset($this->routes[$this->index][$k]);
	}
	function offsetUnset($k){
		if(isset($this->routes[$this->index][$k]))
			unset($this->routes[$this->index][$k]);
	}
}