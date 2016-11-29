<?php
/******************************************************
 * @
 * @package
 * @copywrite Anthony Green, AVAS Partners, LLC
 * @
 *
 *
 ******************************************************
 *
 *
 *
 *
 *	Workflow
 *	1) Read template from command line
 *	2) Generate Object structure
 *	3) Generate tmp directory
 *	4) Construct Files
 *	5) Compress tmp directory
 *	6) Report on completed boilerplate
 *
 *
 *
 *
 *
 *
 */
namespace ng2Kickoff;

const BASE_DIR = __DIR__ . '/'; 

require_once(BASE_DIR . 'inc/Core.php');

// die if not command line
if(php_sapi_name() != 'cli' || !empty($_SERVER['REMOTE_ADDR'])) {
	die('ng2Kickoff is available on the Command Line Interface only');
}

//verify SimpleXML is loaded
if(!extension_loaded('SimpleXML')) {
	throw new \Exception('SimpleXML extension is required. Try installing "php-xml".');
}

// determine structure file
$ng2Kickoff = new inc\Core(getopt(inc\Core::$cliOptions, inc\Core::$cliLongOptions));

if(!$ng2Kickoff->parseTemplate()) {
	throw new \Exception('Template could not be parsed');
}

if(!$ng2Kickoff->generateNg2Objects()) {
	throw new \Exception('Angular2 objects could not be generated');
}

if(!$ng2Kickoff->createSourceDirectory()) {
	throw new \Exception('Source directory could not be created');
}

if(!$ng2Kickoff->createSourceFiles()) {
	throw new \Exception('Source files could not be created');
}

if(!$ng2Kickoff->packageSourceFiles()) {
	throw new \Exception('Source files could not be packaged');
}

if($archiveName = $ng2Kickoff->getPackageFilename()) {
	print('Project ' . $ng2Kickoff->getProjectName() . " packaged into file $archiveName\n");
} else {
	print('Project ' . $ng2Kickoff->getProjectName() . " generated\n");
}

