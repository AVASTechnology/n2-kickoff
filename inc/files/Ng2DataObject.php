<?php
/******************************************************
 *	@version 1.0.0
 *	@file ng2-kickoff/inc/files/Ng2DataObject.php
 *	@dependents none
 *	@author Anthony Green
 *	@copyright Anthony Green
 *	@license GPL
 *
 ******************************************************
 *
 * Data Object File Framework
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
namespace ng2Kickoff\inc\files;

class Ng2DataObject extends Ng2File {


	//! Component Properties
	//! @access protected
	//! @var array
	protected $mProperties = array();

	//! Constructor
	//! @return void
	function __construct($parentObj, $settings) {
		parent::__construct($parentObj, $settings, 'ts');

		if(empty($this->mSettings['name'])) $this->mSettings['name'] = $parentObj->getName();

		$this->mFilename = $this->mSettings['name'] . '.obj.' . $this->mFileType;

		if(empty($this->mSettings['export_class'])) {
			$this->mSettings['export_class'] = implode('', array_map('ucfirst', explode('-', $this->mSettings['name'])));
		}


	}

	//! Get Setting
	//! @param string|NULL|TRUE $setting Setting name or NULL for all settings of this object or TRUE to include defaults @default[NULL]
	function getSetting($setting = NULL) {
		if(is_null($setting)) return $this->mSettings;
		if($setting !== TRUE) return $this->mSettings[$setting];
		return array_merge($this->cParent->getDefaultSettings('dataobjects'), $this->mSettings);
	}





	//! Add Component Properties
	//! @param array $properties Associative array of properties to add
	function addProperties($properties) {
		if(!empty($properties) && is_array($properties)) {
			$this->mProperties = array_merge($this->mProperties, $properties);
		}
	}


	//! Adjust Settings for Module
	function configure() {


	}

	//! Generate Module Files
	function generate() {
		$currentDir = NULL;

		$formatterObj = $this->createFormatter();

		// add app imports
		$formatterObj->addLine('// App Imports');
		$formatterObj->addLine('');

		// Export Data Object Class
		$formatterObj->addBlock('export class ' . $this->getExportClassName() . ' {', '}');

		// add Component properties
		foreach($this->mProperties as $prop => $declaration) {
			$formatterObj->addLine("$prop: $declaration;");
		}

		$formatterObj->closeCurrentBlock();
	}














}


