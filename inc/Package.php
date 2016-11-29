<?php
/******************************************************
 *	@version 1.0.0
 *	@file ng2-kickoff/inc/Package.php
 *	@dependents none
 *	@author Anthony Green
 *	@copyright Anthony Green
 *	@license GPL
 *
 ******************************************************
 *
 *	Package Object Declaration
 *	->Manages all the general details of the project
 *
 *
 *
 *	Manages the creationg of the following files
 *		/package.json
 *		/systemjs.config.js
 *		/tsconfig.json
 *		/bs-config.js
 *		/index.html
 *		/styles.css
 *
 *
 *
 *
 */
namespace ng2Kickoff\inc;

class Package {

	//! Project Name
	//! @access protected
	//! @var string
	protected $mName;

	//! Package Version
	//! @access protected
	//! @var string
	protected $mVersion;

	//! Package Title
	//! @access protected
	//! @var string
	protected $mTitle;

	//! Package Description
	//! @access protected
	//! @var string
	protected $mDescription;

	//! Package Copyright
	//! @access protected
	//! @var string
	protected $mCopyright;

	//! Package License
	//! @access protected
	//! @var string
	protected $mLicense;







	//! Package Files
	//! @access protected
	//! @var array
	protected $mFiles = array();


	//! Package Archive Name
	//! @access protected
	//! @var string
	protected $mArchive;



	//! Constructor
	//! @param array $settings Associative array of settings
	//! @return void
	function __construct($settings) {
		
		if(!empty($settings) && is_array($settings)) {
			foreach($settings as $name => $val) {
				if(!property_exists($this, 'm' . ucfirst($name))) continue;
				$this->{'m' . ucfirst($name)} = $val;
			}
		}

		if(!isset($this->mName)) $this->mName = 'package';
		if(!isset($this->mArchive)) $this->mArchive = $this->mName . '.tar';
	}




	//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
	//! 	Accessor Functions
	//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!

	//! Get Project Name
	//! @return string Project name
	function getProjectName() {
		return $this->mName;
	}

	//! Get Package Name
	//! @return string Project name
	function getPackageName() {
		return $this->mName . (!empty($this->mVersion) ? '-' . $this->mVersion :'');
	}


	//! Get Package File Name
	//! @return string Package file name
	function getPackageFilename() {
		return $this->mArchive;
	}



	//! Set Package File
	//! @param string $name Filename
	//! @param string|NULL $src Source @default[NULL]
	//! @param array|NULL $content Source @default[NULL]
	function setFile($name, $src = NULL, $content = NULL) {
		if($name == 'file') {
			if(empty($src)) return FALSE;
			$name = $src;
		}

		if(!empty($content)) {
			$this->mFiles[$name] = $content;
		} elseif(!empty($src)) {
			$this->mFiles[$name] = '~' . $src;
		} else {
			$this->mFiles[$name] = TRUE;
		}
		return $name;
	}




	//! Generate Package Files
	//! Creates the following files
	//!		/package.json
	//!		/systemjs.config.js
	//!		/tsconfig.json
	//!		/bs-config.js
	//!		/index.html
	//!		/styles.css
	//!
	//! @param string $dir Base dir
	function generate($dir) {
		$this->generatePackageJsonFile($dir);
		$this->generateSystemJSConfigFile($dir);
		$this->generateTSConfigJsonFile($dir);
		$this->generateBSConfigFile($dir);
		$this->generateIndexHtmlFile($dir);
		$this->generateStylesCssFile($dir);

		$this->generateInitializationFile($dir);
	}
	
