<?php
namespace RedCat\Route\Match;
use RedCat\Route\Match;
class Suffix extends Match{
	function __invoke($uri){
		$match = ltrim($this->match,'/');
		if(empty($match)){
			if(empty($uri))
				return '';
		}
		elseif(strrpos($uri,$match)===strlen($uri)-strlen($match)){
			return (string)substr($uri,strlen($match));
		}
	}
}