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
	var $mode=''; //FE or BE
	var $user='';
	var $db_user='';
	var $db_group='';
	
	function __construct($mode, $db_user, $db_group) {
		t3lib_div::devlog('constructor','shibboleth',0,$db_user);
		$this->$mode = $mode;
		$this->$db_user = $db_user;
		$this->$db_group = $db_group;
	}
	
	function getUserFromDB() {
		t3lib_div::devlog('inGetUserFromDB','shibboleth');
		// TODO: user ID configuration
		$conf = $this->getTyposcriptConfiguration();

		$where = 'username=' . $_SERVER['REMOTE_USER'] . ' ';
		$where .= $this->db_user['enable_clause'] . ' ';
		if($this->db_user['checkPidList']) {
			$where .= $this->db_user['check_pid_clause'];
		}
		//$GLOBALS['TYPO3_DB']->debugOutput = TRUE;
		$table = $this->db_user['table'];
		$groupBy = '';
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery (
			'*',
			$table,
			$where
		);
		if ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))  {
			return $row;
		} else {
			return false;
		}
	}
	
	function mapShibbolethAttributesToUserArray($user) {
			// TODO: Shibboleth-username prefix/postfix
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
		$configArr = $parser->setup[$this->mode . '.'];
		$configArr = $parser->setup;
		t3lib_div::devlog('parsed TypoScript','shibboleth',0,$configArr);
		
		return $configArr;
		
		
			// TODO: wrong place here ;-)
		$localcObj = t3lib_div::makeInstance('tslib_cObj');
		#$GLOBALS['TSFE'] = t3lib_div::makeInstance('tslib_fe', $GLOBALS['TYPO3_CONF_VARS'], t3lib_div::_GET('id'), '0', 0, '','','','');
		#$GLOBALS['TSFE']->sys_page = t3lib_div::makeInstance('t3lib_pageSelect');
		$localcObj->start($_SERVER);
		
		t3lib_div::devlog('cObj data','shibboleth',0,$localcObj->data);
		
		return $parser->setup[$this->mode . '.'];
	}
}

?>