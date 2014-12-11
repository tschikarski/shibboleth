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

namespace TrustCnct\Shibboleth;

use TYPO3\CMS\Core\Utility\GeneralUtility;

require_once(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('shibboleth').'lib/class.tx_shibboleth_userhandler.php');

/**
 * Service "Shibboleth Authentication" for the "shibboleth" extension.
 *
 * @author	Irene Höppner <irene.hoeppner@abezet.de>
 * @package	TYPO3
 * @subpackage	tx_shibboleth
 */

class tx_shibboleth_sv1 extends \TYPO3\CMS\Sv\AbstractAuthenticationService {
	var $prefixId = 'tx_shibboleth_sv1';		// Same as class name
	var $scriptRelPath = 'sv1/class.tx_shibboleth_sv1.php';	// Path to this script relative to the extension dir.
	var $extKey = 'shibboleth';	// The extension key.
	var $shibboleth_extConf = ''; // Extension configuration.
	var $ShibSessionID = 'Shib-Session-ID';
	var $ShibApplicationID = 'Shib-Application-ID';

	/**
	 * [Put your description here]
	 *
	 * @return	[type]		...
	 */
	function init() {
		$available = parent::init();
		
		// Here you can initialize your class.
		
		// The class have to do a strict check if the service is available.
		// The needed external programs are already checked in the parent class.
		
		// If there's no reason for initialization you can remove this function.

		global $TYPO3_CONF_VARS;
		$this->shibboleth_extConf = unserialize($TYPO3_CONF_VARS['EXT']['extConf']['shibboleth']);
		
		if (isset($_SERVER['AUTH_TYPE']) && $_SERVER['AUTH_TYPE'] == 'shibboleth') {
			if (isset($_SERVER['Shib_Session_ID']) && $_SERVER['Shib_Session_ID'] != '') {
				$this->ShibSessionID = 'Shib_Session_ID';
				$this->ShibApplicationID = 'Shib_Application_ID';
			}
		}
		
		return $available;
	}
	
	function getUser() {
		
		if (is_object($GLOBALS['TSFE'])) {
			$isAlreadyThere = TRUE;
		}

		if($this->writeDevLog) GeneralUtility::devlog('getUser ($_SERVER)','shibboleth',0,$_SERVER);
		if($this->writeDevLog) GeneralUtility::devlog('getUser: mode: ' . $this->mode,'shibboleth'); // subtype
		if($this->writeDevLog) GeneralUtility::devlog('getUser: loginType: ' . $this->authInfo['loginType'],'shibboleth'); // BE or FE
		if($this->writeDevLog) GeneralUtility::devlog('getUser: (authInfo)','shibboleth',0,$this->authInfo);
		if($this->writeDevLog) GeneralUtility::devlog('getUser: (loginData)','shibboleth',0,$this->login);
		
			// check, if there is a user that is Shibboleth authenticated (with a correct application ID, if required by configuration)
			// Remark: Best recognition of Shibboleth session by $_SERVER['AUTH_TYPE'] == 'shibboleth', as other Shibboleth-specific 
			// server vars may have differing syntax/names on different systems
		if(!isset($_SERVER['AUTH_TYPE']) || $_SERVER['AUTH_TYPE'] != 'shibboleth' ||
			($this->shibboleth_extConf[$this->authInfo['loginType'].'_applicationID'] != '' &&
			$this->shibboleth_extConf[$this->authInfo['loginType'].'_applicationID'] != $_SERVER[$this->ShibApplicationID])
		) {
			if($this->writeDevLog) GeneralUtility::devlog('getUser: no applicable Shibboleth session present','shibboleth',0,$_SERVER);
				// Unfortunately, returning FALSE is not sufficient to log off from an active session (tested on FE)
				// But: Log off only, if the logged in user came from Shibboleth, i.e. has a non-empty special field!
			if (is_array($this->authInfo['userSession']) && $this->authInfo['userSession']['tx_shibboleth_shibbolethsessionid']) {
				if($this->writeDevLog) GeneralUtility::devlog('getUser: ... so logging off actively ($this->authInfo[\'userSession\'])','shibboleth',0,$this->authInfo['userSession']);
				$this->pObj->logoff();
			}
			
			return FALSE;
		}
		
		$userhandler = GeneralUtility::makeInstance('TrustCnct\\Shibboleth\\tx_shibboleth_userhandler',$this->authInfo['loginType'], $this->db_user, $this->db_groups, $this->ShibSessionID, $this->writeDevLog);

		$user = $userhandler->getUserFromDB();
		if($this->writeDevLog) GeneralUtility::devlog('getUser: after getUserFromDB ($user)','shibboleth',0,$user);
		
		if (!is_array($user)) {
				// Got no matching user from DB
			if($this->writeDevLog) GeneralUtility::devlog('getUser: no matching user in DB','shibboleth');
			if (!$this->shibboleth_extConf[$this->authInfo['loginType'].'_autoImport']){
					// No auto-import for this login type, no user found -> no login possible, don't return a user record.
				if($this->writeDevLog) GeneralUtility::devlog('getUser: no auto-import configured; will exit','shibboleth',0,$this->shibboleth_extConf[$this->authInfo['loginType'].'_autoImport']);
				// if($this->writeDevLog) GeneralUtility::devlog('getUser: extConf','shibboleth',0,$this->shibboleth_extConf);
				
				return false;
			} else {
				if($this->writeDevLog) GeneralUtility::devlog('getUser: preparing ($user) for auto-import','shibboleth',0,$user);
			}
		}
			// Fetched matching user successfully from DB or auto-import is allowed
			// get some basic user data from shibboleth server-variables
		$user = $userhandler->transferShibbolethAttributesToUserArray($user);
		if($this->writeDevLog) GeneralUtility::devlog('getUser: offering $user for authentication','shibboleth',0,$user);

		if (!$isAlreadyThere) {
			unset($GLOBALS['TSFE']);
		}
		
		return $user;
	}
	
