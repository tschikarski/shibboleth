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

	// TODO: Check if we can replace $_SERVER[~] by t3lib_div::getIndpEnv(~)
	// TODO: with ish: Observation: If logged in to BE using Shibboleth and TYPO3 timeout occurs, you have to click Logout to re-login.

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
	var $shibboleth_extConf = ''; // Extension configuration.

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

		global $TYPO3_CONF_VARS;
		$this->shibboleth_extConf = unserialize($TYPO3_CONF_VARS['EXT']['extConf']['shibboleth']);
		
		return $available;
	}
	
	function getUser() {

		if($this->writeDevLog) t3lib_div::devlog('getUser ($_SERVER)','shibboleth',0,$_SERVER);
		if($this->writeDevLog) t3lib_div::devlog('getUser: mode: ' . $this->mode,'shibboleth'); // subtype
		if($this->writeDevLog) t3lib_div::devlog('getUser: loginType: ' . $this->authInfo['loginType'],'shibboleth'); // BE or FE
		if($this->writeDevLog) t3lib_div::devlog('getUser: (authInfo)','shibboleth',0,$this->authInfo);
		if($this->writeDevLog) t3lib_div::devlog('getUser: (loginData)','shibboleth',0,$this->login);
		
			// check, if the user is Shibboleth authenticated
		if(!isset($_SERVER['Shib-Session-ID'])) {
			if($this->writeDevLog) t3lib_div::devlog('getUser: no Shibboleth session present','shibboleth',0,$_SERVER);
				// Unfortunately, returning FALSE is not sufficient to log off from an active session (tested on FE)
				// But: Log off only, if the logged in user came from Shibboleth, i.e. has a non-empty special field!
			if (is_array($this->authInfo['userSession']) && $this->authInfo['userSession']['tx_shibboleth_shibbolethsessionid']) {
				if($this->writeDevLog) t3lib_div::devlog('getUser: ... so logging off actively ($this->authInfo[\'userSession\'])','shibboleth',0,$this->authInfo['userSession']);
				$this->pObj->logoff();
			}
				// TODO: ish: In BE check visibility of tx_shibb... for non-admin be users
			
			return FALSE;
		}
		
			// TODO: with ish: shall we become sensitive for "Application-ID"? Optional by filling in a related config item?
		
		$userhandler_classname = t3lib_div::makeInstanceClassName('tx_shibboleth_userhandler');
		$userhandler = new $userhandler_classname($this->authInfo['loginType'], $this->db_user, $this->db_groups);
		
		$user = $userhandler->getUserFromDB();
		if($this->writeDevLog) t3lib_div::devlog('getUser: after getUserFromDB ($user)','shibboleth',0,$user);
		
		if (!is_array($user)) {
				// Got no matching user from DB
			if($this->writeDevLog) t3lib_div::devlog('getUser: no matching user in DB','shibboleth');
			if (!$this->shibboleth_extConf[$this->authInfo['loginType'].'_autoImport']){
					// No auto-import for this login type, no user found -> no login possible, don't return a user record.
				if($this->writeDevLog) t3lib_div::devlog('getUser: no auto-import configured; will exit','shibboleth',0,$this->shibboleth_extConf[$this->authInfo['loginType'].'_autoImport']);
				// if($this->writeDevLog) t3lib_div::devlog('getUser: extConf','shibboleth',0,$this->shibboleth_extConf);
				
				return false;
			} else {
				if($this->writeDevLog) t3lib_div::devlog('getUser: preparing ($user) for auto-import','shibboleth',0,$user);
			}
		}
			// Fetched matching user successfully from DB or auto-import is allowed
			// get some basic user data from shibboleth server-variables
		$user = $userhandler->mapShibbolethAttributesToUserArray($user);
		if($this->writeDevLog) t3lib_div::devlog('getUser: offering $user for authentication','shibboleth',0,$user);

		return $user;
	}
	
	function authUser(&$user) {
		if($this->writeDevLog) t3lib_div::devlog('authUser: ($user); Shib-Session-ID: ' . $_SERVER['Shib-Session-ID'],'shibboleth',0,$user);
		
		if($this->writeDevLog) t3lib_div::devlog('authUser: ($this->authInfo)','shibboleth',0,$this->authInfo);
			// TODO: with ish: Verify the following line! 
		if (is_array($this->authInfo['userSession'])) {
		// if ($user['authenticated']) {
				// This user is already logged in to TYPO3, check Shibboleth session (e.g. to detect time-out or even change of Shibboleth Session)?
			if (isset($_SERVER['Shib-Session-ID']) && ($_SERVER['Shib-Session-ID'] == $user['tx_shibboleth_shibbolethsessionid'])) {
					// Shibboleth session still exists, authenticate!
				if($this->writeDevLog) t3lib_div::devlog('authUser: Found Shib-Session-ID: authenticated','shibboleth',0,array($_SERVER['Shib-Session-ID']));	
				return 200;
			} else {
					// Shibboleth session gone, refuse authentication, even log off a logged in user!
				if($this->writeDevLog) t3lib_div::devlog('authUser: Shib-Session gone, time-out? Log off!','shibboleth',0,$_SERVER);
					// Just returning FALSE will not log off an already active user!
				$this->pObj->logoff();
				return FALSE;
			}
		} else {
				// This user is not yet logged in
			if (is_array($user) && $user[$this->db_user['usergroup_column']]) {
					// User has group(s), i.e. he is allowed to login
					// Before we return our positiv result, we have to update/insert the user in DB
				$userhandler_classname = t3lib_div::makeInstanceClassName('tx_shibboleth_userhandler');
				$userhandler = new $userhandler_classname($this->authInfo['loginType'], $this->db_user, $this->db_groups);
					// We now can auto-import; we won't be in authUser, if getUser didn't detect auto-import configuration.
				$user['uid'] = $userhandler->synchronizeUserData($user);				
				if($this->writeDevLog) t3lib_div::devlog('authUser: after insert/update DB $uid=' . $user['uid'] . '; Auth OK','shibboleth');
				return 200;
			}
		}
		if($this->writeDevLog) t3lib_div::devlog('authUser: Refusing auth based on criteria. (usergroup)','shibboleth',0,array($user[$this->db_user['usergroup_column']]));
		return false; // To be safe: Default access is no access.
	}


}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/shibboleth/sv1/class.tx_shibboleth_sv1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/shibboleth/sv1/class.tx_shibboleth_sv1.php']);
}

?>