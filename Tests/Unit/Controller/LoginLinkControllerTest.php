<?php
namespace TrustCnct\Shibboleth\Tests\Unit\Controller;

use Prophecy\Prophecy\ObjectProphecy;
use TrustCnct\Shibboleth\Controller\LoginLinkController;
use TrustCnct\Shibboleth\Service\LoginUrlService;
use TYPO3\CMS\Fluid\View\TemplateView;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Test case.
 */
class LoginLinkControllerTest extends UnitTestCase
{

    use \Prophecy\PhpUnit\ProphecyTrait;

    /**
     * @var LoginLinkController
     */
    private $subject;

    /**
     * @var TemplateView|ObjectProphecy
     */
    private $viewProphecy;

    /**
     * @var LoginUrlService|ObjectProphecy
     */
    private $loginUrlServiceProphecy;

    protected function setUp(): void
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
            'forceSSL' => '1',
            'mappingConfigPath' => '/typo3conf/ext/shibboleth/Resources/Private/config.txt',
            'pageUidForTSFE' => '1',
            'sessionInitiator_Location' => '/Login',
            'sessions_handlerURL' => 'Shibboleth.sso',
        ];

        $this->subject = new LoginLinkController();

        $this->viewProphecy = $this->prophesize(TemplateView::class);
        $view = $this->viewProphecy->reveal();

        $reflectionClass = new \ReflectionClass(LoginLinkController::class);
        $prop = $reflectionClass->getProperty('view');
        $prop->setAccessible(true);
        $prop->setValue($this->subject, $view);

        $this->loginUrlServiceProphecy = $this->prophesize(LoginUrlService::class);
        $this->subject->injectLoginUrlService($this->loginUrlServiceProphecy->reveal());
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        unset($GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['shibboleth']);
    }

    /**
     * @test
     */
    public function showActionAssignsTheGivenLoginUrlToView()
    {
        $loginUrl = 'https://localhost/Shibboleth.sso/Login';

        $this->viewProphecy->assign('loginLinkUrl', $loginUrl)->shouldBeCalled();
        $this->loginUrlServiceProphecy->createUrl()->willReturn($loginUrl)->shouldBeCalled();

        $this->subject->showAction();
    }
}
