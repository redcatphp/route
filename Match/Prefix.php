<?php
namespace RedCat\Route\Match;
use RedCat\Route\Match;
class Prefix extends Match{
	function __invoke($uri){
		$match = ltrim($this->match,'/');
		if(empty($match)){
			if(empty($uri))
				return '';
		}
		elseif(strpos($uri,$match)===0){
			return (string)substr($uri,strlen($match));
		}
	}
}