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
		$extConf = unserialize($TYPO3_CONF_VARS['EXT']['extConf']['shibboleth']);	
		
			// TODO: Move the following information to some place more appropriate
			/*
			About virtual hosts in connection with Shibboleth:
			Think in terms of applications: An application needs an entry in shibboleth2.xml and is identified by it's application ID
			Default application ID is "default". Application is a set of protected resources. See RequestMapper section in xml or 
			set application ID directly in Apache config (or by .htaccess). We protect our TYPO3 instance, possibly we use an alternative 
			application ID for the /typo3 directory, i.e. we make another application out of the backend.
			
			Typically an application does not span multiple virtual hosts. However, an application may be accessible by more than one
			domain, i.e. virtual host. In that case, it is unclear to me, if one has do define multiple applications in the xml file.
			
			See also: http://kb.ucla.edu/articles/shibboleth-apache-multiple-virtual-host-configuration-for-moodle
			*/
			
			// Allow adding of entityID parameter to IdP-Link, if configured in ext conf.
		
		$entityIDparam = $extConf['entityID'];
		if ($entityIDparam != '') {
			$entityIDparam = 'entityID='. rawurldecode($entityIDparam);
		}
		
		$typo3_site_url = t3lib_div::getIndpEnv('TYPO3_SITE_URL');
		if ($extConf['forceSSL']) {
			$typo3_site_url = str_replace('http://', 'https://', $typo3_site_url);
		}
		
			// TODO: hard-coded link text shall be replaced by locallang.xml based, piGetLL or something like that
		$linkText = 'Login &uuml;ber Shibboleth';
		
		$sessionHandlerUrl = $extConf['sessions_handlerURL'];
		
		if (preg_match('/^http/',$sessionHandlerUrl) == 0) {
			$sessionHandlerUrl = $typo3_site_url . $sessionHandlerUrl;
		}

		$targetParam = 'target=' . rawurlencode(t3lib_div::getIndpEnv('TYPO3_SITE_URL'));

		if (($entityIDparam != '') and ($targetParam != '')) {
			$params = $entityIDparam . '&' . $targetParam;
		} else {
			$params = $entityIDparam . $targetParam;
		}

		if ($params != '') {
			$params = '?' . $params;
		}
		
		$content='
			<a href="' . $sessionHandlerUrl . $extConf['sessionInitiator_Location'] . $params . '">' . $linkText . '</a>
		';

		return $this->pi_wrapInBaseClass($content);
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/shibboleth/pi1/class.tx_shibboleth_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/shibboleth/pi1/class.tx_shibboleth_pi1.php']);
}

?>