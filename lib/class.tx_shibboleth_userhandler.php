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

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Shibboleth user handler
 *
 * @author	Irene Höppner <irene.hoeppner@abezet.de>
 * @package	TYPO3
 * @subpackage	tx_shibboleth
 */

class tx_shibboleth_userhandler {
	var $writeDevLog;
	var $tsfeDetected = FALSE;
	var $loginType=''; //FE or BE
	var $user='';
	var $db_user='';
	var $db_group='';
	var $shibboleth_extConf;
	var $config; // typoscript like configuration for the current loginType
	var $cObj; // local cObj, needed to parse the typoscript configuration
	var $ShibSessionID;
	
	function __construct($loginType, $db_user, $db_group, $shibSessionIDname, $writeDevLog = FALSE) {
		global $TYPO3_CONF_VARS;
		$this->writeDevLog = $TYPO3_CONF_VARS['SC_OPTIONS']['shibboleth/lib/class.tx_shibboleth_userhandler.php']['writeMoreDevLog'] AND $writeDevLog;
		if ($this->writeDevLog) GeneralUtility::devlog('constructor','shibboleth');
		
		$this->shibboleth_extConf = unserialize($TYPO3_CONF_VARS['EXT']['extConf']['shibboleth']);
				
		$this->loginType = $loginType;
		$this->db_user = $db_user;
		$this->db_group = $db_group;
		$this->ShibSessionID = $shibSessionIDname;
		$this->config = $this->getTyposcriptConfiguration();
		
		if (is_object($GLOBALS['TSFE'])) {
			$this->tsfeDetected = TRUE;
		}
		$localcObj = GeneralUtility::makeInstance('tslib_cObj');
		$localcObj->start($_SERVER);
		if (!$this->tsfeDetected) {
			unset($GLOBALS['TSFE']);
		}
		
		$this->cObj = $localcObj;
		#if ($this->writeDevLog) GeneralUtility::devlog('cObj data','shibboleth',0,$this->cObj->data);
	}
	
