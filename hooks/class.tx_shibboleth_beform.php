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
 * Hook for redirecting to the shibboleth authentication or modifying the backend form.
 *
 * @author	Irene Höppner <irene.hoeppner@abezet.de>
 * @author	Andreas Groth <andreas.groth@tum.de>
 * @package	TYPO3
 * @subpackage	tx_shibboleth
 */

class tx_shibboleth_beform {

	/**
	 * @return void
	 */
	public function process() {
		$extConf =  unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['shibboleth']);

		if (GeneralUtility::_GET('redirecttoshibboleth') == 'yes') {
				// Redirect to Shibboleth login
			$entityIDparam = $extConf['entityID'];
			if ($entityIDparam != '') {
				$entityIDparam = '?entityID='. rawurldecode($entityIDparam);
			}
			$typo3_site_url = GeneralUtility::getIndpEnv('TYPO3_SITE_URL');
			if ($extConf['forceSSL']) {
				$typo3_site_url = str_replace('http://', 'https://', $typo3_site_url);
			}
			$sessionHandlerUrl = $extConf['sessions_handlerURL'];
			if (preg_match('/^http/',$sessionHandlerUrl) == 0) {
				$sessionHandlerUrl = $typo3_site_url . $sessionHandlerUrl;
			}
			$shiblinkUrl = $sessionHandlerUrl . $extConf['sessionInitiator_Location'] . '?target=' . rawurlencode(GeneralUtility::getIndpEnv('TYPO3_SITE_URL')) . 'typo3/' . $entityIDparam;
			\TYPO3\CMS\Core\Utility\HttpUtility::redirect($shiblinkUrl, \TYPO3\CMS\Core\Utility\HttpUtility::HTTP_STATUS_302);
		}

			// Else: Display local login form
		if ($extConf['BE_loginTemplatePath']) {
			$GLOBALS['TBE_TEMPLATE']->moduleTemplate = $GLOBALS['TBE_TEMPLATE']->getHtmlTemplate(PATH_site.$extConf['BE_loginTemplatePath']);
		}
		if ($extConf['BE_loginTemplateCss']) {
			$GLOBALS['TBE_TEMPLATE']->getPageRenderer()->addCssFile($extConf['BE_loginTemplateCss']);
		}

		return NULL;
	}

}

?>
