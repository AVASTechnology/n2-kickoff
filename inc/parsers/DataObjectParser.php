<?php
/******************************************************
 *	@version 1.0.0
 *	@file ng2-kickoff/inc/parsers/DataObjectParser.php
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

class DataObjectParser {

	//! SimpleXML Node
	//! @access protected
	//! @var SimpleXMLElement
	protected $cXML;



	function __construct($xmlNode) {
		$this->cXML = $xmlNode;
	}


	function parse($moduleObj) {

		$dataObj = new \ng2Kickoff\inc\files\Ng2DataObject($moduleObj, current($this->cXML->attributes()));

		// expand data object contents
		$dataObj->addProperties($this->expandDeclarationProperties($this->cXML));

		$moduleObj->addDataObject($dataObj);

		return $dataObj;
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