<?php
namespace RedCat\Route\Match;
use RedCat\Route\Match;
class Extension extends Match{
	function __invoke($uri){
		$extensions = is_string($this->match)?explode('|',$this->match):$this->match;
		$e = strtolower(pathinfo($uri,PATHINFO_EXTENSION));
		if($e&&in_array($e,$extensions)){
			return [(string)substr($uri,0,-1*(strlen($e)+1)),$e];
		}
	}
	function getMatch(){
		return is_string($this->match)?$this->match:implode('|',$this->match);
	}
}