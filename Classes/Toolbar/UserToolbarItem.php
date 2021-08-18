<?php

namespace TrustCnct\Shibboleth\Toolbar;

/**
 * Hook for implementing a toolbar item that modifies the logout button to redirect
 * to a configurable URL after logout
 *
 * @author    Andreas Groth <andreas.groth@tum.de>
 * @package    TYPO3
 * @subpackage    tx_shibboleth
 */

use TYPO3\CMS\Backend\Domain\Repository\Module\BackendModuleRepository;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class UserToolbarItem extends \TYPO3\CMS\Backend\Backend\ToolbarItems\UserToolbarItem
{

    /**
     * @var \TYPO3\CMS\Backend\Domain\Repository\Module\BackendModuleRepository $backendModuleRepository
     */
    protected $backendModuleRepository;

    /**
     * Render drop down
     *
     * @return string HTML
     */
    public function getDropDown()
    {
        if (!$this->isShibbolethUser()) {
            return parent::getDropDown();
        }
        $redirect_url = $this->getSecureLogoutRedirectUrl();

        if (!$this->backendModuleRepository) {
            $this->backendModuleRepository = GeneralUtility::makeInstance(BackendModuleRepository::class);
        }

        $view = $this->getFluidTemplateObject('UserToolbarItemDropDown.html');
        $view->assignMultiple([
            'modules' => $this->backendModuleRepository->findByModuleName('user')->getChildren(),
            'logoutUrl' => $redirect_url,
            'switchUserMode' => $this->getBackendUser()->user['ses_backuserid'],
        ]);
        return $view->render();
    }

    /**
     * Returns additional attributes for the list item in the toolbar
     * (Must be implemented.)
     *
     * @return array List item HTML attibutes
     */
    public function getAdditionalAttributes() {
        $attributes = parent::getAdditionalAttributes();
        $attributes[] = 'id="tx-shibboleth-logout"';
        return $attributes;
    }

	/**
	 * @return string
	 */
	protected function getSecureLogoutRedirectUrl()
	{
        if (!$this->isShibbolethUser()) {
            return '';
        }
		$configuration = GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('shibboleth');
		$redirect_url = trim($configuration['BE_logoutRedirectUrl']);
		if (strpos($redirect_url,'Logout?return=') === FALSE) {
			$redirect_url = '/'.trim($configuration['sessions_handlerURL']).'/Logout?return='.$redirect_url;
		}
		return $redirect_url;
	}

    /**
     * @return mixed
     */
    protected function isShibbolethUser()
    {
        return $GLOBALS['BE_USER']->user['tx_shibboleth_shibbolethsessionid'];
    }

}
