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

use TrustCnct\Shibboleth\User\UserHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Service "Shibboleth Authentication" for the "shibboleth" extension.
 *
 * @author    Irene Höppner <irene.hoeppner@abezet.de>
 * @package    TYPO3
 * @subpackage    tx_shibboleth
 */

class ShibbolethAuthentificationService extends \TYPO3\CMS\Sv\AbstractAuthenticationService {
    var $prefixId = 'ShibbolethAuthentificationService';        // Same as class name
    var $scriptRelPath = 'Classes/ShibbolethAuthentificationService.php';    // Path to this script relative to the extension dir.
    var $extKey = 'shibboleth';    // The extension key.
    var $shibboleth_extConf = ''; // Extension configuration.
    var $envShibPrefix = '';      // If environment variables are prefixed, store prefix here (e.g. REDIRECT_...)
    var $hasShibbolethSession = FALSE;
    var $shibSessionIdKey = '';
    var $shibApplicationIdKey = '';
    var $forbiddenUser = array(
        'uid' => 999999,
        'username' => 'nevernameauserlikethis',
        '_allowUser' => 0
    );

    private function logoffAnyShibbolethUser() {
        if (is_array($this->authInfo['userSession']) && $this->authInfo['userSession']['tx_shibboleth_shibbolethsessionid']) {
            if($this->writeDevLog)
                GeneralUtility::devlog(
                    $this->mode . ': have a non-matching Shibboleth user logged in - logout! - for session id see extra data',
                    'shibboleth',
                    0,
                    $this->authInfo['userSession']
                );
            $this->pObj->logoff();
        }
    }

    /**
     * [Put your description here]
     *
     * @return    [type]        ...
     */
    function init() {
        $available = parent::init();
        
        // Here you can initialize your class.
        
        // The class have to do a strict check if the service is available.
        // The needed external programs are already checked in the parent class.
        
        // If there's no reason for initialization you can remove this function.

        global $TYPO3_CONF_VARS;
        $this->shibboleth_extConf = unserialize($TYPO3_CONF_VARS['EXT']['extConf']['shibboleth']);

        $shortestPrefixLength = 65535;
        foreach ($_SERVER as $serverEnvKey => $serverEnvValue) {
            $posOfShibInKey = strpos($serverEnvKey,'Shib');
            if ($posOfShibInKey !== FALSE && $posOfShibInKey < $shortestPrefixLength) {
                $shortestPrefixLength = $posOfShibInKey;
                $this->envShibPrefix = substr($serverEnvKey, 0, $posOfShibInKey);
                $this->hasShibbolethSession = TRUE;
                $this->shibSessionIdKey = $this->envShibPrefix . 'Shib_Session_ID';
                $this->shibApplicationIdKey = $this->envShibPrefix . 'Shib_Application_ID';
            }
        }
        // Another chance to detect Shibboleth session present; just for safety, as code before not well tested at the moment
        if (!$this->hasShibbolethSession && isset($_SERVER['AUTH_TYPE']) && $_SERVER['AUTH_TYPE'] == 'shibboleth') {
            if (isset($_SERVER['Shib_Session_ID']) && $_SERVER['Shib_Session_ID'] != '') {
                $this->hasShibbolethSession = TRUE;
                $this->envShibPrefix = '';
                $this->shibSessionIdKey = 'Shib_Session_ID';
                $this->shibApplicationIdKey = 'Shib_Application_ID';
            }
        }
        
        return $available;
    }
    