	function getUserFromDB() {
		if ($this->writeDevLog) {
			GeneralUtility::devlog('getUserFromDB: start','shibboleth');
		}
		
		$idField = $this->config['IDMapping.']['typo3Field'];
		$idValue = $this->getSingle($this->config['IDMapping.']['shibID'],$this->config['IDMapping.']['shibID.']);
		
		$where = $idField . '=\'' . $idValue . '\' ';
			// Next line: Don't use "enable_clause", as it will also exclude hidden users, i.e. 
			// will create new users on every log in attempt until user is unhidden by admin.
		$where .= ' AND deleted = 0 ';
		if($this->db_user['checkPidList']) {
			$where .= $this->db_user['check_pid_clause'];
		}
		#if ($this->writeDevLog) GeneralUtility::devlog('userFromDB: where-statement','shibboleth',0,array($where));
		//$GLOBALS['TYPO3_DB']->debugOutput = TRUE;
		$table = $this->db_user['table'];
		$groupBy = '';
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery (
		#$sql = $GLOBALS['TYPO3_DB']->SELECTquery (
			'*',
			$table,
			$where
		);
		if ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))  {
			if ($this->writeDevLog) GeneralUtility::devlog('getUserFromDB returning user record ($row)','shibboleth',0,$row);
			return $row;
		} else {
			if ($this->writeDevLog) GeneralUtility::devlog('getUserFromDB returning FALSE (no record found)','shibboleth',0,$row);
			return false;
		}
	}
	
	function transferShibbolethAttributesToUserArray($user) {
		if ($this->writeDevLog) GeneralUtility::devlog('transferShibbolethAttributesToUserArray','shibboleth',0,array('user' => $user, 'this_config' => $this->config));
			// We will need part of the config array when writing user to DB in "synchronizeUserData"; let's put it into $user
		$user['tx_shibboleth_config'] = $this->config['userControls.'];
		$user['tx_shibboleth_shibbolethsessionid'] = $_SERVER[$this->ShibSessionID];
			
		$user['_allowUser'] = $this->getSingle($user['tx_shibboleth_config']['allowUser'],$user['tx_shibboleth_config']['allowUser.']);
		
			// Always create random password, as might have to fight against attempts to set a known password for the user.
		$user[$this->db_user['userident_column']] = 'shibb:' . sha1(mt_rand());

			// Force idField and idValue to be consistent with the IDMapping config, overwriting 
			// any possible mis-configuration from the other fields mapping entries
		$idField = $this->config['IDMapping.']['typo3Field'];
		$idValue = $this->getSingle($this->config['IDMapping.']['shibID'],$this->config['IDMapping.']['shibID.']);
		$user[$idField] = $idValue;
		
		if ($this->writeDevLog) GeneralUtility::devlog('transferShibbolethAttributesToUserArray: newUserArray','shibboleth',0,$user);
		return $user;
	}
	
	function synchronizeUserData($user) {
		if ($this->writeDevLog) GeneralUtility::devlog('synchronizeUserData','shibboleth',0,$user);
		
		if($user['uid']) {
				// User is in DB, so we have to update, therefore remove uid from DB record and save it for later
			$uid = $user['uid'];
			unset($user['uid']);
				// We have to update the tstamp field, in any case.
			$user['tstamp'] = time();
			
				// Don't automatically change groups after first creation
			foreach($user['tx_shibboleth_config']['updateUserFieldsMapping.'] as $field => $fieldConfig) {
				$newFieldValue = $this->getSingle($user['tx_shibboleth_config']['updateUserFieldsMapping.'][$field],$user['tx_shibboleth_config']['updateUserFieldsMapping.'][$field . '.']);
				if(substr(trim($field), -1) != '.') {
					$user[$field] = $newFieldValue;
				}
			}
				// Remove that data from $user - otherwise we get an error updating the user record in DB
			unset($user['tx_shibboleth_config']);
			
				// TODO: (On TUM server) Move and change working copy of config.txt

				// Update
			$table = $this->db_user['table'];
			$where = 'uid='.intval($uid);
			#$where=$GLOBALS['TYPO3_DB']->fullQuoteStr($inputString,$table);
			$fields_values = $user;
			$GLOBALS['TYPO3_DB']->exec_UPDATEquery(
				$table, 
				$where, 
				$fields_values
			);
		} else {
				// We will insert a new user
				// We have to set crdate and tstamp correctly
			$user['crdate'] = time();
			$user['tstamp'] = time();
			foreach($user['tx_shibboleth_config']['createUserFieldsMapping.'] as $field => $fieldConfig) {
				$newFieldValue = $this->getSingle($user['tx_shibboleth_config']['createUserFieldsMapping.'][$field],$user['tx_shibboleth_config']['createUserFieldsMapping.'][$field . '.']);
				if(substr(trim($field), -1) != '.') {
					$user[$field] = $newFieldValue;
				}
			}
				// Remove that data from $user - otherwise we get an error inserting the user record into DB
			unset($user['tx_shibboleth_config']);
				// Determine correct pid for new user
			if ($this->loginType == 'FE') {
				$user['pid'] = intval($this->shibboleth_extConf['FE_autoImport_pid']);
			} else {
				$user['pid'] = 0;
			}
				// In BE Autoimport might be done with disable=1, i.e. BE User has to be enabled manually after first login attempt.
			if ($this->loginType == 'BE' && $this->shibboleth_extConf['BE_autoImportDisableUser']) {
				$user['disable'] = 1;
			}
				// Insert
			$table = $this->db_user['table'];
			$insertFields = $user;
			$GLOBALS['TYPO3_DB']->exec_INSERTquery(
				$table, 
				$insertFields
			);
				// get uid
			$uid = $GLOBALS['TYPO3_DB']->sql_insert_id();
		}
		
		if ($this->writeDevLog) GeneralUtility::devLog('synchronizeUserData: After update/insert; $uid='.$uid,'shibboleth');
		return $uid;
	}
	
	function getTyposcriptConfiguration() {
		
		#$incFile = $GLOBALS['TSFE']->tmpl->getFileName($fName);
		#$GLOBALS['TSFE']->tmpl->fileContent($incFile);
		
		$configString = GeneralUtility::getURL(GeneralUtility::getIndpEnv('TYPO3_DOCUMENT_ROOT') . $this->shibboleth_extConf['mappingConfigPath']);
		
		if ($this->writeDevLog) GeneralUtility::devlog('configString','shibboleth',0,array($configString));
		
		$parser = GeneralUtility::makeInstance('t3lib_TSparser');
		$parser->parse($configString);

		$completeSetup = $parser->setup;

		if ($this->writeDevLog) GeneralUtility::devlog('loginType','shibboleth',0,array($this->loginType));
		
		$localSetup = $completeSetup['tx_shibboleth.'][$this->loginType . '.'];
		if ($this->writeDevLog) GeneralUtility::devlog('parsed TypoScript','shibboleth',0,$localSetup);
		
		return $localSetup;
	}
	
	function getSingle($conf,$subconf='') {
		if ($this->writeDevLog) GeneralUtility::devlog('getSingle ($conf,$subconf)','shibboleth',0,array('conf' => $conf, 'subconf' => $subconf));
		if(is_array($subconf)) {
			if ($GLOBALS['TSFE']->cObjectDepthCounter == 0) {
				$GLOBALS['TSFE']->cObjectDepthCounter = 100;
			}
			$result = $this->cObj->cObjGetSingle($conf, $subconf);
		} else {
			$result = $conf;
		}
		if (!$this->tsfeDetected) {
			unset($GLOBALS['TSFE']);
		}
		if ($this->writeDevLog) GeneralUtility::devlog('getSingle ($result)','shibboleth',0,array('result' => $result));
		return $result;
	}
	
	/**
	 * Creating a single static cached instance of TSFE to use with this class.
	 *
	 * @return	tslib_fe		New instance of tslib_fe
	 */
	private static function getTSFE() {
		// Cached instance
		static $tsfe = null;

		if (is_null($tsfe)) {
			$tsfe = GeneralUtility::makeInstance('tslib_fe', $GLOBALS['TYPO3_CONF_VARS'], 0, 0);
		}

		return $tsfe;
	}
}

?>