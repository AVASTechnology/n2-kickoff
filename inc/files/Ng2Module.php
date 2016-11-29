<?php
/******************************************************
 *	@version 1.0.0
 *	@file ng2-kickoff/inc/files/Ng2Module.php
 *	@dependents none
 *	@author Anthony Green
 *	@copyright Anthony Green
 *	@license GPL
 *
 ******************************************************
 *
 * Module File Framework
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

class Ng2Module extends Ng2File {



	//! Angular Imports
	//! @access protected
	//! @default array('@angular/core'=>array('NgModule'))
	//! @var array
	protected $mImports = array('@angular/core'=>array('NgModule'));

	//! Router Object
	//! @access protected
	//! @var Ng2Router
	protected $cRouter;

	//! Components Imports
	//! @access protected
	//! @var array
	protected $mComponents = array();

	//! Service Imports
	//! @access protected
	//! @var array
	protected $mServices = array();

	//! Data Object Imports
	//! @access protected
	//! @var array
	protected $mDataObjects = array();

	//! Bootstrapped Components
	//! @access protected
	//! @var array
	protected $mBootstrap = array();

	//! Child Modules
	//! @access protected
	//! @var array
	protected $mChildren = array();


	//! Default Settings
	//! @access protected
	//! @default array('component'=>array(), 'service'=>array(), 'dataobjects'=>array())
	//! @var array
	protected $mDefSettings = array('components'=>array(),
									'services'=>array(),
									'dataobjects'=>array()
									);



	//! Available Settings
	//! @access static
	//! @var arary
	static $settings = array();

	//! Constructor
	//! @return void
	function __construct($parentObj, $settings) {
		parent::__construct($parentObj, $settings, 'ts');

		// create module directory
		$this->mFilename = $this->mSettings['name'] . '.module.' . $this->mFileType;
		if(empty($this->mSettings['export_class'])) $this->mSettings['export_class'] = ucfirst($this->mSettings['name']) . 'Module';

		$this->mSettings['_module_dir'] = $this->mPath . '/' . $this->mSettings['name'];
		$this->mSettings['_module_css_dir'] = $this->mSettings['_module_dir'] . (($this->mSettings['styles']) ? '/css' : '');
		$this->mSettings['_module_templates_dir'] = $this->mSettings['_module_dir'] . (($this->mSettings['templates']) ? '/templates' : '');

		$this->mPath = $this->mSettings['_module_dir'] . '/';
	}

	//! Get Default Settings
	//! @param enum['components'|'services'|'dataobjects'] $section Section
	//! @param string|NULL $setting Setting name or NULL for all settings in section
	//! @return mixed Setting(s)
	function getDefaultSettings($section, $setting = NULL) {
		if(!isset($this->mDefSettings[$section])) return NULL;
		if(isset($setting)) return $this->mDefSettings[$section][$setting];
		return $this->mDefSettings[$section];
	}

	//! Add Default Settings
	//! @param enum['components'|'services'|'dataobjects'] $section Section
	//! @param array $settings Associative array of default settings
	//! @return array New default settings for section
	function addDefaultSettings($section, $settings) {
		if(!isset($this->mDefSettings[$section]) || !is_array($settings)) return NULL;
		return ($this->mDefSettings[$section] = array_merge($this->mDefSettings[$section], $settings));
	}


	//! Add Router
	//! @param Ng2Router $router Router object
	function addRouter($router) {
		if($router instanceof Ng2Router) {
			$this->cRouter = $router;
		}
	}



	//! Add Component
	//! @param Ng2Component $component Component object
	function addComponent($component) {
		if($component instanceof Ng2Component) {
			$this->mComponents[$component->getName()] = $component;
			if($component->getSetting('bootstrap')) {
				$this->mBootstrap[] = $component->getName();
			}
		}
	}

	//! Add Service
	//! @param Ng2Service $component Component object
	function addService($service) {
		if($service instanceof Ng2Service) {
			$this->mServices[$service->getName()] = $service;
		}
	}


	//! Add Data Object
	//! @param Ng2DataObject $dataObject Data Object object
	function addDataObject($dataObject) {
		if($dataObject instanceof Ng2DataObject) {
			$this->mDataObjects[$dataObject->getName()] = $dataObject;
		}
	}


	//! Add Bootstrapped Component
	//! @param Ng2Component $component Component object
	function addBootstrapComponent($component) {
		if($component instanceof Ng2Component && isset($this->mComponents[$component->getName()])) {
			$this->mBootstrap[] = $component->getName();
		}
	}


	//! Add Child modules	
	//! @param Ng2Module $moduleObj Child module object
	function addChildModules($moduleObj) {
		if($moduleObj instanceof Ng2Module) {
			$this->mChildren[$moduleObj->getName()] = $moduleObj;
		}
	}




	//! Get Component
	//! @param string $name Component name
	//! @param bool $exportableName Use exportable name @default[FALSE]
	//! @success Ng2Component Returns component object
	//! @failure FALSE Returns FALSE on failure
	function getComponent($name, $exportableName = FALSE) {
		if($exportableName) {
			foreach($this->mComponents as $comp) {
				if($comp->getExportClassName() == $name) return $comp;
			}
		} elseif(isset($this->mComponents[$name])) {
			return $this->mComponents[$name];
		}
		return FALSE;
	}

	//! Get Router
	//! @success Ng2Router Returns router object
	//! @failure FALSE Returns FALSE if no router is set
	function getRouter() {
		if(isset($this->cRouter)) return $this->cRouter;
		return FALSE;
	}

	//! Get Service
	//! @param string $name Service name
	//! @param bool $exportableName Use exportable name @default[FALSE]
	//! @success Ng2Service Returns service object
	//! @failure FALSE Returns FALSE on failure
	function getService($name, $exportableName = FALSE) {
		if($exportableName) {
			foreach($this->mServices as $service) {
				if($service->getExportClassName() == $name) return $service;
			}
		} elseif(isset($this->mServices[$name])) {
			return $this->mServices[$name];
		}
		return FALSE;
	}
	

	//! Get Data Object
	//! @param string $name Data object name
	//! @param bool $exportableName Use exportable name @default[FALSE]
	//! @success Ng2DataObject Returns data object
	//! @failure FALSE Returns FALSE on failure
	function getDataObject($name, $exportableName = FALSE) {
		if($exportableName) {
			foreach($this->mDataObjects as $dataObj) {
				if($dataObj->getExportClassName() == $name) return $dataObj;
			}
		} elseif(isset($this->mDataObjects[$name])) {
			return $this->mDataObjects[$name];
		}
		return FALSE;
	}



	//! Get Current Module Directory
	//! @return string Current Module Directory
	function getModuleDirectory() {
		return $this->mPath;
	}



	//! Create default directories for a module
	//! @access static
	//! @param string $dir Base directory
	//! @param string $name Module name
	function createModuleDirectories($dir, $name) {


	
	
	
	
	}

	//! Adjust Settings for Module
	function configure() {
		// generate child files
		if($this->cRouter) $this->cRouter->generate($this->mPath);

		if(!empty($this->mComponents)) {
			foreach($this->mComponents as $comp) {
				$comp->configure($this->mSettings['_module_templates_dir'] .'/', $this->mSettings['_module_css_dir'] .'/');
			}
		}

		if(!empty($this->mServices)) {
			foreach($this->mServices as $service) {
				$service->configure();
			}
		}

		if(!empty($this->mDataObjects)) {
			foreach($this->mDataObjects as $dataObj) {
				$dataObj->configure();
			}
		}
	
		if(!empty($this->mChildren)) {
			foreach($this->mChildren as $childModules) {
				$childModules->configure();
			}
		}
	}



	//! Generate Module Files
	function generate() {

		// create module directories
		mkdir($this->mSettings['_module_dir']);
		if($this->mSettings['_module_dir'] != $this->mSettings['_module_css_dir']) mkdir($this->mSettings['_module_css_dir']);
		if($this->mSettings['_module_dir'] != $this->mSettings['_module_templates_dir']) mkdir($this->mSettings['_module_templates_dir']);

		$decoratorClasses = array('imports'=>array(), 'declarations'=>array(), 'providers'=>array());

		// generate child files
		if($this->cRouter) $this->cRouter->generate($this->mPath);

		if(!empty($this->mComponents)) {
			foreach($this->mComponents as $comp) {
				$comp->generate($this->mPath, $this->mSettings['_module_templates_dir'] .'/', $this->mSettings['_module_css_dir'] .'/');
			}
		}

		if(!empty($this->mServices)) {
			foreach($this->mServices as $service) {
				$service->generate($this->mPath);
			}
		}

		if(!empty($this->mDataObjects)) {
			foreach($this->mDataObjects as $dataObj) {
				$dataObj->generate($this->mPath);
			}
		}



		$formatterObj = $this->createFormatter($this->mSettings['_module_dir'] . '/' . $this->mFilename);

		//! @TODO Add File Header details

		// add angular imports
		$formatterObj->addLine('// Angular Imports');
		foreach($this->mImports as $src => $classes) {
			// skip NgModule incase of duplication
			$this->buildImportLine($formatterObj, $src, $classes);

			if($src == '@angular/core') $classes = array_filter($classes, function($v) { return !($v == 'NgModule'); });
			if(empty($classes)) continue;
			$decoratorClasses['imports'] = array_merge($decoratorClasses['imports'], $classes);
		}

		$formatterObj->addLine('');

		// add routing imports
		if($this->cRouter) {
			$formatterObj->addLine('// Router');
			$formatterObj->addLine($this->cRouter->getImportStatement());
			$decoratorClasses['imports'][] = $this->cRouter->getExportClassName();
			$formatterObj->addLine('');
		}


		$formatterObj->addLine('// Component Imports');
		foreach($this->mComponents as $name => $comp) {
			$formatterObj->addLine($comp->getImportStatement());
			$decoratorClasses['declarations'][] = $comp->getExportClassName();
		}
		$formatterObj->addLine('');

		if(!empty($this->mServices)) {
			$formatterObj->addLine('// Service Imports');
			foreach($this->mServices as $name => $service) {
				$formatterObj->addLine($service->getImportStatement());
				$decoratorClasses['providers'][] = $service->getExportClassName();
			}
			$formatterObj->addLine('');
		}

		if(!empty($this->mDataObjects)) {
			$formatterObj->addLine('// Data Object Imports');
			foreach($this->mDataObjects as $name => $dataObj) {
				$formatterObj->addLine($dataObj->getImportStatement());
			}
			$formatterObj->addLine('');
		}


		// probably need to add child modules here



		// @NgModule Decorator
		$formatterObj->addBlock('@NgModule({', '})');

		// imported classes
		foreach($decoratorClasses as $sec => $classes) {
			$formatterObj->addArray($classes, "$sec: [", '],');
			$formatterObj->addLine('');
		}

		// bootstrap classes
		$formatterObj->addBlock('bootstrap: [', ']');
		if(!empty($this->mBootstrap)) {
			$end = count($this->mBootstrap) - 1;
			for($i=0; $i<=$end; $i++) {
				$formatterObj->addLine($this->mComponents[$this->mBootstrap[$i]]->getExportClassName() . ($i == $end ? '' : ','));
			}
		}
		$formatterObj->closeCurrentBlock();

	
		// close @NgModule
		$formatterObj->closeCurrentBlock();
		$formatterObj->addLine('');
		$formatterObj->addLine('export class ' . $this->getExportClassName() . ' { }');
	}





}





