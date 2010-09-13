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
 * Hook for creating the link to the shibboleth authentication in the backend form.
 *
 * @author	Irene Höppner <irene.hoeppner@abezet.de>
 * @package	TYPO3
 * @subpackage	tx_shibboleth
 */

class tx_shibboleth_beform {
	function addShibbolethJavaScript($params, $pObj) {
		global $TYPO3_CONF_VARS;
		$extConf = unserialize($TYPO3_CONF_VARS['EXT']['extConf']['shibboleth']);
		$function = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['shibboleth']['originalLoginScriptHook'];
		$params = array();
		$scriptCode = t3lib_div::callUserFunction($function, $params, $pObj);
#debug($formCode);
		$shiblinkUrl = 'http://' . t3lib_div::getIndpEnv('HTTP_HOST') . '' . $extConf['sessions_handlerURL'] . $extConf['sessionInitiator_Location'] . '?target=http%3A%2F%2F' . t3lib_div::getIndpEnv('HTTP_HOST') . '/typo3/';
			// add jquery core
		$scriptCode .= '<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4/jquery.min.js"></script>';
			// add custom jquery
		$scriptCode .= '<script type="text/javascript">
		//<![CDATA[
		$(document).ready(function() {
			$(\'#t3-login-form-fields\').before(\'<h1><a href="' . $shiblinkUrl . '">Login with Shibboleth</a></h1>\');
			$(\'#t3-login-form-fields\').before(\'<a href="" id="toggleLoginForm">Login with the TYPO3 login form</a>\');
			$(\'#t3-login-form-fields\').hide();
			$(\'#toggleLoginForm\').click(function(){
				$(\'#t3-login-form-fields\').toggle();
				return false;
			});
			
		});
		//]]>
		</script>';
		
		
		
		return $scriptCode;
	}
}

?>