<?php
/******************************************************
 *	@version 1.0.0
 *	@file ng2-kickoff/inc/parsers/RouterParser.php
 *	@dependents none
 *	@author Anthony Green
 *	@copyright Anthony Green
 *	@license GPL
 *
 ******************************************************
 *
 *
 *
 *
 *
 *
 *
 *
 *
 *
 *
 *
 *
 *
 *
 *
 *
 */
namespace ng2Kickoff\inc\parsers;

class RouterParser extends ng2Parser {

	//! Parse XML
	//! @param Ng2Module $moduleObj Module Object corresponding to this component definition
	//! @return Ng2Router Returns router object
	function parse($moduleObj) {
		$routeDefAttrs = current($this->cXML->attributes());
		$routerObj = new \ng2Kickoff\inc\files\Ng2Router($moduleObj, $routeDefAttrs);
		$moduleObj->addRouter($routerObj);

		foreach($this->cXML->route as $routeNodeObj) {
			$routeAttrs = current($routeNodeObj->attributes());
			if(!isset($routeAttrs['path'])) continue;
			$routePath = $routeAttrs['path'];
			unset($routeAttrs['path']);
			$routerObj->addRoute($routePath, $routeAttrs);
		}
	
		return $routerObj;
	}



}