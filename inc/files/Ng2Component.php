<?php
/******************************************************
 *	@version 1.0.0
 *	@file ng2-kickoff/inc/files/Ng2Component.php
 *	@dependents none
 *	@author Anthony Green
 *	@copyright Anthony Green
 *	@license GPL
 *
 ******************************************************
 *
 * Component File Framework
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

class Ng2Component extends Ng2File {


	//! Component Properties
	//! @access protected
	//! @var array
	protected $mProperties = array();

	//! Angular Imports
	//! @access protected
	//! @default array('@angular/core'=>array('Component'))
	//! @var array
	protected $mImports = array('@angular/core'=>array('Component'));


	//! Available Settings
	//! @access static
	//! @var arary
	static $settings = array('selector'=>'', 'templateUrl'=>'', 'template'=>'', 'styleUrls'=>array(), 'bootstrap'=>FALSE);


	//! Constructor
	//! @return void
	function __construct($parentObj, $settings) {
		parent::__construct($parentObj, $settings, 'ts');

		if(empty($this->mSettings['name'])) $this->mSettings['name'] = $parentObj->getName();

		$this->mFilename = $this->mSettings['name'] . '.component.' . $this->mFileType;

		if(empty($this->mSettings['export_class'])) {
			$this->mSettings['export_class'] = implode('', array_map('ucfirst', explode('-', $this->mSettings['name']))) . 'Component';
		}
		
		if(empty($this->mSettings['selector'])) $this->mSettings['selector'] = "'" . $this->mSettings['name'] . "'";
		if(empty($this->mSettings['moduleId'])) $this->mSettings['moduleId'] = 'module.id';

		if(empty($this->mSettings['templateUrl'])) {
			$this->mSettings['templateUrl'] = $this->mSettings['name'] . ".component.html";
		}

		$this->mSettings['styleUrls'] = (array)$this->mSettings['styleUrls'];
		if(empty($this->mSettings['styleUrls'])) {
			$this->mSettings['styleUrls'] = array($this->mSettings['name'] . ".component.css");
		}
	}




	//! Add Component Properties
	//! @param array $properties Associative array of properties to add
	function addProperties($properties) {
		if(!empty($properties) && is_array($properties)) {
			$this->mProperties = array_merge($this->mProperties, $properties);
		}
	}





	//! Adjust Settings for Module
	function configure($templateDir = NULL, $stylesDir = NULL) {
		$this->mSettings['template_dir'] = ($templateDir) ?: $this->mPath;
		if(!empty($templateDir) && $templateDir != $this->mPath) {
			$this->mSettings['templateUrl'] = str_replace($this->mPath, '', $templateDir) . $this->mSettings['templateUrl'];
		}

		$this->mSettings['styles_dir'] = ($stylesDir) ?: $this->mPath;
		$this->mSettings['styleUrls'] = array_filter($this->mSettings['styleUrls']);
		if(!empty($stylesDir) && $stylesDir != $this->mPath && !empty($this->mSettings['styleUrls'])) {
			$relDir = str_replace($this->mPath, '', $stylesDir);
			$this->mSettings['styleUrls'] = array_map(function($v) use ($relDir) { return $relDir . $v; }, $this->mSettings['styleUrls']);
		}	

		if(!empty($this->mSettings['implements'])) {
			$implementedClasses = array_intersect($this->mSettings['implements'], array('OnInit'));
			if(!empty($implementedClasses)) $this->addImport('@angular/core', $implementedClasses);
		}

		// Make sure Module contains everything necessary to run
		$this->detectRequiredModules();
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
		$formatterObj->addLine('');

		// add app imports
		$formatterObj->addLine('// App Imports');
		$formatterObj->addLine('');



		// @Component Decorator
		$formatterObj->addBlock('@Component({', '})');

		$formatterObj->addLine('selector: ' . $this->mSettings['selector'] . ',');
		$formatterObj->addLine('moduleId: ' . $this->mSettings['moduleId'] . ',');
		$formatterObj->addLine("templateUrl: '" . $this->mSettings['templateUrl'] . "',");

		$formatterObj->addLine("styleUrls: ['" . implode("', '", $this->mSettings['styleUrls']) . "']");
		$formatterObj->closeCurrentBlock();
		$formatterObj->addLine('');


		// Export Component Class
		$implements = (!empty($this->mSettings['implements'])) ? ' implments ' . implode(', ', $this->mSettings['implements']) : '';

		$formatterObj->addBlock('export class ' . $this->getExportClassName() . "$implements {", '}');
		// add Component properties
		foreach($this->mProperties as $prop => $declaration) {
			$line = $prop;
			if(is_array($declaration)) {
				if(is_array($declaration['args'])) {
					$line .= '(' . implode(', ', $declaration['args']) . ')';
				}

				if(isset($declaration['return'])) $line .= ": $declaration[return]";

				if(isset($declaration['args'])) {
					$line .= '{ ' . $declaration['body'] . ' }';
				} else {
					$line .= !empty($declaration['body']) ? " = $declaration[body];" : ';';
				}
			} else {
				$line .= " = $declaration;";
			}
			$formatterObj->addLine($line);
			$formatterObj->addLine('');
		}
		$formatterObj->closeCurrentBlock();

		// add Component files
		if(!file_exists($this->mPath . $this->mSettings['templateUrl'])) {
			$templateHtml = $this->mSettings['template'];
			if(empty($templateHtml)) {
				$templateHtml = '<div>Template for ' . $this->mSettings['name'] . ' Component</div>';
			}
			file_put_contents($this->mPath . $this->mSettings['templateUrl'], $templateHtml);
		}



		foreach($this->mSettings['styleUrls'] as $styleUrl) {
			if(!file_exists($this->mPath . $styleUrl)) {
				file_put_contents($this->mPath . $styleUrl, "\n/* Styles for " . $this->mSettings['name'] . " Component */\n" . $this->mSettings['styles'] . "\n");
			}
		}




	}






	//! Detect Required Modules for Component
	//! Add required modules to parent
	//! @return void
	function detectRequiredModules() {
		// make sure all included core modules are included
		parent::detectRequiredModules();


		if(!empty($this->mSettings['template'])) {
			if(strpos($this->mSettings['template'], 'ngModel')) {
				// FormsModule needed
				$this->cParent->addImport('@angular/forms', 'FormsModule');
			}




		}
	
	
	}









}


