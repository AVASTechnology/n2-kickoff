<?php
/******************************************************
 *	@version 1.0.0
 *	@file ng2-kickoff/inc/parsers/ServiceParser.php
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

class ServiceParser extends ng2Parser {


	//! Parse XML
	//! @param Ng2Module $moduleObj Module Object corresponding to this component definition
	//! @return Ng2Service Returns service object
	function parse($moduleObj) {
		$settings = current($this->cXML->attributes());
		$props = array();
		$imports = array('@'=>array());
		
		if(count($this->cXML->children())) {
			$this->parseImportModulesDefinition($this->cXML, $imports);
			$props = $this->parseClassDefinition($this->cXML->class, $settings, $imports);

		} else {
			$nodeVal = strval($this->cXML);
			if(!empty($nodeVal)) $props = json_decode($nodeVal, TRUE);
			if(!is_array($props)) throw new \Exception('Content is not valid XML or JSON');
		}

		$serviceObj = new \ng2Kickoff\inc\files\Ng2Service($moduleObj, $settings);

		// expand component contents
		if(!empty($props)) $serviceObj->addProperties($props);
		if(!empty($imports)) {
			foreach($imports as $src => $classes) {
				$serviceObj->addImport($src, $classes);
			}
		}

		$moduleObj->addService($serviceObj);
	
		return $serviceObj;
	}





}