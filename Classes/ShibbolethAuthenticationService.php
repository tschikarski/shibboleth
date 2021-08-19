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
use TYPO3\CMS\Core\Authentication\AbstractAuthenticationService;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Service "Shibboleth Authentication" for the "shibboleth" extension.
 *
 * @author    Irene Höppner <irene.hoeppner@abezet.de>
 * @package    TYPO3
 * @subpackage    tx_shibboleth
 */

class ShibbolethAuthenticationService extends AbstractAuthenticationService
{

    /**
     * @var string
     */
    protected $prefixId = 'ShibbolethAuthenticationService';        // Same as class name

    /**
     * @var string
     */
    protected $scriptRelPath = 'Classes/ShibbolethAuthenticationService.php';    // Path to this script relative to the extension dir.

    /**
     * @var array
     */
    protected $configuration = []; // Extension configuration.

    /**
     * @var string
     */
    protected $envShibPrefix = '';      // If environment variables are prefixed, store prefix here (e.g. REDIRECT_...)

    /**
     * @var bool
     */
    protected $hasShibbolethSession = FALSE;

    /**
     * @var string
     */
    protected $shibSessionIdKey = '';

    /**
     * @var string
     */
    protected $shibApplicationIdKey = '';

    /**
     * @var string
     */
    protected $primaryMode = '';

    /**
     * @var array
     */
    protected $forbiddenUser = array(
        'uid' => 999999,
        'username' => 'nevernameauserlikethis',
        '_allowUser' => 0
    );

     public function init(): bool
     {
        $available = parent::init();

        // Here you can initialize your class.
        
        // The class have to do a strict check if the service is available.
        // The needed external programs are already checked in the parent class.
        
        // If there's no reason for initialization you can remove this function.

        $this->configuration = GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('shibboleth');

        $shortestPrefixLength = 65535;
        foreach ($_SERVER as $serverEnvKey => $serverEnvValue) {
            $posOfShibInKey = strpos($serverEnvKey,'Shib');
            if ($posOfShibInKey !== FALSE && $posOfShibInKey < $shortestPrefixLength) {
                $shortestPrefixLength = $posOfShibInKey;
                $this->envShibPrefix = substr($serverEnvKey, 0, $posOfShibInKey);
                $this->hasShibbolethSession = TRUE;
                $separationChar = substr($serverEnvKey, $posOfShibInKey+4,1);
                $this->shibSessionIdKey = $this->envShibPrefix . 'Shib'.$separationChar.'Session'.$separationChar.'ID';
                $this->shibApplicationIdKey = $this->envShibPrefix . 'Shib'.$separationChar.'Application'.$separationChar.'ID';
            }
        }
        /*
        // Another chance to detect Shibboleth session present; just for safety, as code before not well tested at the moment
        if (!$this->hasShibbolethSession && isset($_SERVER['AUTH_TYPE']) && $_SERVER['AUTH_TYPE'] == 'shibboleth') {
            if (isset($_SERVER['Shib_Session_ID']) && $_SERVER['Shib_Session_ID'] != '') {
                $this->hasShibbolethSession = TRUE;
                $this->envShibPrefix = '';
                $this->shibSessionIdKey = 'Shib_Session_ID';
                $this->shibApplicationIdKey = 'Shib_Application_ID';
            }
        }
        */
        
        return $available;
    }
    
