<?php
/******************************************************
 *	@version 1.0.0
 *	@file ng2-kickoff/inc/parsers/ng2Parser.php
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

abstract class ng2Parser {

	//! SimpleXML Node
	//! @access protected
	//! @var SimpleXMLElement
	protected $cXML;



	function __construct($xmlNode) {
		$this->cXML = $xmlNode;
	}


	function parse($moduleObj) {
		$settings = current($this->cXML->attributes());
		$props = array();
		$imports = array('@'=>array());
		
		if(count($this->cXML->children())) {
			$this->parseImportModulesDefinition($this->cXML, $imports);
			$this->parseDecoratorDefinition($this->cXML->decorator, $settings, $imports);

			if($this->cXML->class) $props = $this->parseClassDefinition($this->cXML->class, $settings, $imports);

		} else {
			$nodeVal = strval($this->cXML);
			if(!empty($nodeVal)) $props = json_decode($nodeVal, TRUE);
			if(!is_array($props)) throw new \Exception('Content is not valid XML or JSON');
		}

		$componentObj = new \ng2Kickoff\inc\files\Ng2Component($moduleObj, $settings);

		// expand component contents
		if(!empty($props)) $componentObj->addProperties($props);
		if(!empty($imports)) {
			foreach($imports as $src => $classes) {
				$componentObj->addImport($src, $classes);
			}
		}

		$moduleObj->addComponent($componentObj);
	
		return $componentObj;
	}


	//! Parse Decorator Definition XML Block
	//! @param SimpleXMLElement $xml XML Decorator Block element
	//! @param array &$settings Settings array
	//! @param array &$imports Imports array
	//! @return array Returns properties array
	function parseDecoratorDefinition($xml, &$settings, &$imports) {
		foreach($xml->children() as $childName => $childNode) {
			if(count($childNode->children())) {
				$settings[$childName] = '';
				foreach($childNode->children() as $node) {
					$settings[$childName] .= $node->asXML();
				}
			} else {
				$settings[$childName] = strval($childNode);
			}
		}
	}


	//! Parse Class Definition XML Block
	//! @param SimpleXMLElement $xml XML Class Block element
	//! @param array &$settings Settings array
	//! @param array &$imports Imports array
	//! @return array Returns properties array
	function parseClassDefinition($xml, &$settings, &$imports) {
		$props = array();
		if(!$xml->count()) return $props;

		foreach($xml->children() as $childName => $childNode) {

			$attrs = $childNode->attributes();
			$key = strval($childName);
			$val = strval($childNode);

			// determine if import is needed
			switch($key) {
				case 'ngOnInit':
					if(!isset($imports['@angular/core'])) $imports['@angular/core'] = array();
					$imports['@angular/core'][] = 'OnInit';
					$settings['implements'] = (!isset($settings['implements'])) ? array() : (array)$settings['implements'];
					$settings['implements'][] = 'OnInit';
					break;

			}

			if(isset($attrs['type'])) {
				switch($attrs['type']) {
					case 'number':
						$val = (is_numeric($val)) ? $val : array('return'=>'number');
						break;

					case 'string':
						if($val[0] != substr($val, -1) || ($val[0] != "'" && $val[0] != "'")) {
							$val = "'" . addcslashes($val, "'") . "'";
						}
						break;

					case 'function':
						$val = array('args'=>array(), 'body'=>$val, 'return'=>NULL);

						if(!empty($attrs['args'])) {
							$val['args'] = explode(',', $attrs['args']);

							foreach($val['args'] as &$arg) {
								$arg = trim($arg);

								// extract classes
								if(strpos($arg, ':')) {
									list($p, $v) = explode(':', $arg, 2);
									$v = trim($v);
									// deal with classes & promises

									if(!in_array($v, array('string', 'any', 'void', 'number', 'boolean'))) $imports['@'][] = $v;
								}
							}
						}

						if(!empty($attrs['return'])) {
							$ret = (substr($attrs['return'], -2) == '[]') ? substr($attrs['return'], 0, -2) : $attrs['return'];
							if(!in_array($ret, array('string', 'any', 'void', 'number', 'boolean'))) $imports['@'][] = $ret;
							$val['return'] = $attrs['return'];
						}
						break;

					case 'raw':
						// do nothing -> pass straight through
						break;

					default:
						// likely a property declaration of a class
						$val = array('args'=>NULL, 'body'=>$val, 'return'=>$attrs['type']);
						$ret = (substr($attrs['return'], -2) == '[]') ? substr($attrs['return'], 0, -2) : $attrs['return'];
						if(!in_array($ret, array('string', 'any', 'void', 'number', 'boolean'))) $imports['@'][] = $ret;
						break;
				}	
			}
			$props[$key] = $val;
		}
		return $props;
	}


	//! Parse Import Modules Definition XML Block
	//! @param SimpleXMLElement $xml XML Module Block element
	//! @param array &$imports Imports array
	//! @return array Returns properties array
	function parseImportModulesDefinition($xml, &$imports) {
		if(!is_array($imports)) $imports = array();
		foreach($xml->module as $importModuleNode) {
			$attrs = $importModuleNode->attributes();
			if(isset($attrs['src'])) {
				$src = (string)$attrs['src'];
				if(!isset($imports[$src])) $imports[$src] = array();
				$imports[$src] = array_merge($imports[$src], array_map('trim', explode(',', strval($importModuleNode))) );
			} else {
				$imports['@'] = array_merge($imports['@'], array_map('trim', explode(',', strval($importModuleNode))) );
			}
		}
	}

}