	function authUser(&$user) {
		if($this->writeDevLog) GeneralUtility::devlog('authUser: ($user); Shib-Session-ID: ' . $_SERVER[$this->ShibSessionID],'shibboleth',0,$user);
		
		if($this->writeDevLog) GeneralUtility::devlog('authUser: ($this->authInfo)','shibboleth',0,$this->authInfo);
		
			// If the user come not from shibboleth getUser, we will ignore it.
		if (!$user['tx_shibboleth_shibbolethsessionid']) {
			if($this->writeDevLog) GeneralUtility::devlog('authUser: This is not our user. Exiting.','shibboleth');
			return 100;
		}
			// Check, if we have an already logged in TYPO3 user.
		if (is_array($this->authInfo['userSession'])) {
				// Some user is already logged in to TYPO3, check if it is a Shibboleth user 
			if (!$this->authInfo['userSession']['tx_shibboleth_shibbolethsessionid']) {
					// The presently logged in user is not a shibboleth user, we do nothing
				if($this->writeDevLog) GeneralUtility::devlog('authUser: Found a logged in non-Shibboleth user - exiting','shibboleth',0,array($_SERVER[$this->ShibSessionID]));	
				return 100;
			}
			
				// For safety: Check for existing Shibboleth-Session and return FALSE, otherwise!
			if (!$_SERVER[$this->ShibSessionID]) {
					// With no Shibboleth session we won't authenticate anyone!
				if($this->writeDevLog) GeneralUtility::devlog('authUser: Found no Shib-Session-ID: rejecting','shibboleth',0,array($_SERVER[$this->ShibSessionID]));
				return FALSE;
			}
			
				// The logged in user is a Shibboleth user, and we have a Shib-Session-ID. However, Session-ID might have changed on some miraculous way
			if ($_SERVER[$this->ShibSessionID] == $this->authInfo['userSession']['tx_shibboleth_shibbolethsessionid']) {
					// Shibboleth session still the same, authenticate!
				if($this->writeDevLog) GeneralUtility::devlog('authUser: Found our previous Shib-Session-ID: authenticated','shibboleth',0,array($_SERVER[$this->ShibSessionID]));
				return 200;
			}
			
//				// Shibboleth session gone or changed, refuse authentication, even log off a logged in user!
//			if($this->writeDevLog) GeneralUtility::devlog('authUser: Shib-Session changed. Log off present user!','shibboleth',0,$_SERVER);
//				// Just returning FALSE will not log off an already active user!
//			$this->pObj->logoff();

				// Shibboleth session gone or changed, this is just a re-authentication via Shibboleth, nothing to do
		}
		
		if($this->writeDevLog) GeneralUtility::devlog('authUser: $this->db_user','shibboleth',0,$this->db_user);
		
			// This user is not yet logged in
		if (is_array($user) && $user['_allowUser']) {
			unset ($user['_allowUser']);
				// Before we return our positiv result, we have to update/insert the user in DB
			$userhandler = GeneralUtility::makeInstance('TrustCnct\\Shibboleth\\tx_shibboleth_userhandler',$this->authInfo['loginType'], $this->db_user, $this->db_groups, $this->ShibSessionID, $this->writeDevLog);
				// We now can auto-import; we won't be in authUser, if getUser didn't detect auto-import configuration.
			$user['uid'] = $userhandler->synchronizeUserData($user);
			if($this->writeDevLog) GeneralUtility::devlog('authUser: after insert/update DB $uid=' . $user['uid'] . '; Auth OK','shibboleth');
			if (! $user['disable']) return 200;
		}
		
		if($this->writeDevLog) GeneralUtility::devlog('authUser: Refusing auth based because _allowUser = 0','shibboleth',0,$user);
		return false; // To be safe: Default access is no access.
	}


}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/shibboleth/sv1/class.tx_shibboleth_sv1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/shibboleth/sv1/class.tx_shibboleth_sv1.php']);
}

?>