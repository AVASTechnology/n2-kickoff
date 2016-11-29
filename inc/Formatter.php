<?php
/******************************************************
 *	@version 1.0.0
 *	@file ng2-kickoff/inc/Formatting.php
 *	@dependents none
 *	@author Anthony Green
 *	@copyright Anthony Green
 *	@license GPL
 *
 ******************************************************
 *
 *	Manages general formatting of files
 *
 *	Primarily a wrapper function of 
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

class Formatter {

	//! Overwrite current content
	//! @access protected
	//! @var bool
	protected $mOverwrite;

	//! File name
	//! @access protected
	//! @var string
	protected $mFilename;

	//! Content Array
	//! @access protected
	//! @var array
	protected $mContent = array();

	//! Current Blocks
	//! Array of references to current blocks of content
	//! Last element is the current block
	//! @access protected
	//! @var &array
	protected $mCurrentBlock = array();

	//! Block Closures
	//! Characters to insert upon closign block
	//! @access protected
	//! @var array
	protected $mBlockClosures = array();

	//! Single Indent Character(s)
	//! @access static
	//! @default "\t"
	//! @var string
	static public $indent = "\t";

	//! Single Indent Character(s)
	//! @access static
	//! @default "\t"
	//! @var string
	static public $line_return = "\n";


	//! Constructor
	//! @param string $filename File name where content will be wrote
	function __construct($filename, $overwrite = TRUE) {
		$this->mFilename = $filename;
		$this->mOverwrite = $overwrite;
		
		$this->mCurrentBlock[0] = &$this->mContent;
	}

	//! Desctructor
	//! Makes sure content is written to file
	function __destruct() {
		$this->write();
	}


	//! Write Content to file
	//! @return bool Status of writing file
	function write() {
		if(empty($this->mContent)) return FALSE;
		$mode = $this->mOverwrite ? 'w' : 'a';

		if(!($fh = fopen($this->mFilename, $mode))) {
			// Error
			return FALSE;
		}

		// close all current blocks
		$this->closeCurrentBlock(count($this->mCurrentBlock));

		$this->writeBlock($fh, $this->mContent, 0);

		// reset content
		$this->mContent = array();
		$this->mCurrentBlock = array();
		$this->mCurrentBlock[0] = &$this->mContent;

		fclose($fh);	

		$this->mOverwrite = FALSE;
		return TRUE;
	}

	//! Write a Block of Content
	//! @access protected
	//! @param resource $fh File handle
	//! @param array $block Block of content
	//! @param int $lvl Level of indentation for block @default[0]
	protected function writeBlock($fh, $block, $lvl = 0) {
		$indent = str_repeat(static::$indent, $lvl);
		if(is_array($block)) {
			foreach($block as $content) {
				if(is_array($content)) {
					$this->writeBlock($fh, $content, $lvl +1);
				} else {
					fwrite($fh, $indent . $content . static::$line_return);
				}
			}
		} else {
			//! This is actually an error situation so indention might be wrong
			fwrite($fh, $indent . $content . static::$line_return);
		}
	}






	//! Add Line of Content
	//! @param string $line Line of content
	//! @param bool $currentBlock Use current block (TRUE) or move up to parent block (FALSE)
	function addLine($line, $currentBlock = TRUE) {
		if(!$currentBlock) {
			$this->closeCurrentBlock();
		}

		//! @TODO Look for a more efficient way of doing this
		end($this->mCurrentBlock);
		$key = key($this->mCurrentBlock);
		$this->mCurrentBlock[$key][] = $line;
	}


	//! Add Block of Content
	//! @param string $line Line starting new block
	//! @param string $closure Characters to insert when closing the block
	function addBlock($line, $closure = '') {
		$this->addLine($line, TRUE);
		$this->mBlockClosures[] = $closure;

		end($this->mCurrentBlock);
		$key = key($this->mCurrentBlock);
		$this->mCurrentBlock[$key][] = array();

		end($this->mCurrentBlock[$key]);
		$bKey = key($this->mCurrentBlock[$key]);


		$this->mCurrentBlock[] = &$this->mCurrentBlock[$key][$bKey];
	}


	//! Add Block of Content as a JS Array
	//! @param string() $lines Lines of content to add, each as it's own array element
	function addArray($lines, $opening = '[', $closure = ']') {
		$this->addBlock($opening, $closure);
		if(!empty($lines)) {
			$end = count($lines) - 1;
			for($i=0; $i<=$end; $i++) {
				$this->addLine($lines[$i] . ($i == $end ? '' : ','));
			}
		}
		$this->closeCurrentBlock();
	}





	//! Close Current Block
	//! @param int $lvl Number of levels to close @default[1]
	function closeCurrentBlock($lvl = 1) {
		for($lvl; $lvl>0; $lvl--) {
			end($this->mCurrentBlock);
			$key = key($this->mCurrentBlock);

			// Do not unset base block
			if($key) {
				unset($this->mCurrentBlock[$key]);
			}

			end($this->mCurrentBlock);
			$key = key($this->mCurrentBlock);
			$this->mCurrentBlock[$key][] = array_pop($this->mBlockClosures);
		}
	}





}




