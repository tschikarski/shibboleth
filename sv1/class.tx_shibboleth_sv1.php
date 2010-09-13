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

require_once(t3lib_extMgm::extPath('shibboleth').'lib/class.tx_shibboleth_userhandler.php');

/**
 * Service "Shibboleth Authentication" for the "shibboleth" extension.
 *
 * @author	Irene Höppner <irene.hoeppner@abezet.de>
 * @package	TYPO3
 * @subpackage	tx_shibboleth
 */
class tx_shibboleth_sv1 extends tx_sv_authbase {
	var $prefixId = 'tx_shibboleth_sv1';		// Same as class name
	var $scriptRelPath = 'sv1/class.tx_shibboleth_sv1.php';	// Path to this script relative to the extension dir.
	var $extKey = 'shibboleth';	// The extension key.

	/**
	 * [Put your description here]
	 *
	 * @return	[type]		...
	 */
	function init()	{
		$available = parent::init();

		// Here you can initialize your class.

		// The class have to do a strict check if the service is available.
		// The needed external programs are already checked in the parent class.

		// If there's no reason for initialization you can remove this function.

		return $available;
	}
	
	function getUser() {

		if($this->writeDevLog) t3lib_div::devlog('inGetUser','shibboleth',0,$_SERVER);
		if($this->writeDevLog) t3lib_div::devlog('mode: ' . $this->mode,'shibboleth'); // subtype
		if($this->writeDevLog) t3lib_div::devlog('loginType: ' . $this->authInfo->loginType,'shibboleth'); // BE or FE
		if($this->writeDevLog) t3lib_div::devlog('authInfo','shibboleth',0,$this->authInfo);
		if($this->writeDevLog) t3lib_div::devlog('loginData','shibboleth',0,$this->login);
		
		// check, if the user is Shibboleth authenticated
		if($_SERVER['Shib-Session-ID']) {
			$userhandler_classname = t3lib_div::makeInstanceClassName('tx_shibboleth_userhandler');
			$userhandler = new $userhandler_classname($this->mode, $this->db_user, $this->db_groups);
			
			$userhandler->getUserFromDB(array());
				// get some basic user data from shibboleth server-variables
			
			
				// TODO: check, if the user exists already! (Learn about "anonymous FE user" - see devLog entries)
				// TODO: Shibboleth-username prefix/postfix
			if($this->writeDevLog) t3lib_div::devlog('user','shibboleth',0,$user);
			//return $user;
		}
	}
	
	function authUser($user) {
		if($this->writeDevLog) t3lib_div::devlog('authUser','shibboleth',0,$_SERVER);
		return 200; // TODO: Just a dummy test
	}


}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/shibboleth/sv1/class.tx_shibboleth_sv1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/shibboleth/sv1/class.tx_shibboleth_sv1.php']);
}

?>