    function getUser() {
        
        if (is_object($GLOBALS['TSFE'])) {
            $isAlreadyThere = TRUE;
        }

        if($this->writeDevLog) GeneralUtility::devlog($this->mode.' ($_SERVER)','shibboleth',0,$_SERVER);
        // if($this->writeDevLog) GeneralUtility::devlog('getUser: mode: ' . $this->mode,'shibboleth'); // subtype
        // if($this->writeDevLog) GeneralUtility::devlog('getUser: loginType: ' . $this->authInfo['loginType'],'shibboleth'); // BE or FE
        // if($this->writeDevLog) GeneralUtility::devlog('getUser: (authInfo)','shibboleth',0,$this->authInfo);
        // if($this->writeDevLog) GeneralUtility::devlog('getUser: (loginData)','shibboleth',0,$this->login);

        if (($this->envShibPrefix) && ($this->writeDevLog))
            GeneralUtility::devLog(
                'Found only prefixed "Shib" environment variables. Will remove prefix "'.$this->envShibPrefix.'"',
                'shibboleth',
                1
            );

        // Without a valid Shibboleth session, bail out here returning FALSE
        if (!$this->hasShibbolethSession) {
            if($this->writeDevLog)
                GeneralUtility::devlog(
                    $this->mode . ': no applicable Shibboleth session recognized - see extra data for environment variables',
                    'shibboleth',
                    2,
                    $_SERVER
                );
            $this->logoffAnyShibbolethUser();
            return FALSE;
        }

            // Also bail out, if the application ID is required to coincide and does not
        if ($this->shibboleth_extConf[$this->authInfo['loginType'].'_applicationID'] != '' &&
                $this->shibboleth_extConf[$this->authInfo['loginType'].'_applicationID'] != $_SERVER[$this->shibApplicationIdKey]) {
            if($this->writeDevLog)
                GeneralUtility::devlog(
                    $this->mode . ': Shibboleth session appliation ID ' . $_SERVER[$this->shibApplicationIdKey] .
                    ' does not match required '. $this->authInfo['loginType'].'_applicationID (' .
                    $this->shibboleth_extConf[$this->authInfo['loginType'].'_applicationID'].
                    ') - see extra data for environment variables',
                    'shibboleth',
                    3,
                    $_SERVER);
            $this->logoffAnyShibbolethUser();
            return FALSE;
        }

            // check, if there is a user that is Shibboleth authenticated (with a correct application ID, if required by configuration)
            // Remark: Best recognition of Shibboleth session by $_SERVER['AUTH_TYPE'] == 'shibboleth', as other Shibboleth-specific 
            // server vars may have differing syntax/names on different systems
        /*
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
        */
        
        $userhandler = GeneralUtility::makeInstance(UserHandler::class,$this->authInfo['loginType'],
            $this->db_user, $this->db_groups, $this->shibSessionIdKey, $this->writeDevLog, $this->envShibPrefix);

        $user = $userhandler->getUserFromDB();

        if (!is_array($user)) {
                // Got no matching user from DB
            if($user !== false) {
                if($this->writeDevLog)
                    GeneralUtility::devLog(
                        $this->mode.': '.$user.' - see $_SERVER in extra data for original data',
                        'shibboleth',
                        3,
                        $_SERVER
                    );
                return false;
            }
            if (!$this->shibboleth_extConf[$this->authInfo['loginType'].'_autoImport']){
                    // No auto-import for this login type, no user found -> no login possible, don't return a user record.
                if($this->writeDevLog)
                    GeneralUtility::devlog(
                        $this->mode.': User not found in DB and no auto-import configured; will exit',
                        'shibboleth',
                        2,
                        $this->shibboleth_extConf[$this->authInfo['loginType'].'_autoImport']
                    );
                return false;
            }
        }
            // Fetched matching user successfully from DB or auto-import is allowed
            // get some basic user data from shibboleth server-variables
        $user = $userhandler->transferShibbolethAttributesToUserArray($user);
        if (!is_array($user)) {
            if($this->writeDevLog) {
                if($user === false) {
                    $msg = $this->mode . ': Error while calculating user attributes.';
                } else {
                    $msg = $this->mode . ': '. $user;
                }
                $msg = $msg . ' Check $_SERVER (extra data) and config file!';
                GeneralUtility::devlog(
                        $msg,
                        'shibboleth',
                        3,
                        $_SERVER
                    );
            }
            return false;
        }

        if($this->writeDevLog) GeneralUtility::devlog('getUser: offering $user for authentication','shibboleth',0,$user);

        if (!$isAlreadyThere) {
            unset($GLOBALS['TSFE']);
        }

        if ($user[$this->db_user['username_column']] == '') {
            if($this->writeDevLog)
                GeneralUtility::devlog(
                    $this->mode.': Username is empty string. Never do this!',
                    'shibboleth',
                    3,
                    $this->shibboleth_extConf[$this->authInfo['loginType'].'_autoImport']
                );
            $this->logoffAnyShibbolethUser();
            return FALSE;
        }
        
        return $user;
    }

