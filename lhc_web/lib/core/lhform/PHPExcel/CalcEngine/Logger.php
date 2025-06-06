<?php
/**
 * PHPExcel
 *
 * Copyright (c) 2006 - 2014 PHPExcel
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @category   PHPExcel
 * @package    PHPExcel_Calculation
 * @copyright  Copyright (c) 2006 - 2014 PHPExcel (http://www.codeplex.com/PHPExcel)
 * @license	http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt	LGPL
 * @version	##VERSION##, ##DATE##
 */

/**
 * PHPExcel_CalcEngine_Logger
 *
 * @category	PHPExcel
 * @package		PHPExcel_Calculation
 * @copyright	Copyright (c) 2006 - 2014 PHPExcel (http://www.codeplex.com/PHPExcel)
 */
class PHPExcel_CalcEngine_Logger {

	/**
	 * Flag to determine whether a debug log should be generated by the calculation engine
	 *		If true, then a debug log will be generated
	 *		If false, then a debug log will not be generated
	 *
	 * @var boolean
	 */
	private $_writeDebugLog = FALSE;

	/**
	 * Flag to determine whether a debug log should be echoed by the calculation engine
	 *		If true, then a debug log will be echoed
	 *		If false, then a debug log will not be echoed
	 * A debug log can only be echoed if it is generated
	 *
	 * @var boolean
	 */
	private $_echoDebugLog = FALSE;

	/**
	 * The debug log generated by the calculation engine
	 *
	 * @var string[]
	 */
	private $_debugLog = array();

	/**
	 * The calculation engine cell reference stack
	 *
	 * @var PHPExcel_CalcEngine_CyclicReferenceStack
	 */
	private $_cellStack;


	/**
	 * Instantiate a Calculation engine logger
	 *
	 * @param  PHPExcel_CalcEngine_CyclicReferenceStack $stack
	 */
	public function __construct(PHPExcel_CalcEngine_CyclicReferenceStack $stack) {
		$this->_cellStack = $stack;
	}

	/**
	 * Enable/Disable Calculation engine logging
	 *
	 * @param  boolean $pValue
	 */
	public function setWriteDebugLog($pValue = FALSE) {
		$this->_writeDebugLog = $pValue;
	}

	/**
	 * Return whether calculation engine logging is enabled or disabled
	 *
	 * @return  boolean
	 */
	public function getWriteDebugLog() {
		return $this->_writeDebugLog;
	}

	/**
	 * Enable/Disable echoing of debug log information
	 *
	 * @param  boolean $pValue
	 */
	public function setEchoDebugLog($pValue = FALSE) {
		$this->_echoDebugLog = $pValue;
	}

	/**
	 * Return whether echoing of debug log information is enabled or disabled
	 *
	 * @return  boolean
	 */
	public function getEchoDebugLog() {
		return $this->_echoDebugLog;
	}

	/**
	 * Write an entry to the calculation engine debug log
	 */
	public function writeDebugLog() {
		//	Only write the debug log if logging is enabled
		if ($this->_writeDebugLog) {
			$message = implode(func_get_args());
			$cellReference = implode(' -> ', $this->_cellStack->showStack());
			if ($this->_echoDebugLog) {
				echo $cellReference, 
					($this->_cellStack->count() > 0 ? ' => ' : ''), 
					$message, 
					PHP_EOL;
			}
			$this->_debugLog[] = $cellReference . 
				($this->_cellStack->count() > 0 ? ' => ' : '') . 
				$message;
		}
	}	//	function _writeDebug()

	/**
	 * Clear the calculation engine debug log
	 */
	public function clearLog() {
		$this->_debugLog = array();
	}	//	function flushLogger()

	/**
	 * Return the calculation engine debug log
	 *
	 * @return  string[]
	 */
	public function getLog() {
		return $this->_debugLog;
	}	//	function flushLogger()

}	//	class PHPExcel_CalcEngine_Logger

