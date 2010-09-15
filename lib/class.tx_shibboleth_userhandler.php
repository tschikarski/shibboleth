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
		//t3lib_div::devlog('constructor: SC_OPTIONS','shibboleth',0,$TYPO3_CONF_VARS['SC_OPTIONS']);
		$this->writeDevLog = 1; // TODO: Get from config var $TYPO3_CONF_VARS['SC_OPTIONS']['shibboleth/lib/class.tx_shibboleth_userhandler.php']['writeDevLog']
		if ($this->writeDevLog) t3lib_div::devlog('constructor','shibboleth',0,$db_user);
		$this->loginType = $loginType;
		$this->db_user = $db_user;
		$this->db_group = $db_group;
		$this->config = $this->getTyposcriptConfiguration();
		
		$localcObj = t3lib_div::makeInstance('tslib_cObj');
		$localcObj->start($_SERVER);
		
		$this->cObj = $localcObj;
		t3lib_div::devlog('cObj data','shibboleth',0,$this->cObj->data);
	}
	
	function getUserFromDB() {
		t3lib_div::devlog('getUserFromDB','shibboleth');
		
		$idField = $this->config['IDMapping.']['typo3Field'];
		$idValue = $this->getSingle($this->config['IDMapping.']['shibID'],$this->config['IDMapping.']['shibID.']);

		$where = $idField . '=\'' . $idValue . '\' ';
		$where .= $this->db_user['enable_clause'] . ' ';
		if($this->db_user['checkPidList']) {
			$where .= $this->db_user['check_pid_clause'];
		}
		t3lib_div::devlog('userFromDB:where-statement','shibboleth',0,array($where));
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
			t3lib_div::devlog('userFromDB','shibboleth',0,$row);
		} else {
			return false;
		}
	}
	
	function mapShibbolethAttributesToUserArray($user) {
			// TODO: Shibboleth-username prefix/postfix
		t3lib_div::devlog('mapShibbolethAttributesToUserArray','shibboleth',0,array('user' => $user, 'this_config' => $this->config));
			// TODO: Shib-Sessin-ID mit speichern
		return $user;
	}
	
	function synchronizeUserData($user) {
		
		return $uid;
	}
	
	function getTyposcriptConfiguration() {
		
		#$incFile = $GLOBALS['TSFE']->tmpl->getFileName($fName);
		#$GLOBALS['TSFE']->tmpl->fileContent($incFile);
		
			// TODO: put path in typo3confvars
		$configString = t3lib_div::getURL(t3lib_extMgm::extPath('shibboleth') . 'res/config.txt');

		t3lib_div::devlog('configString','shibboleth',0,array($configString));

		if(!class_exists('t3lib_TSparser') && defined('PATH_t3lib')) {
			require_once(PATH_t3lib.'class.t3lib_TSparser.php');
		}
		$parser = t3lib_div::makeInstance('t3lib_TSparser');
		$parser->parse($configString);
		$completeSetup = $parser->setup;
		t3lib_div::devlog('mode','shibboleth',0,array($this->loginType));
		$localSetup = $completeSetup['tx_shibboleth.'][$this->loginType . '.'];
		t3lib_div::devlog('parsed TypoScript','shibboleth',0,$localSetup);
		
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