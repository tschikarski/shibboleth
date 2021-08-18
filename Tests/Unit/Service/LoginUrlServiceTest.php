<?php

namespace TrustCnct\Shibboleth\Tests\Unit\Service;

use TrustCnct\Shibboleth\Service\LoginUrlService;

class LoginUrlServiceTest extends \Nimut\TestingFramework\TestCase\UnitTestCase
{
    protected $configuration;

    /**
     * @var LoginUrlService
     */
    protected $loginUrlService = null;

    protected function setUp()
    {
        parent::setUp();
        $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['shibboleth'] = [
            'BE_applicationID' => '',
            'BE_autoImport' => '0',
            'BE_autoImportDisableUser' => '1',
            'BE_disabledUserRedirectUrl' => '/typo3conf/ext/shibboleth/Resources/Public/LogoutPages/nologinyet.html',
            'BE_enable' => '0',
            'BE_loginTemplatePath' => 'typo3conf/ext/shibboleth/Resources/Private/Templates/BeForm/login.html',
            'BE_logoutRedirectUrl' => '/typo3conf/ext/shibboleth/Resources/Public/LogoutPages/logout.html',
            'FE_applicationID' => '',
            'FE_autoImport' => '0',
            'FE_autoImport_pid' => '',
            'FE_enable' => '0',
            'debugLog' => '0',
            'enableAlwaysFetchUser' => '1',
            'entityID' => '',
            'forceSSL' => '0',
            'mappingConfigPath' => '/typo3conf/ext/shibboleth/Resources/Private/config.txt',
            'pageUidForTSFE' => '1',
            'sessionInitiator_Location' => '/Login',
            'sessions_handlerURL' => 'Shibboleth.sso',
        ];
        $this->loginUrlService = new LoginUrlService();
    }

    protected function tearDown()
    {
        unset($GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['shibboleth']);
        parent::tearDown();
    }

    protected function getParameterArrayFromUrl($url) {
        if (strpos($url,'?') === FALSE) return array();
        preg_match('|\?(.*)$|', $url, $matches);
        $parameterAssignments = explode('&', $matches[1]);
        $parameters = array();
        foreach ($parameterAssignments as $pa) {
            list($key, $value) = explode('=', $pa, 2);
            $parameters[$key] = $value;
        }
        return $parameters;
    }

    /**
     * @test
     */
    public function loginLinkProtocolIsHttp() {
        preg_match('|^(.+)\:.*|',$this->loginUrlService->createUrl(),$matches);
        $this->assertRegExp('|http|', $matches[1]);
    }

    /**
     * @test
     */
    public function loginLinkProtocolIsForcedHttps() {
        $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['shibboleth'] = [
            'BE_applicationID' => '',
            'BE_autoImport' => '0',
            'BE_autoImportDisableUser' => '1',
            'BE_disabledUserRedirectUrl' => '/typo3conf/ext/shibboleth/Resources/Public/LogoutPages/nologinyet.html',
            'BE_enable' => '0',
            'BE_loginTemplatePath' => 'typo3conf/ext/shibboleth/Resources/Private/Templates/BeForm/login.html',
            'BE_logoutRedirectUrl' => '/typo3conf/ext/shibboleth/Resources/Public/LogoutPages/logout.html',
            'FE_applicationID' => '',
            'FE_autoImport' => '0',
            'FE_autoImport_pid' => '',
            'FE_enable' => '0',
            'debugLog' => '0',
            'enableAlwaysFetchUser' => '1',
            'entityID' => '',
            'forceSSL' => '1',
            'mappingConfigPath' => '/typo3conf/ext/shibboleth/Resources/Private/config.txt',
            'pageUidForTSFE' => '1',
            'sessionInitiator_Location' => '/Login',
            'sessions_handlerURL' => 'Shibboleth.sso',
        ];
        $this->loginUrlService = new LoginUrlService();
        preg_match('|^(.+)\:.*|',$this->loginUrlService->createUrl(),$matches);
        $this->assertRegExp('|https|', $matches[1]);
    }

    /**
     * @test
     */
    public function loginLinkContainsShibbolethHandlerUrl() {
        $this->assertRegExp('|/'.$this->configuration['sessions_handlerURL'].'/|', $this->loginUrlService->createUrl());
    }

    /**
     * @test
     */
    public function loginLinkContainsSessionInitiatorLocation() {
        $link = $this->loginUrlService->createUrl();
        $this->assertNotEmpty($link);
        $this->assertRegExp('|/'.$this->configuration['sessionsInitiator_location'].'|', $link);
    }

    /**
     * @test
     */
    public function loginLinkContainsTargetParameter() {
        $parameters = $this->getParameterArrayFromUrl($this->loginUrlService->createUrl());
        $this->assertArrayHasKey('target',$parameters);
    }

    /**
     * @test
     */
    public function loginLinkTargetParameterIsUrl() {
        $parameters = $this->getParameterArrayFromUrl($this->loginUrlService->createUrl());
        $targetUrl = urldecode($parameters['target']);
        $this->assertRegExp('|^https?://.*$|', $targetUrl,'Link target must have URL format.');
    }

    /**
     * @test
     */
    public function loginLinkContainsNoEntityIdParameter() {
        $parameters = $this->getParameterArrayFromUrl($this->loginUrlService->createUrl());
        $this->assertArrayNotHasKey('entityID',$parameters);
    }

    /**
     * @test
     */
    public function loginLinkContainsOptionalEntityIdParameter() {
        $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['shibboleth'] = [
            'BE_applicationID' => '',
            'BE_autoImport' => '0',
            'BE_autoImportDisableUser' => '1',
            'BE_disabledUserRedirectUrl' => '/typo3conf/ext/shibboleth/Resources/Public/LogoutPages/nologinyet.html',
            'BE_enable' => '0',
            'BE_loginTemplatePath' => 'typo3conf/ext/shibboleth/Resources/Private/Templates/BeForm/login.html',
            'BE_logoutRedirectUrl' => '/typo3conf/ext/shibboleth/Resources/Public/LogoutPages/logout.html',
            'FE_applicationID' => '',
            'FE_autoImport' => '0',
            'FE_autoImport_pid' => '',
            'FE_enable' => '0',
            'debugLog' => '0',
            'enableAlwaysFetchUser' => '1',
            'entityID' => 'EntityIdTest',
            'forceSSL' => '1',
            'mappingConfigPath' => '/typo3conf/ext/shibboleth/Resources/Private/config.txt',
            'pageUidForTSFE' => '1',
            'sessionInitiator_Location' => '/Login',
            'sessions_handlerURL' => 'Shibboleth.sso',
        ];
        $this->loginUrlService = new LoginUrlService();
        $parameters = $this->getParameterArrayFromUrl($this->loginUrlService->createUrl());
        $this->assertArrayHasKey('entityID',$parameters,'We expect here the additional parameter "entityID".');
        $this->assertSame('EntityIdTest',$parameters['entityID']);
    }
}
