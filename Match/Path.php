<?php
namespace RedCat\Route\Match;
use RedCat\Route\Match;
class Path extends Match{
	function __invoke($uri){
		$match = ltrim($this->match,'/');
		if(empty($match)){
			if(empty($uri))
				return '';
		}
		elseif((string)$uri===$match){
			return $uri;
		}
	}
}