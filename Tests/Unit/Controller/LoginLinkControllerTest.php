<?php
namespace TrustCnct\Shibboleth\Tests\Unit\Controller;

use TrustCnct\Shibboleth\Controller\LoginLinkController;
use TrustCnct\Shibboleth\Service\LoginUrlService;

/**
 * Test case.
 */
class LoginLinkControllerTest extends \Nimut\TestingFramework\TestCase\UnitTestCase
{
    /**
     * @var \TrustCnct\Shibboleth\Controller\LoginLinkController
     */
    protected $loginLinkController = null;

    /**
     * @var \TrustCnct\Shibboleth\Service\LoginUrlService
     */
    protected $loginUrlService;

    public function injectLoginUrlService(LoginUrlService $loginUrlService)
    {
        $this->loginUrlService = $loginUrlService;
    }

    protected function setUp()
    {
        parent::setUp();
        $this->loginLinkController = $this->getMockBuilder(LoginLinkController::class)->getMock();
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
    }

    protected function tearDown()
    {
        parent::tearDown();
        unset($GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['shibboleth']);
    }

    /**
     * @test
     */
    public function showActionAssignsTheGivenLoginUrlToView()
    {
        $loginUrl = 'https://localhost/Shibboleth.sso/LoginTest';
        $view = $this->getMockBuilder(\TYPO3\CMS\Extbase\Mvc\View\ViewInterface::class)->getMock();
        $this->inject($this->loginLinkController, 'view', $view);
        $loginUrlService = $this->getMockBuilder(\TrustCnct\Shibboleth\Service\LoginUrlService::class)
            ->setMethods(['createUrl'])->getMock();
        $loginUrlService->expects($this->once())->method('createUrl')->willReturn($loginUrl);
        $this->inject($this->loginLinkController, 'loginUrlService', $loginUrlService);
        $view->expects($this->once())->method('assign')->with('loginLinkUrl', $loginUrl);
        $this->loginLinkController->showAction();
    }
}
