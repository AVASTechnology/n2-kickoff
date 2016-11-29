<?php
/******************************************************
 *	@version 1.0.0
 *	@file ng2-kickoff/inc/files/Ng2File.php
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
namespace ng2Kickoff\inc\files;

use ng2Kickoff\inc\Formatter as Formatter;

abstract class Ng2File {

	//! File Name
	//! @access protected
	//! @default ''
	//! @var string
	protected $mFilename = '';

	//! File Path
	//! @access protected
	//! @default ''
	//! @var string
	protected $mPath = '';
		
	//! File Type
	//! @access protected
	//! @default 'ts'
	//! @var enum['ts', 'js', 'html', 'css']
	protected $mFileType = 'ts';
	
	//! File Settings
	//! @access protected
	//! @var array
	protected $mSettings = array('name'=>'',
								'version'=>'',
								'export_class'=>'',
								);


	//! Angular Imports
	//! Associative array of arrays
	//! 	key => file src
	//! 	values => classes to import
	//! @access protected
	//! @default array()
	//! @var array
	protected $mImports = array();

	//! Available Settings
	//! @access static
	//! @var arary
	static $settings = array();


	//! Parent object
	//! @access protected
	//! @var object
	protected $cParent;


	//! Constructor
	//! @return void
	function __construct($parentObj, $settings = array(), $type = 'ts') {
		$this->cParent = $parentObj;
		$this->mPath = $parentObj->getModuleDirectory();

		$this->mSettings = array_merge(static::$settings, $this->mSettings);
		if(is_array($settings) && !empty($settings)) {
			foreach($settings as $name => $val) {
				if(isset(static::$settings[$name])) {
					switch(gettype(static::$settings[$name])) {
						case 'boolean':
							if($val == 'true' || $val == 'false') {
								$this->mSettings[$name] = ($val == 'true');
							} else {
								$this->mSettings[$name] = (bool)$val;
							}
							break;
						case 'integer':
							$this->mSettings[$name] = intval($val);
							break;

						case 'array':
							$this->mSettings[$name] = str_getcsv($val);
							break;

						default:
						case 'string':
							$this->mSettings[$name] = $val;
							break;
					}
				} else {
					$this->mSettings[$name] = $val;
				}
			}
		}
	}

	//! Get Setting
	//! @param string|NULL|TRUE $setting Setting name or NULL for all settings of this object or TRUE to include defaults @default[NULL]
	function getSetting($setting = NULL) {
		if(is_null($setting)) return $this->mSettings;
		if($setting !== TRUE) return $this->mSettings[$setting];
		if(method_exists($this->cParent, 'getDefaultSettings')) {
			return array_merge($this->cParent->getDefaultSettings('components'), $this->mSettings);
		}
		return $this->mSettings;
	}

	//! Get General Name
	//! Helper function to get setting name
	//! @return string Returns setting name
	function getName() {
		return $this->mSettings['name'];
	}

	//! Get Export Class Name
	//! Helper function to get setting export_class
	//! @return string Returns setting export_class
	function getExportClassName() {
		return $this->mSettings['export_class'];
	}


	//! Get File Name
	//! @return string Returns file name
	function getFilename() {
		return $this->mFilename;
	}


	//! Get Importable File Name
	//! @param string $currentDir Current directory for relative pathing @default[NULL]
	//! @return string Returns file name for use in an import statement
	function getImportableFilename($currentDir = NULL) {
		if(!empty($currentDir)) {
			// relate to Filename
			$relDir = '';
		} else {
			$relDir = './';
		}
		return $relDir . strstr($this->mFilename, '.' . $this->mFileType, TRUE);;
	}


	//! Get Import Statement
	//! @param string $currentDir Current directory for relative pathing @default[NULL]
	//! @return string Returns line used to import this file
	function getImportStatement($currentDir = NULL) {
		return 'import { ' . $this->getExportClassName() . " } from '" . $this->getImportableFilename($currentDir) . "';";
	}


	//! Add Explicit Import
	//! @param string $src Source containing the class to import
	//! @param string|array $jsClass JS/TS Class(es) to import
	function addImport($src, $jsClass) {
		if(!isset($this->mImports[$src])) $this->mImports[$src] = array();
		$this->mImports[$src] = array_merge($this->mImports[$src], (array)$jsClass);
	}


	//! Adjust Settings for Module
	function configure() {

	}

	//! Generate Package Files
	function generate() {

		if($fh = fopen($this->mPath . $this->mFilename, 'w')) {

			fclose($fh);
		}
	
	}

	//! Create Formatter Object
	//! Primarily used to deal with NS Aliasing issues
	//! @param string $filename Filename or NULL to used $this->mFilename @default[NULL]
	function createFormatter($filename = NULL) {
		if(empty($filename)) {
			if(empty($this->mPath)) $this->mPath = \ng2Kickoff\BASE_DIR;
			$filename = $this->mPath . $this->mFilename;
		}
		return new Formatter($filename);
	}




	//! Detect Required Modules for File
	//! Add required modules to parent or skips if parent is not a module
	//! @return void
	function detectRequiredModules() {
		if(empty($this->mImports) || !($this->cParent instanceof Ng2Module)) return;

		foreach($this->mImports as $src => $classes) {
			$import = array();
			switch($src) {		
				case '@angular/core':
					// skip everything
					break;

				case '@angular/common':
					// skip everything
					break;

				case '@angular/forms':
					if(!empty($classes)) $imports = array('FormsModule');
					break;

				case '@angular/http':
					if(!empty($classes)) $imports = array('HttpModule');
					break;

				case '@angular/router':
					// skip everything
					break;
		
			}		
			if(!empty($import)) {
				$this->cParent->addImport($src, $imports);
			}
		}

	}


	//! Adds Import Line to Formatter
	//! @param Formatter $formatter Formatter object
	//! @param string $src Source
	//! @param array $classes Classes array
	function buildImportLine($formatter, $src, $classes) {
		// make sure each class is only listed once
		$classes = array_unique($classes);
		if(array_key_exists(0, $classes) && $classes[0] == NULL) {
			// incase entire file should be imported
			$formatter->addLine("import '$src';");		
		} elseif($src == '@') {
			// determine location of source



		} else {
			$formatter->addLine('import { ' . implode(', ', $classes) . " } from '$src';");
		}
	}








}






