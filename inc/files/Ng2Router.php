<?php
/******************************************************
 *	@version 1.0.0
 *	@file ng2-kickoff/inc/files/Ng2Router.php
 *	@dependents none
 *	@author Anthony Green
 *	@copyright Anthony Green
 *	@license GPL
 *
 ******************************************************
 *
 * Router File Framework
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

class Ng2Router extends Ng2File {



	//! Route Details
	//! @access protected
	//! @var array
	protected $mRoutes = array();

	//! Angular Imports
	//! @access protected
	//! @default array(''@angular/core'=>array('NgModule'), '@angular/router'=>array('RouterModule', 'Routes'))
	//! @var array
	protected $mImports = array('@angular/core'=>array('NgModule'),
								'@angular/router'=>array('RouterModule', 'Routes')
								);


	//! Constructor
	//! @return void
	function __construct($parentObj, $settings) {
		parent::__construct($parentObj, $settings, 'ts');

		if(empty($this->mSettings['name'])) $this->mSettings['name'] = $parentObj->getName() . '-routing';

		$this->mFilename = $this->mSettings['name'] . '.module.' . $this->mFileType;

		if(empty($this->mSettings['export_class'])) {
			$this->mSettings['export_class'] = implode('', array_map('ucfirst', explode('-', $this->mSettings['name']))) . 'Module';
		}
		
	}




	//! Add Route
	//! @param string $path Route path
	//! @param array $params Route parameters
	function addRoute($path, $params = array()) {
		$this->mRoutes[$path] = $params;
	}






	//! Generate Module Files
	function generate() {

		$routes = array();
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

		// add component imports
		$formatterObj->addLine('// Component Imports');

		// add relavant Components
		foreach($this->mRoutes as $path => $params) {
			if(!empty($params['component'])) {
				if($comp = $this->cParent->getComponent($params['component'])) {
					$formatterObj->addLine($comp->getImportStatement($currentDir));
				} elseif($comp = $this->cParent->getComponent($params['component'], TRUE)) {
					$formatterObj->addLine($comp->getImportStatement($currentDir));
				}
			}
			if(!isset($params['path'])) $params['path'] = $path;
			$routes[] = json_encode($params);
		}

		$formatterObj->addBlock('const routes: Routes = [', '];');
		if(!empty($routes)) {
			$end = count($routes) - 1;
			for($i=0; $i<=$end; $i++) {
				$formatterObj->addLine($routes[$i] . ($i == $end ? '' : ','));
			}
		}
		$formatterObj->closeCurrentBlock();
		$formatterObj->addLine('');


		// @NgModule Decorator
		$formatterObj->addBlock('@NgModule({', '})');
		$formatterObj->addLine('imports: [ RouterModule.forRoot(routes) ],');
		$formatterObj->addLine('exports: [ RouterModule ]');
		$formatterObj->closeCurrentBlock();
		
		// close @NgModule
		$formatterObj->closeCurrentBlock();
		$formatterObj->addLine('');
		$formatterObj->addLine('export class ' . $this->getExportClassName() . ' { }');


	}




}





