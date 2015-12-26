<?php
namespace RedCat\Route\Match;
use RedCat\Route\Match;
class Regex extends Match{
	function __invoke($uri){
		if(preg_match($this->match, $uri, $params)){
			array_shift($params);
			return array_values($params);
		}
	}
}