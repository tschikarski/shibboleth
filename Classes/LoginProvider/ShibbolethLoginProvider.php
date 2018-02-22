<?php

namespace TrustCnct\Shibboleth\LoginProvider;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use TYPO3\CMS\Backend\Controller\LoginController;
use TYPO3\CMS\Backend\LoginProvider\LoginProviderInterface;
use TYPO3\CMS\Backend\LoginProvider\UsernamePasswordLoginProvider;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;

/**
 * Class ShibbolethLoginProvider
 *
 * @package TrustCnct\Shibboleth\LoginProvider
 */
class ShibbolethLoginProvider extends UsernamePasswordLoginProvider
{
    public function render(StandaloneView $view, PageRenderer $pageRenderer, LoginController $loginController)
    {
        $extConf =  unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['shibboleth']);

        if (GeneralUtility::_GET('redirecttoshibboleth') == 'yes') {
            // Redirect to Shibboleth login
            $typo3SiteUrlParams = array();
            $typo3SiteUrlParams[] = 'login_status=login';
            $entityIDparam = $extConf['entityID'];
            if ($entityIDparam != '') {
                $typo3SiteUrlParams[] = 'entityID='. rawurldecode($entityIDparam);
            }
            $typo3_site_url = GeneralUtility::getIndpEnv('TYPO3_SITE_URL');
            if ($extConf['forceSSL']) {
                $typo3_site_url = str_replace('http://', 'https://', $typo3_site_url);
            }
            $sessionHandlerUrl = $extConf['sessions_handlerURL'];
            if (preg_match('/^http/',$sessionHandlerUrl) == 0) {
                $sessionHandlerUrl = $typo3_site_url . $sessionHandlerUrl;
            }
            $typo3SiteUrlParamString = implode('&', $typo3SiteUrlParams);
            if ($typo3SiteUrlParamString != '') {
                $typo3SiteUrlParamString = '?' . $typo3SiteUrlParamString;
            }
            $shiblinkUrl = $sessionHandlerUrl . $extConf['sessionInitiator_Location'] . '?target=' . rawurlencode(GeneralUtility::getIndpEnv('TYPO3_SITE_URL')) .
                    'typo3/' . $typo3SiteUrlParamString;
            \TYPO3\CMS\Core\Utility\HttpUtility::redirect($shiblinkUrl, \TYPO3\CMS\Core\Utility\HttpUtility::HTTP_STATUS_302);
        }

        parent::render($view,$pageRenderer,$loginController);
        $templatePathAndFilename = GeneralUtility::getFileAbsFileName(PATH_site . $extConf['BE_loginTemplatePath']);
        if (is_file($templatePathAndFilename)) {
            $view->setTemplatePathAndFilename($templatePathAndFilename);
            $newLayoutRootPaths = $view->getLayoutRootPaths();
            $extPath = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('shibboleth');
            $newLayoutRootPaths[] = $extPath . 'Resources/Private/Layouts';
            $view->setLayoutRootPaths($newLayoutRootPaths);
        } else {
            throw new \TYPO3\CMS\Extbase\Configuration\Exception\NoSuchFileException('BE_loginTemplatePath: File not found', 1473848139);
        }
    }

}