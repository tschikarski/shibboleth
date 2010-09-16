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
 * Shibboleth user handler
 *
 * @author	Irene Höppner <irene.hoeppner@abezet.de>
 * @package	TYPO3
 * @subpackage	tx_shibboleth
 */

class tx_shibboleth_userhandler {
	var $writeDevLog;
	var $loginType=''; //FE or BE
	var $user='';
	var $db_user='';
	var $db_group='';
	var $config; // typoscript like configuration for the current loginType
	var $cObj; // local cObj, needed to parse the typoscript configuration
	
	function __construct($loginType, $db_user, $db_group) {
		global $TYPO3_CONF_VARS;
		$this->writeDevLog = $TYPO3_CONF_VARS['SC_OPTIONS']['shibboleth/lib/class.tx_shibboleth_userhandler.php']['writeDevLog'];
		
		if ($this->writeDevLog) t3lib_div::devlog('constructor','shibboleth',0,$db_user);
		
		$this->loginType = $loginType;
		$this->db_user = $db_user;
		$this->db_group = $db_group;
		$this->config = $this->getTyposcriptConfiguration();
		
		$localcObj = t3lib_div::makeInstance('tslib_cObj');
		$localcObj->start($_SERVER);
		
		$this->cObj = $localcObj;
		if ($this->writeDevLog) t3lib_div::devlog('cObj data','shibboleth',0,$this->cObj->data);
	}
	
	function getUserFromDB() {
		if ($this->writeDevLog) t3lib_div::devlog('getUserFromDB','shibboleth');
		
		$idField = $this->config['IDMapping.']['typo3Field'];
		$idValue = $this->getSingle($this->config['IDMapping.']['shibID'],$this->config['IDMapping.']['shibID.']);
			// TODO: Shibboleth-username prefix/postfix

		$where = $idField . '=\'' . $idValue . '\' ';
		$where .= $this->db_user['enable_clause'] . ' ';
		if($this->db_user['checkPidList']) {
			$where .= $this->db_user['check_pid_clause'];
		}
		if ($this->writeDevLog) t3lib_div::devlog('userFromDB:where-statement','shibboleth',0,array($where));
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
			return $row;
			if ($this->writeDevLog) t3lib_div::devlog('userFromDB','shibboleth',0,$row);
		} else {
			return false;
		}
	}
	
	function mapShibbolethAttributesToUserArray($user) {
		if ($this->writeDevLog) t3lib_div::devlog('mapShibbolethAttributesToUserArray','shibboleth',0,array('user' => $user, 'this_config' => $this->config));
		foreach($this->config['fieldsMapping.'] as $field => $fieldConfig) {
			$newFieldValue = $this->getSingle($this->config['fieldsMapping.'][$field],$this->config['fieldsMapping.'][$field . '.']);
			if(substr(trim($field), -1) != '.') {
				$user[$field] = $newFieldValue;
			}
		}
			// TODO: Shibboleth-username prefix/postfix
			// TODO: For TS config of usergroup field: We can't use $db_user['usergroup_column'] in our Typoscript, right? (= TS needs to use "usergroup" hard-coded.)
		
		$user['tx_shibboleth_shibbolethsessionid'] = $_SERVER['Shib-Session-ID'];
			// TODO: Think about field definition TINYTEXT (255 chars), enough?
			// TODO: Hard-coded ok? Do it here, or in getUser?

			// TODO: Password random
		$user[$this->db_user['userident_column']] = sha1(mt_rand());
			// Setting idfield and idvalue to make sure that they are the same like in getUserFromDB
		$idField = $this->config['IDMapping.']['typo3Field'];
		$idValue = $this->getSingle($this->config['IDMapping.']['shibID'],$this->config['IDMapping.']['shibID.']);
		$user[$idField] = $idValue;
		if ($this->writeDevLog) t3lib_div::devlog('mapShibbolethAttributesToUserArray: newUserArray','shibboleth',0,$user);
		return $user;
	}
	
	function synchronizeUserData($user) {
		if ($this->writeDevLog) t3lib_div::devlog('synchronizeUserData','shibboleth',0,$user);
		
		if($user['uid']) {
			$uid = $user['uid'];
			unset($user['uid']);
				// Prepare the user data a bit
				// TODO: check, what has to be prepared, too
			$user['tstamp'] = time();
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
				// Prepare the user data a bit
				// TODO: check, what has to be prepared, too
				// .... crdate!!!
			$user['crdate'] = time();
			$user['tstamp'] = time();
				// TODO: TYPO3_CONF_VAR for pid
			$user['pid'] = 29;
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
		return $uid;
	}
	
	function getTyposcriptConfiguration() {
		
		#$incFile = $GLOBALS['TSFE']->tmpl->getFileName($fName);
		#$GLOBALS['TSFE']->tmpl->fileContent($incFile);
		
			// TODO: put path in typo3confvars
		$configString = t3lib_div::getURL(t3lib_extMgm::extPath('shibboleth') . 'res/config.txt');

		if ($this->writeDevLog) t3lib_div::devlog('configString','shibboleth',0,array($configString));

		if(!class_exists('t3lib_TSparser') && defined('PATH_t3lib')) {
			require_once(PATH_t3lib.'class.t3lib_TSparser.php');
		}
		$parser = t3lib_div::makeInstance('t3lib_TSparser');
		$parser->parse($configString);

		$completeSetup = $parser->setup;

		if ($this->writeDevLog) t3lib_div::devlog('loginType','shibboleth',0,array($this->loginType));
		
		$localSetup = $completeSetup['tx_shibboleth.'][$this->loginType . '.'];
		if ($this->writeDevLog) t3lib_div::devlog('parsed TypoScript','shibboleth',0,$localSetup);
		
		return $localSetup;
	}
	
	function getSingle($conf,$subconf='') {
		if(is_array($subconf)) {
			$result = $this->cObj->cObjGetSingle($conf, $subconf);
		} else {
			$result = $conf;
		}
		return $result;
	}
}

?>