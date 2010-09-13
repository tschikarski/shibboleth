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
	
	function __construct($mode, $db_user = '', $db_group = '') {
		$this->$mode = $mode;
		$this->$db_user = $db_user;
		$this->$db_group = $db_group;
	}
	
	function getUserFromDB() {
		t3lib_div::devlog('inGetUserFromDB','shibboleth',0,$_SERVER);
		return $row;
	}
	
	function mapShibbolethAttributesToUserArray($user) {
		
		return $user;
	}
	
	function synchronizeUserData($user) {
		
		return $uid;
	}
}

?>