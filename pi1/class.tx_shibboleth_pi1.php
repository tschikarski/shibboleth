<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2010 Irene Höppner <irene.hoeppner@abezet.de>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 * Hint: use extdeveval to insert/update function index above.
 */

require_once(PATH_tslib.'class.tslib_pibase.php');


/**
 * Plugin 'Shibboleth Login' for the 'shibboleth' extension.
 *
 * @author	Irene Höppner <irene.hoeppner@abezet.de>
 * @package	TYPO3
 * @subpackage	tx_shibboleth
 */
class tx_shibboleth_pi1 extends tslib_pibase {
	var $prefixId      = 'tx_shibboleth_pi1';		// Same as class name
	var $scriptRelPath = 'pi1/class.tx_shibboleth_pi1.php';	// Path to this script relative to the extension dir.
	var $extKey        = 'shibboleth';	// The extension key.
	var $pi_checkCHash = true;
	
	/**
	 * The main method of the PlugIn
	 *
	 * @param	string		$content: The PlugIn content
	 * @param	array		$conf: The PlugIn configuration
	 * @return	The content that is displayed on the website
	 */
	function main($content, $conf) {
		$this->conf = $conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();
		global $TYPO3_CONF_VARS;
#debug($_SERVER);
			// TODO: Detect if extension not installed and/or shibd daemon not present/running?
			// check, if the (apache) module is available.
			// SCHEMAS is if the user is not authenticated, Application-ID is used if the user is authenticated
		#if(isset($_SERVER['SHIBSP_SCHEMAS']) || isset($_SERVER['Shib-Application-ID'])) {
			$extConf = unserialize($TYPO3_CONF_VARS['EXT']['extConf']['shibboleth']);	
				// TODO: ish: Check this kind of fixing wrong slashes
			fixSlashes($extConf['sessions_handlerURL']);
			fixSlashes($extConf['sessionInitiator_Location']);
				// TODO: with ish: This will only work, if the TYPO3 instance is on the DocumentRoot of the (virtual) host 
				// TODO: ish: Replacement of $_SERVER by some T3 lib method, however, already tried t3lib_div::getIndpEnv('~'), using 'HTTPS' and 'scheme'
			if ($_SERVER['HTTPS'] && (strtolower($_SERVER['HTTPS']) == 'on')) {
				$protocol = 'https';
			} else {
				$protocol = 'http';
			}

			$content='
				<a href="' . t3lib_div::getIndpEnv('TYPO3_REQUEST_HOST') . '' . $extConf['sessions_handlerURL'] . $extConf['sessionInitiator_Location'] . '?target=' . rawurlencode(t3lib_div::getIndpEnv('TYPO3_REQUEST_HOST')) . '">Shibboleth-Login</a>
			';
				// TODO: ish: Better use 'TYPO3_SITE_URL' or similar, than 'TYPO3_REQUEST_HOST'?
				// TODO: ish: hard-coded link text?
		#} else {
		#	$content = 'The Shibboleth SP module is not installed.';
		#}
		
		return $this->pi_wrapInBaseClass($content);
	}
}

	function fixSlashes(&$partialPath) {
		$stripped = trim($partialPath);
		$stripped = str_replace('/', '', $stripped);
		$partialPath = '/'.$stripped;
	}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/shibboleth/pi1/class.tx_shibboleth_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/shibboleth/pi1/class.tx_shibboleth_pi1.php']);
}

?>