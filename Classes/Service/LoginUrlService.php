<?php
/**
 * Created by PhpStorm.
 * User: tschikarski
 * Date: 09.06.17
 * Time: 19:28
 */

namespace TrustCnct\Shibboleth\Service;

use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class LoginUrlService
{
    protected $configuration;

    public function __construct()
    {
        $this->configuration =  GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('shibboleth');
    }

    /**
     * @return string
     */
    public function createUrl()
    {
        $entityIDparam = $this->configuration['entityID'];
        if ($entityIDparam != '') {
            $entityIDparam = 'entityID='. rawurldecode($entityIDparam);
        }

        $typo3_site_url = GeneralUtility::getIndpEnv('TYPO3_SITE_URL');

        if ($this->configuration['forceSSL']) {
            $typo3_site_url = str_replace('http://', 'https://', $typo3_site_url);
        }

        $sessionHandlerUrl = $this->configuration['sessions_handlerURL'];

        if (0 !== strpos($sessionHandlerUrl, "http")) {
            $sessionHandlerUrl = $typo3_site_url . $sessionHandlerUrl;
        }

        $targetParam = 'target=' . rawurlencode($typo3_site_url);

        if (($entityIDparam !== '') && ($targetParam !== '')) {
            $params = $entityIDparam . '&' . $targetParam;
        } else {
            $params = $entityIDparam . $targetParam;
        }

        if ($params !== '') {
            $params = '?' . $params;
        }

        return $sessionHandlerUrl . $this->configuration['sessionInitiator_Location'] . $params;
    }
}