    public function getUser() {
        if (($this->primaryMode !== '') && ($this->primaryMode !== $this->mode)) {
            $this->logger->debug('Secondary login of mode '.$this->mode.' detected after registering primary mode'.$this->primaryMode.'. Skipping.');
            return false;
        }

        if ($this->primaryMode === '') {
            $this->primaryMode = $this->mode;
        }

        if ($this->isLoggedInByNonShibboleth()) {
            $this->logger->debug('Existing non-Shibboleth session detected (mode '.$this->mode.'). Skipping.');
            return FALSE;
        }

        if (is_object($GLOBALS['TSFE'])) {
            $isAlreadyThere = TRUE;
        }

        $this->logger->debug($this->mode.' ($_SERVER)', [$_SERVER]);
        // $this->logger->debug('getUser: mode: ' . $this->mode); // subtype
        // $this->logger->debug('getUser: loginType: ' . $this->authInfo['loginType']); // BE or FE
        // $this->logger->debug('getUser: (authInfo)',[$this->authInfo]);
        // $this->logger->debug('getUser: (loginData)', [$this->login]);

        if ($this->envShibPrefix)
            $this->logger->debug(
                'Found only prefixed "Shib" environment variables. Will remove prefix "'.$this->envShibPrefix.'"',
            );
        // Without a valid Shibboleth session, bail out here returning FALSE
        if (!$this->applicationHasMatchingShibbolethSession()) {
            $this->logger->debug(
                $this->mode . ': no applicable Shibboleth session recognized - see extra data for environment variables',
                [$_SERVER]
            );
            if ($this->isLoggedInByShibboleth()) {
                $this->logger->debug(
                    $this->mode . ': have a non-matching Shibboleth user logged in - logout! - for session id see extra data',
                    [$this->authInfo['userSession']]
                );
                $this->pObj->logoff();
                return $this->forbiddenUser;
            }
            return FALSE;
        }

        /** @var UserHandler $userhandler */
        $userhandler = GeneralUtility::makeInstance(UserHandler::class,$this->authInfo['loginType'],
            $this->db_user, $this->db_groups, $this->shibSessionIdKey, $this->envShibPrefix);

        $user = $userhandler->lookUpShibbolethUserInDatabase();

        if (!is_array($user)) {
                // Got no matching user from DB
            if($user !== false) {
                $this->logger->debug(
                    $this->mode.': '.$user.' - see $_SERVER in extra data for original data',
                    [$_SERVER]
                );
                return false;
            }
            if (!$this->configuration[$this->authInfo['loginType'].'_autoImport']){
                    // No auto-import for this login type, no user found -> no login possible, don't return a user record.
                $this->logger->debug(
                    $this->mode.': User not found in DB and no auto-import configured; will exit',
                    [$this->configuration[$this->authInfo['loginType'].'_autoImport']]
                );
                return false;
            }
        }
        // Fetched matching user successfully from DB or auto-import is allowed
        // get some basic user data from shibboleth server-variables
        $user = $userhandler->transferShibbolethAttributesToUserArray($user);
        if (!is_array($user)) {
            if($user == false) {
                $msg = $this->mode . ': Error while calculating user attributes.';
            } else {
                $msg = $this->mode . ': '. $user;
            }
            $msg = $msg . ' Check $_SERVER (extra data) and config file!';
            $this->logger->debug($msg, [$_SERVER]);
            return false;
        }

        if ($user[$this->db_user['username_column']] == '') {
            $this->logger->debug(
                $this->mode.': Username is empty string. Never do this!',
                [$this->configuration[$this->authInfo['loginType'].'_autoImport']]
            );
            $this->logoffPresentUser();
            return FALSE;
        }

        $this->logger->debug('getUser: offering $user for authentication',[$user]);

        if (!$isAlreadyThere) {
            unset($GLOBALS['TSFE']);
        }

        return $user;
    }