	//! Generate /package.json File
	//! @param string $dir Base dir
	protected function generatePackageJsonFile($dir) {

		// should be dynamically created based upon used components
		$packageJson = array(
							'name'=>$this->mName,
							'version'=>$this->mVersion,
							'scripts'=>array(
											"start"=>'tsc && concurrently "tsc -w" "lite-server" ',
											"lite"=>"lite-server",
											"tsc"=>"tsc",
											"tsc:w"=>"tsc -w"
											),
							'licenses'=>array(array('type'=>'MIT', 'url'=>'https://github.com/angular/angular.io/blob/master/LICENSE')),
							'dependencies'=>array(
									'angular-in-memory-web-api'=>'~0.1.13',
									'core-js'=>'^2.4.1',
									'reflect-metadata'=>'^0.1.8',
									'rxjs'=>'5.0.0-beta.12',
									'systemjs'=>'0.19.39',
									'zone.js'=>'^0.6.25'
									),
							'devDependencies'=>array(
									'@types/core-js'=>'^0.9.34',
									'@types/node'=>'^6.0.45',
									'concurrently'=>'^3.0.0',
									'lite-server'=>'^2.2.2',
									'typescript'=>'^2.0.3'
									)
							);

		$angularModules = $this->determineAngularModulesUsed();
		foreach($angularModules as $name => $ver) {
			$packageJson['dependencies']['@angular/' . $name] = $ver;
		}

		// create package.json
		$packageJsonFormatter = new Formatter($dir . '/package.json');
		$packageJsonFormatter->addLine(json_encode($packageJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
		$packageJsonFormatter->write();
	
	}


	//! Generate /systemjs.config.js File
	//! @param string $dir Base dir
	protected function generateSystemJSConfigFile($dir) {
		// should be dynamically created based upon used components
		$configJson = array(
							'paths'=>array(
										'npm'=>'node_modules/'
										),
							'map'=>array(
										'app'=>'app',
										'rxjs'=>'npm:rxjs',
										'angular-in-memory-web-api'=>'npm:angular-in-memory-web-api/bundles/in-memory-web-api.umd.js',
										),
							'packages'=>array(
										'app'=>array(
													'main'=>'./main.js',
													'defaultExtension'=>'.js'
													),
										'rxjs'=>array(
													'defaultExtension'=>'.js'
													)
										)
							);

		$angularModules = $this->determineAngularModulesUsed();
		foreach($angularModules as $name => $ver) {
			$configJson['map']['@angular/' . $name] = 'npm:@angular/' . $name . '/bundles/' . $name . '.umd.js';
		}


		// create systemjs.config.js
		$systemJSConfigFormatter = new Formatter($dir . '/systemjs.config.js');
		$systemJSConfigFormatter->addBlock('(function (global) {', '})(this);');
		$systemJSConfigFormatter->addLine('System.config(' . json_encode($configJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . ');');
		$systemJSConfigFormatter->write();
	
	}

	//! Generate /tsconfig.json File
	//! @param string $dir Base dir
	protected function generateTSConfigJsonFile($dir) {
		// should be dynamically created based upon used components
		$tsJson = array('compilerOptions'=>array(
											'target'=>'es5',
											'module'=>'commonjs',
											'moduleResolution'=>'node',
											'sourceMap'=>TRUE,
											'emitDecoratorMetadata'=>TRUE,
											'experimentalDecorators'=>TRUE,
											'removeComments'=>FALSE,
											'noImplicitAny'=>FALSE
											)
						);

		// create tsconfig.json
		$tsConfigFormatter = new Formatter($dir . '/tsconfig.json');
		$tsConfigFormatter->addLine(json_encode($tsJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
		$tsConfigFormatter->write();
	}

	//! Generate /bs-config.js File
	//! @param string $dir Base dir
	protected function generateBSConfigFile($dir) {

	}

	//! Generate /index.html File
	//! @param string $dir Base dir
	protected function generateIndexHtmlFile($dir) {
		global $ng2Kickoff;

		// create index.html formatter
		$indexFormatter = new Formatter($dir . '/index.html');

		$indexFormatter->addBlock('<html>', '</html>');
		$indexFormatter->addBlock('<head>', '</head>');
		$indexFormatter->addLine('<title>' . (!empty($this->mTitle) ? $this->mTitle : $this->mName) . '</title>');
		$indexFormatter->addLine('<base href="/">');
		$indexFormatter->addLine('<meta charset="UTF-8">');
		$indexFormatter->addLine('<meta name="viewport" content="width=device-width, initial-scale=1">');
		$indexFormatter->addLine('<link rel="stylesheet" href="styles.css">');
		$indexFormatter->addLine('<!-- 1. Load libraries -->');
		$indexFormatter->addLine('<!-- Polyfill(s) for older browsers -->');
		$indexFormatter->addLine('<script src="node_modules/core-js/client/shim.min.js"></script>');
		$indexFormatter->addLine('<script src="node_modules/zone.js/dist/zone.js"></script>');
		$indexFormatter->addLine('<script src="node_modules/reflect-metadata/Reflect.js"></script>');
		$indexFormatter->addLine('<script src="node_modules/systemjs/dist/system.src.js"></script>');
		$indexFormatter->addLine('<!-- 2. Configure SystemJS -->');
		$indexFormatter->addLine('<script src="systemjs.config.js"></script>');
		$indexFormatter->addLine('<script>System.import("app").catch(function(err){ console.error(err); });</script>');
		$indexFormatter->closeCurrentBlock();
		
		$indexFormatter->addBlock('<body>', '</body>');

		//! @TODO need to get app's selector

		$appSelector = $ng2Kickoff->getAppSelector();

		$indexFormatter->addLine("<$appSelector></$appSelector>");
		$indexFormatter->write();
	}

	//! Generate /styles.css File
	//! @param string $dir Base dir
	protected function generateStylesCssFile($dir) {

		// create styles.css formatter
		$stylesFormatter = new Formatter($dir . '/styles.css');
		$stylesFormatter->addLine('/* General Master Styles */');

		$stylesFormatter->addBlock('h1 {', '}');
		$stylesFormatter->addLine('color: #369;');
		$stylesFormatter->addLine('font-family: Arial, Helvetica, sans-serif;');
		$stylesFormatter->addLine('font-size: 250%;');
		$stylesFormatter->closeCurrentBlock();

		$stylesFormatter->addBlock('h2, h3 {', '}');
		$stylesFormatter->addLine('color: #444;');
		$stylesFormatter->addLine('font-family: Arial, Helvetica, sans-serif;');
		$stylesFormatter->addLine('font-weight: lighter');
		$stylesFormatter->closeCurrentBlock();

		$stylesFormatter->addBlock('body {', '}');
		$stylesFormatter->addLine('margin: 2em;');
		$stylesFormatter->closeCurrentBlock();

		$stylesFormatter->addBlock('body, input[text], button {', '}');
		$stylesFormatter->addLine('color: #888;');
		$stylesFormatter->addLine('font-family: Cambria, Georgia;');
		$stylesFormatter->closeCurrentBlock();

		$stylesFormatter->write();
	}


	//! Creates the Script Initialization file
	//! File created is 'app/main.js' by default
	//! @TODO Abstract out name allowing for location of app in other directories
	//! @param string $dir Base dir
	protected function generateInitializationFile($dir) {
	
		// create main.js formatter
		$mainFormatter = new Formatter($dir . '/app/main.js');
		$mainFormatter->addLine("import { platformBrowserDynamic } from '@angular/platform-browser-dynamic';");
		$mainFormatter->addLine('');
		$mainFormatter->addLine("import { AppModule } from './app.module';");
		$mainFormatter->addLine('');
		$mainFormatter->addLine("platformBrowserDynamic().bootstrapModule(AppModule);");	
		$mainFormatter->addLine('');
	
		$mainFormatter->write();
	}


	//! Determine Angular Modules Used
	//! @TODO Dynamically determine this based upon actually used modules
	//! @return array Associative array of module names and minimum versions used
	protected function determineAngularModulesUsed() {

		return array('core'=>'~2.1.1',
					'common'=>'~2.1.1',
					'compiler'=>'~2.1.1',
					'forms'=>'~2.1.1',
					'http'=>'~2.1.1',
					'platform-browser'=>'~2.1.1',
					'platform-browser-dynamic'=>'~2.1.1',
					'router'=>'~3.1.1',
					'upgrade'=>'~2.1.1'
					);
	}





	
}