<?php
/******************************************************
 *	@version 1.0.0
 *	@file ng2-kickoff/inc/parsers/ModuleParser.php
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

class ModuleParser extends ng2Parser {

	//! Parse XML
	//! @param Ng2Module|Core $parentObj Parent of this Module Object, either Core or another module object
	//! @return Ng2Module Returns module object
	function parse($parentObj) {
		global $ng2Kickoff;

		$moduleAttrs = current($this->cXML->attributes());
		if(empty($parentObj)) {
			$moduleAttrs = array_merge(array('name'=>'app'), $moduleAttrs);
		}

		$moduleObj = new \ng2Kickoff\inc\files\Ng2Module(($parentObj instanceof files\Ng2Module) ? $parentObj : $ng2Kickoff, $moduleAttrs);

		if(isset($this->cXML->routes)) {
			$routerParser = new RouterParser($this->cXML->routes);
			$routerParser->parse($moduleObj);
		}


		if(isset($this->cXML->components)) {
			$moduleObj->addDefaultSettings('components', current($this->cXML->components->attributes()));

			foreach($this->cXML->components->component as $componentNodeObj) {
				$compParser = new ComponentParser($componentNodeObj);
				$compParser->parse($moduleObj);
			}
		}


		if(isset($this->cXML->providers)) {
			$moduleObj->addDefaultSettings('services', current($this->cXML->providers->attributes()));

			foreach($this->cXML->providers->service as $serviceNodeObj) {
				$serviceParser = new ServiceParser($serviceNodeObj);
				$serviceParser->parse($moduleObj);
			}
		}


		if(isset($this->cXML->dataobjects)) {
			$moduleObj->addDefaultSettings('dataobjects', current($this->cXML->dataobjects->attributes()));

			foreach($this->cXML->dataobjects->object as $dataNodeObj) {
				$dataObjParser = new DataObjectParser($dataNodeObj);
				$dataObjParser->parse($moduleObj);
			}
		}

		if(isset($this->cXML->children)) {
			foreach($this->cXML->children->children() as $childName => $childNode) {

				$childAttrs = current($childNode->attributes());			
				if(isset($childAttrs['src'])) {
					// comma seperated list of direct imports
					$moduleObj->addImport($childAttrs['src'], array_map('trim', explode(',', strval($childNode))) );
				} else {
					$childParser = new ModuleParser($childNode);
					if($childModuleObj = $childParser->parse($moduleObj)) {
						$moduleObj->addChildModules($childModuleObj);
					}
				}

			}
		}
	
		return $moduleObj;
	}




	//! Expand Declaration Properties
	//! @TODO Should be recursive for XML children
	//! @access private
	//! @param SimpleXMLElement
	//! @return array Associative array of properties to assign to object
	private function expandDeclarationProperties($xmlNode) {
		$props = array();
	
		if(count($xmlNode->children())) {
			foreach($xmlNode->children() as $childName => $childNode) {
				$props[$childName] = strval($childNode);
			}
		} else {
			$nodeVal = strval($xmlNode);
			if(!empty($nodeVal)) $props = json_decode($nodeVal, TRUE);
			if(!is_array($props)) throw new \Exception('Content is not valid XML or JSON');
		}
		return $props;	
	}



}