    public function authUser(&$user) {
        $this->logger->debug('authUser: ($user); Shib-Session-ID: ' . $_SERVER[$this->shibSessionIdKey],[$user]);
        $this->logger->debug('authUser: ($this->authInfo)',[$this->authInfo]);
        
        // If the user comes not from shibboleth getUser, we will ignore it.
        if (!$user['tx_shibboleth_shibbolethsessionid']) {
            $this->logger->debug($this->mode.': This user is not for us (not Shibboleth). Exiting.');
            return 100;
        }
        // For safety: Check for existing Shibboleth-Session and return FALSE, otherwise!
        if (!$this->applicationHasMatchingShibbolethSession()) {
            // With no Shibboleth session we won't authenticate anyone!
            $this->logger->debug('authUser: Found no Shib-Session-ID: rejecting',[$_SERVER[$this->shibSessionIdKey]]);
            return FALSE;
        }

        // Check, if we have an already logged in TYPO3 user.
        if (is_array($this->authInfo['userSession'])) {
            // Some user is already logged in to TYPO3, check if it is a Shibboleth user
            if (!$this->authInfo['userSession']['tx_shibboleth_shibbolethsessionid']) {
                // The presently logged in user is not a shibboleth user - neutral answer
                $this->logger->debug('authUser: Found a logged in non-Shibboleth user - no decision',[$_SERVER[$this->shibSessionIdKey]]);
                return 100;
            }

            // The logged in user is a Shibboleth user, and we have a Shib-Session-ID. However, we are paranoic and check, if we still have the same user.
            if ($user['username'] == $this->authInfo['userSession']['username']) {
                // Shibboleth user name still the same.
                $this->logger->debug('authUser: Found our previous Shibboleth user: authenticated');
                return 200;
            }

            $this->logger->debug('authUser: Shibboleth user changed from "'.$this->authInfo['userSession']['username'].'" to "'.$user['username'].'": reject',[$_SERVER[$this->shibSessionIdKey]]);
            $this->logoffPresentUser();
            return false;
        }
        
        // This user is not yet logged in
        if (is_array($user) && $user['_allowUser']) {
            unset ($user['_allowUser']);
            // Before we return our positiv result, we have to update/insert the user in DB
            $userhandler = GeneralUtility::makeInstance(UserHandler::class,$this->authInfo['loginType'],
                $this->db_user, $this->db_groups, $this->shibSessionIdKey, $this->envShibPrefix);
            // We now can auto-import; we won't be in authUser, if getUser didn't detect auto-import configuration.
            $user['uid'] = $userhandler->synchronizeUserData($user);
            $this->logger->debug('authUser: after insert/update DB $uid=' . $user['uid'] . '; ($user attached).',[$user]);
            if ((! $user['disable']) AND ($user['uid']>0)) {
                $this->logger->debug('authUser: user authenticated',[$user]);
                return 200;
            }
            if (defined('TYPO3_MODE') && (TYPO3_MODE === 'BE') && ($user['disable'])) {
                $this->logger->debug('authUser: user created/exists, but is in state "disable"',[$user]);
                if ($this->configuration['BE_disabledUserRedirectUrl']) {
                    $redirectUrl = $this->configuration['BE_disabledUserRedirectUrl'];
                    $this->logger->debug('authUser: redirecting to '. $redirectUrl);
                    // initiate Redirect here
                    header("Location: $redirectUrl");
                    exit;
                }
            }
        }
        
        $this->logger->debug('authUser: Refusing auth',[$user]);
        return false; // To be safe: Default access is no access.
    }

    /**
     * @return bool
     */
    private function isLoggedInByShibboleth()
    {
        return is_array($this->authInfo['userSession']) && $this->authInfo['userSession']['tx_shibboleth_shibbolethsessionid'];
    }

    /**
     * @return bool
     */
    private function isLoggedInByNonShibboleth()
    {
        return is_array($this->authInfo['userSession']) && !$this->authInfo['userSession']['tx_shibboleth_shibbolethsessionid'];
    }

    /**
     * @return bool
     */
    private function applicationHasMatchingShibbolethSession()
    {
        if (!$this->hasShibbolethSession) {
            return false;
        }
        $configurationKey = $this->authInfo['loginType'].'_applicationID';
        if (($this->configuration[$configurationKey] !== '') &&
            ($this->configuration[$configurationKey] !== $_SERVER[$this->shibApplicationIdKey])) {
            $this->logger->debug(
                $this->mode . ': Shibboleth session appliation ID ' . $_SERVER[$this->shibApplicationIdKey] .
                ' does not match required '. $configurationKey. ' (' .
                $this->configuration[$configurationKey].
                ') - see extra data for environment variables',
                [$_SERVER]
            );
            return false;
        }
        return true;
    }

    private function logoffPresentUser() {
        $this->logger->debug(
            $this->mode . ': have a non-matching Shibboleth user logged in - logout! - for session id see extra data',
            [$this->authInfo['userSession']]
        );
        $this->pObj->logoff();
    }

}