    function authUser(&$user) {
        if($this->writeDevLog) GeneralUtility::devlog('authUser: ($user); Shib-Session-ID: ' . $_SERVER[$this->shibSessionIdKey],'shibboleth',0,$user);
        
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
                if($this->writeDevLog) GeneralUtility::devlog('authUser: Found a logged in non-Shibboleth user - exiting','shibboleth',0,array($_SERVER[$this->shibSessionIdKey]));
                return 100;
            }
            
                // For safety: Check for existing Shibboleth-Session and return FALSE, otherwise!
            if (!$this->hasShibbolethSession) {
                    // With no Shibboleth session we won't authenticate anyone!
                if($this->writeDevLog) GeneralUtility::devlog('authUser: Found no Shib-Session-ID: rejecting','shibboleth',0,array($_SERVER[$this->shibSessionIdKey]));
                return FALSE;
            }
            
                // The logged in user is a Shibboleth user, and we have a Shib-Session-ID. However, Session-ID might have changed on some miraculous way
            if ($_SERVER[$this->shibSessionIdKey] == $this->authInfo['userSession']['tx_shibboleth_shibbolethsessionid']) {
                    // Shibboleth session still the same, authenticate!
                if($this->writeDevLog) GeneralUtility::devlog('authUser: Found our previous Shib-Session-ID: authenticated','shibboleth',0,array($_SERVER[$this->shibSessionIdKey]));
                return 200;
            }
            
//                // Shibboleth session gone or changed, refuse authentication, even log off a logged in user!
//            if($this->writeDevLog) GeneralUtility::devlog('authUser: Shib-Session changed. Log off present user!','shibboleth',0,$_SERVER);
//                // Just returning FALSE will not log off an already active user!
//            $this->pObj->logoff();

                // Shibboleth session gone or changed, this is just a re-authentication via Shibboleth, nothing to do
        }
        
        // if($this->writeDevLog) GeneralUtility::devlog('authUser: $this->db_user','shibboleth',0,$this->db_user);
        
            // This user is not yet logged in
        if (is_array($user) && $user['_allowUser']) {
            unset ($user['_allowUser']);
                // Before we return our positiv result, we have to update/insert the user in DB
            $userhandler = GeneralUtility::makeInstance(UserHandler::class,$this->authInfo['loginType'],
                $this->db_user, $this->db_groups, $this->shibSessionIdKey, $this->writeDevLog);
                // We now can auto-import; we won't be in authUser, if getUser didn't detect auto-import configuration.
            $user['uid'] = $userhandler->synchronizeUserData($user);
            if($this->writeDevLog) GeneralUtility::devlog('authUser: after insert/update DB $uid=' . $user['uid'] . '; ($user attached).','shibboleth',0,$user);
            if ((! $user['disable']) AND ($user['uid']>0)) return 200;
        }
        
        if($this->writeDevLog) GeneralUtility::devlog('authUser: Refusing auth','shibboleth',0,$user);
        return false; // To be safe: Default access is no access.
    }


}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/shibboleth/Classes/ShibbolethAuthentificationService.php'])    {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/shibboleth/Classes/ShibbolethAuthentificationService.php']);
}

?>
