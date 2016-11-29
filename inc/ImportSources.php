<?php
/******************************************************
 *	@version 1.0.0
 *	@file ng2-kickoff/inc/ImportSources.php
 *	@dependents none
 *	@author Anthony Green
 *	@copyright Anthony Green
 *	@license GPL
 *
 ******************************************************
 *
 *	Object for tracking all Importable Classes
 *
 *
 *
 *
 *
 *
 *
 */
namespace ng2Kickoff\inc;

class ImportSources {

	//! Package Directory
	//! @access protected
	//! @var string
	protected $mPackageDir;


	//! Sources of Importable Classes
	//! @access protected
	//! @var array
	protected $mClassSources = array(
									
									'NgModule'=>'@angular/core',
									'Component'=>'@angular/core',
									'OnInit'=>'@angular/core',
									'OnChanges'=>'@angular/core',
									'OnDestroy'=>'@angular/core',
									'Injector'=>'@angular/core',
									
									'BrowserModule'=>'@angular/platform-browser',
									'FormsModule'=>'@angular/forms',
									'HttpModule'=>'@angular/http',
									'RouterModule'=>'@angular/router',
									'Routes'=>'@angular/router',
									
									
									
									
									
									
									
									
									
									);




	//! Constructor
	//! @param array $settings Associative array of settings
	//! @return void
	function __construct($packageDir) {
		$this->mPackageDir = $packageDir;
	}



	//! Register Local Import Source Path
	//! @param string $class Class name
	//! @param string $src Source path relative to package directory
	function registerImportSource($class, $src) {
		$this->mClassSources[$class] = $src;
	}

	//! Determine Import Source Path
	//! @param string $class Class name
	//! @param string|NULL $curDir Current directory or NULL for relative to current directory
	function determineImportSource($class, $curDir = NULL) {
		if(!isset($this->mClassSources[$class])) return FALSE;

		// if angular return direct path
		if(strpos($this->mClassSources[$class], '@angular') === 0) return $this->mClassSources[$class];

		// determine relative path
		$path = $this->mClassSources[$class];
		if($curDir) {
			// adjust for current directory


		}
		return $path;
	}






	
}