<?php

namespace TrustCnct\Shibboleth\Toolbar;

/**
 * Hook for implementing a toolbar item that modifies the logout button to redirect
 * to a configurable URL after logout
 *
 * @author	Andreas Groth <andreas.groth@tum.de>
 * @package	TYPO3
 * @subpackage	tx_shibboleth
 */

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Backend\Domain\Repository\Module\BackendModuleRepository;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Imaging\Icon;

class UserToolbarItem extends \TYPO3\CMS\Backend\Backend\ToolbarItems\UserToolbarItem {

	/**
	 * reference to the backend object
	 *
	 * @var TYPO3backend
	 */
	protected $backendReference;

	protected $EXTKEY = 'shibboleth';

	/**
	 * Render drop down
	 *
	 * @return string HTML
	 */
	public function getDropDown()
	{
		$backendUser = $this->getBackendUser();
		$languageService = $this->getLanguageService();

		$dropdown = array();
		$dropdown[] = '<ul class="dropdown-list">';

		$extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$this->EXTKEY]);
		$redirect_url = trim($extConf['BE_logoutRedirectUrl']);
		if (!$redirect_url) {
			$urlParameters = array();
		} else {
			$urlParameters = array(
				'redirect' => htmlspecialchars($redirect_url)
			);
		}

		/** @var BackendModuleRepository $backendModuleRepository */
		$backendModuleRepository = GeneralUtility::makeInstance(BackendModuleRepository::class);
		/** @var \TYPO3\CMS\Backend\Domain\Model\Module\BackendModule $userModuleMenu */
		$userModuleMenu = $backendModuleRepository->findByModuleName('user');
		if ($userModuleMenu != false && $userModuleMenu->getChildren()->count() > 0) {
			foreach ($userModuleMenu->getChildren() as $module) {
				/** @var BackendModule $module */
				$dropdown[] ='<li'
					. ' id="' . htmlspecialchars($module->getName()) . '"'
					. ' class="typo3-module-menu-item submodule mod-' . htmlspecialchars($module->getName()) . '" '
					. ' data-modulename="' . htmlspecialchars($module->getName()) . '"'
					. ' data-navigationcomponentid="' . htmlspecialchars($module->getNavigationComponentId()) . '"'
					. ' data-navigationframescript="' . htmlspecialchars($module->getNavigationFrameScript()) . '"'
					. ' data-navigationframescriptparameters="' . htmlspecialchars($module->getNavigationFrameScriptParameters()) . '"'
					. '>';
				$dropdown[] = '<a title="' . htmlspecialchars($module->getDescription()) . '" href="' . htmlspecialchars($module->getLink()) . '" class="dropdown-list-link modlink">';
				$dropdown[] = '<span class="submodule-icon typo3-app-icon"><span><span>' . $module->getIcon() . '</span></span></span>';
				$dropdown[] = '<span class="submodule-label">' . htmlspecialchars($module->getTitle()) . '</span>';
				$dropdown[] = '</a>';
				$dropdown[] = '</li>';
			}
			$dropdown[] = '<li class="divider"></li>';
		}

		// Logout button
		$buttonLabel = 'LLL:EXT:lang/locallang_core.xlf:' . ($backendUser->user['ses_backuserid'] ? 'buttons.exit' : 'buttons.logout');
		$dropdown[] = '<li class="reset-dropdown">';
		$dropdown[] = '<a href="' . htmlspecialchars(BackendUtility::getModuleUrl('logout', $urlParameters)) . '" class="btn btn-danger pull-right" target="_top">';
		$dropdown[] = $this->iconFactory->getIcon('actions-logout', Icon::SIZE_SMALL)->render('inline') . ' ';
		$dropdown[] = $languageService->sL($buttonLabel, true);
		$dropdown[] = '</a>';
		$dropdown[] = '</li>';

		$dropdown[] = '</ul>';

		return implode(LF, $dropdown);
	}

	/**
	 * Returns additional attributes for the list item in the toolbar
	 * (Must be implemented.)
	 *
	 * @return string List item HTML attibutes
	 */
	public function getAdditionalAttributes() {
		$attributes = parent::getAdditionalAttributes();
		$attributes[] = 'id="tx-shibboleth-logout"';
		return $attributes;
	}

}
?>
