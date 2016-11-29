<?php
/******************************************************
 *	@version 1.0.0
 *	@file ng2-kickoff/inc/Core.php
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
namespace ng2Kickoff\inc;

class Core {

	//! Options
	//! @access protected
	//! @default array
	//! @var array
	protected $mOptions = array();


	//! Application Framework
	//! @access pr
	//! @var Ng2App
	protected $cApp;

	//! Package Object
	//! @access protected
	//! @var Package
	protected $cPackage;

	//! Sources Manager
	//! @access protected
	//! @var Package
	protected $cImportSources;


	//! Template SimpleXML Object
	//! @access protected
	//! @var SimpleXMLElement
	protected $cXML;

	//! Available Command Line Short Options
	//! @access static
	//! @var string 
	static public $cliOptions = 'f:nvs';

	//! Available Command Line Long Options
	//! @access static
	//! @var array
	static public $cliLongOptions = array('packagedir::', 'new', 'version', 'skip-archiving');

	//! Constructor
	//! @param array $options Command Line Options
	//1 @return void
	function __construct($options) {

		$this->mOptions['package_dir'] = (!empty($options['packagedir'])) ? $options['packagedir'] : 'package';	
		$this->mOptions['template_file'] = (!empty($options['f'])) ? $options['f'] : '';

		$this->mOptions['new_package'] = (isset($options['n'])) ? TRUE : (isset($options['new']) ? : FALSE);	
		$this->mOptions['version_package'] = (isset($options['v'])) ? TRUE : (isset($options['version']) ? : FALSE);	
		$this->mOptions['archive_package'] = (isset($options['s'])) ? FALSE : (isset($options['skip-archiving']) ? FALSE : TRUE);

 		spl_autoload_extensions('.php');
		spl_autoload_register(array($this, 'autoload'));

		// make sure package_dir is not a restricted directory
		if(in_array(strtolower($this->mOptions['package_dir']), array('inc'))) {
			throw new Exception('Error in package directory name. The "' . $this->mOptions['package_dir'] . '" is restricted for usage by ng2Kickoff only.');
		}

		$this->cImportSources = new ImportSources($this->mOptions['package_dir']);
	}



	//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
	//! 	Accessor Functions
	//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!

	//! Get Option
	//! Options are set on initialiation of script
	//! @param string $opt Option name
	//! @return mixed Returns option value
	function getOption($opt) {
		return $this->mOptions[$opt];
	}



	//! Get Package Name
	//! @return string Package name
	function getProjectName() {
		return $this->cPackage->getProjectName();
	}

	//! Get Package File Name
	//! @return string Package file name
	function getPackageFilename() {
		return $this->cPackage->getPackageFilename();
	}


	//! Register Local Import Source Path
	//! @param string $class Class name
	//! @param string $src Source path relative to package directory
	function registerImportSource($class, $src) {
		$this->cImportSources->registerImportSource($class, $curDir);
	}

	//! Determine Import Source Path
	//! @param string $class Class name
	//! @param string|NULL $curDir Current directory or NULL for relative to current directory
	function determineImportSource($class, $curDir = NULL) {
		return $this->cImportSources->determineImportSource($class, $curDir);
	}



	//! Get Default Module Directory
	//! @return string Current Module Directory
	function getModuleDirectory() {
		return \ng2Kickoff\BASE_DIR . $this->mOptions['package_dir'];
	}


	//! Get App Selector
	//! @return string Returns selector of base app module OR 'app' on default
	function getAppSelector() {
		if($this->cApp) return $this->cApp->getSetting('selector');
		return 'app';
	}







	//!	Parse Template
	//! Determines if template file exists and generally ok
	//! Creates Package object and loads with general info
	//! @return bool Returns status of parsing template
	function parseTemplate() {
		if(empty($this->mOptions['template_file'])) return FALSE;
		if(!file_exists(\ng2Kickoff\BASE_DIR . $this->mOptions['template_file'])) return FALSE;
	
		// determine if XML is valid
		$this->cXML = \simplexml_load_file(\ng2Kickoff\BASE_DIR . $this->mOptions['template_file']);	
	
		$packageSettings = array();
		$packageXml = $this->cXML->package;

		$packageSettings = current($packageXml->attributes());

		foreach($packageXml->children() as $nodeName => $nodeObj) {
			if($nodeName == 'files') continue;
			$packageSettings[$nodeName] = strval($nodeObj);
		}

		// load package object
		$this->cPackage = new Package($packageSettings);
	
		if(isset($packageXml->files)) {
			// load files
			foreach($packageXml->files->children() as $nodeName => $nodeObj) {
				$nodeAttrs = current($nodeObj->attributes());
				$this->cPackage->setFile(strval($nodeName), $nodeAttrs['src'], strval($nodeObj));
			}
		}
	
		return TRUE;
	}



	//!	Generate Angular Objects
	//! Full parsing of template occurs here
	//! @return bool Returns status of generating objects
	function generateNg2Objects() {
		$this->cApp = $this->buildModuleObj($this->cXML->module);
		$this->cApp->configure();
		return TRUE;
	}

	//!	Generate Angular Objects
	//! Full parsing of template occurs here
	//! @return bool Returns status of generating objects
	function createSourceDirectory() {
		if(empty($this->mOptions['package_dir']) || strpos($this->mOptions['package_dir'], '..') !== FALSE) return FALSE;
		$packageDir = \ng2Kickoff\BASE_DIR . $this->mOptions['package_dir'];
		
		// deal with overwriting and versioning ($this->mOptions['version_package'])


		if($this->mOptions['new_package']) {
			// delete any old version of package
			if(file_exists($packageDir)) {
				if(!is_dir($packageDir)) throw new Exception('File exists where package directory should be');
				$this->rmdir($this->mOptions['package_dir']);
			}
		}
	
		return mkdir($packageDir, 0777, TRUE);
	}

	//!	Generate Files from the Angular Objects
	//! @return bool Returns status of generating objects
	function createSourceFiles() {
		if(empty($this->mOptions['package_dir']) 
				|| !file_exists(\ng2Kickoff\BASE_DIR . $this->mOptions['package_dir'])
				|| !is_dir(\ng2Kickoff\BASE_DIR . $this->mOptions['package_dir'])
				) {
			return FALSE;
		}

		$this->cApp->generate();
		$this->cPackage->generate(\ng2Kickoff\BASE_DIR . $this->mOptions['package_dir']);
		return TRUE;
	}

	//!	Package 
	//! Package all the source files into a tar.gz file
	//! @return bool Returns status of generating objects
	function packageSourceFiles() {
		if(!$this->mOptions['archive_package']) return TRUE;

		$archive = new \PharData(\ng2Kickoff\BASE_DIR . $this->getPackageFilename());
		$archive->buildFromDirectory(\ng2Kickoff\BASE_DIR . $this->mOptions['package_dir']);

		$archive->compress(\Phar::GZ);

		unset($archive);
		return unlink(\ng2Kickoff\BASE_DIR . $this->getPackageFilename());
	}







	//! Class Autoloader
	//! @param string $class Fully-qualified class name (including namespaces)
	function autoload($class) {
		if(strpos($class, 'ng2Kickoff\\') !== 0) return FALSE;
		include_once(\ng2Kickoff\BASE_DIR . str_replace('\\', DIRECTORY_SEPARATOR, substr($class, 11) . '.php'));
	}


	//! Removes a local directory
	//! Recursively removes all contents also
	//! @param string $dir Directory relative to \ng2Kickoff\BASE_DIR
	function rmdir($dir) {
		if(empty($dir) || !is_dir(\ng2Kickoff\BASE_DIR . $dir)) return FALSE;
		$dirCts = scandir(\ng2Kickoff\BASE_DIR . $dir);

		foreach($dirCts as $file) {
			if($file == '..' || $file == '.') continue;
			if(is_dir(\ng2Kickoff\BASE_DIR . "$dir/$file")) {
				$this->rmdir("$dir/$file");
			} else {
				unlink(\ng2Kickoff\BASE_DIR . "$dir/$file");
			}
		}
		rmdir(\ng2Kickoff\BASE_DIR . $dir);
	}

	private function buildModuleObj($moduleXml, $parentObj = NULL) {
		if(!($moduleXml instanceof \SimpleXMLElement)) return FALSE;

		$parser = new parsers\ModuleParser($moduleXml);
		return $parser->parse($this);
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









