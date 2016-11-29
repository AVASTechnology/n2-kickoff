<?php
/******************************************************
 *	@version 1.0.0
 *	@file ng2-kickoff/inc/files/Ng2Service.php
 *	@dependents none
 *	@author Anthony Green
 *	@copyright Anthony Green
 *	@license GPL
 *
 ******************************************************
 *
 * Service File Framework
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

class Ng2Service extends Ng2File {


	//! Component Properties
	//! @access protected
	//! @var array
	protected $mProperties = array();

	//! Angular Imports
	//! @access protected
	//! @default array('@angular/core'=>array('Injectable'))
	//! @var array
	protected $mImports = array('@angular/core'=>array('Injectable'));


	//! Constructor
	//! @return void
	function __construct($parentObj, $settings) {
		parent::__construct($parentObj, $settings, 'ts');

		if(empty($this->mSettings['name'])) $this->mSettings['name'] = $parentObj->getName();

		$this->mFilename = $this->mSettings['name'] . '.service.' . $this->mFileType;

		if(empty($this->mSettings['export_class'])) {
			$this->mSettings['export_class'] = implode('', array_map('ucfirst', explode('-', $this->mSettings['name']))) . 'Service';
		}
	}

	//! Get Setting
	//! @param string|NULL|TRUE $setting Setting name or NULL for all settings of this object or TRUE to include defaults @default[NULL]
	function getSetting($setting = NULL) {
		if(is_null($setting)) return $this->mSettings;
		if($setting !== TRUE) return $this->mSettings[$setting];
		return array_merge($this->cParent->getDefaultSettings('services'), $this->mSettings);
	}



	//! Add Component Properties
	//! @param array $properties Associative array of properties to add
	function addProperties($properties) {
		if(!empty($properties) && is_array($properties)) {
			$this->mProperties = array_merge($this->mProperties, $properties);
		}
	}






	//! Generate Module Files
	function generate() {

		$currentDir = NULL;

		$formatterObj = $this->createFormatter();

		//! @TODO Add File Header details

		// add angular imports
		$formatterObj->addLine('// Angular Imports');
		if(!empty($this->mImports)) {
			foreach($this->mImports as $src => $classes) {
				$this->buildImportLine($formatterObj, $src, $classes);
			}
		}

		$formatterObj->addLine("import { Headers, Http } from '@angular/http';");
		$formatterObj->addLine('');
		$formatterObj->addLine("import 'rxjs/add/operator/toPromise'");
		$formatterObj->addLine('');

		// add app imports
		$formatterObj->addLine('// App Imports');
		$formatterObj->addLine('');

		// Export Service Class
		$formatterObj->addLine('@Injectable()');
		$formatterObj->addBlock('export class ' . $this->getExportClassName() . ' {', '}');

		// add Component properties
		foreach($this->mProperties as $prop => $declaration) {
			$formatterObj->addLine("$prop = $declaration;");
		}

		$formatterObj->closeCurrentBlock();
	}












}


