<?php
namespace RedCat\Route;
class Match implements MatchInterface{
	protected $match;
	function __construct($match){
		$this->match = $match;
	}
	function getMatch(){
		return $this->match;
	}
}