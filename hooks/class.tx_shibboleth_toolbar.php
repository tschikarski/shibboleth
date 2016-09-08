<?php
/**
 * Hook for implementing a toolbar item that modifies the logout button to redirect
 * to a configurable URL after logout
 *
 * @author	Andreas Groth <andreas.groth@tum.de>
 * @package	TYPO3
 * @subpackage	tx_shibboleth
 */

namespace TrustCnct\Shibboleth;

class tx_shibboleth_toolbar implements \TYPO3\CMS\Backend\Toolbar\ToolbarItemHookInterface {

	/**
	 * reference to the backend object
	 *
	 * @var TYPO3backend
	 */
	protected $backendReference;

	protected $EXTKEY = 'shibboleth';

	/**
	 * Constructor
	 *
	 * @param \TYPO3\CMS\Backend\Controller\BackendController TYPO3 backend object reference
	 */
	public function __construct(\TYPO3\CMS\Backend\Controller\BackendController &$backendReference = NULL) {
		$this->backendReference = $backendReference;
	}

	/**
	 * Whether the user has access to this toolbar item: always
	 * (Must be implemented.)
	 *
	 * @return boolean TRUE if user has access, FALSE if not
	 */
	public function checkAccess() {
		return TRUE;
	}

	/**
	 * Renders the toolbar item, adds JavaScript and CSS files
	 * (Must be implemented.)
	 *
	 * @return string The toolbar item as HTML
	 */
	public function render() {
		$extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$this->EXTKEY]);
		$redirect_url = trim($extConf['BE_logoutRedirectUrl']);

		$ret = array();
		if (!$redirect_url) {
			$ret [] = '<!-- empty -->';
		} else {
			// Add the neccessary JavaScript to the backend
			$this->backendReference->addJavascriptFile(
				\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath($this->EXTKEY) . 'res/modify_be.js'
			);
			// Add the necessary CSS to the backend
			$this->backendReference->addCssFile(
				'shibboleth',
					\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath($this->EXTKEY) . 'res/modify_be.css'
			);

			$redirect_url = str_replace('{HOSTNAME}', \TYPO3\CMS\Core\Utility\GeneralUtility::getHostname(), $redirect_url);
			$ret[] = '<div id="logout-button-shib" class="toolbar-item no-separator">';
			$ret[] = '<form action="logout.php" target="_top">';
			$ret[] = '<input id="tx_shibboleth-HiddenInputParam-redirect" type="hidden" name="redirect" value="' . htmlspecialchars($redirect_url) . '"/>';
			$ret[] = '<input type="submit" id="logout-submit-button-shib" value="Logout"/>';
			$ret[] = '</form>';
			$ret[] = '</div>';
		}
		return implode(LF, $ret);
	}

	/**
	 * Returns additional attributes for the list item in the toolbar
	 * (Must be implemented.)
	 *
	 * @return string List item HTML attibutes
	 */
	public function getAdditionalAttributes() {
		return 'id="tx-shibboleth-logout"';
	}

}
?>
