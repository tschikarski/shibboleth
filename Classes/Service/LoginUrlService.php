<?php
/**
 * Created by PhpStorm.
 * User: tschikarski
 * Date: 09.06.17
 * Time: 19:28
 */

namespace TrustCnct\Shibboleth\Service;

use TYPO3\CMS\Core\Utility\GeneralUtility;

class LoginUrlService
{
    protected $extConf;

    public function __construct()
    {
        $this->extConf =  unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['shibboleth']);

    }

    /**
     * @return string
     */
    public function createLink()
    {
        $entityIDparam = $this->extConf['entityID'];
        if ($entityIDparam != '') {
            $entityIDparam = 'entityID='. rawurldecode($entityIDparam);
        }

        $typo3_site_url = GeneralUtility::getIndpEnv('TYPO3_SITE_URL');
        if ($this->extConf['forceSSL']) {
            $typo3_site_url = str_replace('http://', 'https://', $typo3_site_url);
        }

        // TODO: hard-coded link text shall be replaced by locallang.xml based, piGetLL or something like that
        $linkText = 'Shibboleth Login';

        $sessionHandlerUrl = $this->extConf['sessions_handlerURL'];

        if (preg_match('/^http/',$sessionHandlerUrl) == 0) {
            $sessionHandlerUrl = $typo3_site_url . $sessionHandlerUrl;
        }

        $targetParam = 'target=' . rawurlencode(GeneralUtility::getIndpEnv('TYPO3_SITE_URL'));

        if (($entityIDparam != '') and ($targetParam != '')) {
            $params = $entityIDparam . '&' . $targetParam;
        } else {
            $params = $entityIDparam . $targetParam;
        }

        if ($params != '') {
            $params = '?' . $params;
        }

        $linkUrl = $sessionHandlerUrl . $this->extConf['sessionInitiator_Location'] . $params;

        return $linkUrl;

    }
}