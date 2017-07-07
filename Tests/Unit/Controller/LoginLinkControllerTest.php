<?php
namespace TrustCnct\Shibboleth\Tests\Unit\Controller;

/**
 * Test case.
 */
class LoginLinkControllerTest extends \Nimut\TestingFramework\TestCase\UnitTestCase
{
    /**
     * @var \TrustCnct\Shibboleth\Controller\LoginLinkController
     */
    protected $subject = null;

    /**
     * @var \TrustCnct\Shibboleth\Service\LoginUrlService
     * @inject
     */
    protected $loginLinkService;

    protected function setUp()
    {
        parent::setUp();
        $this->subject = $this->getMock('TrustCnct\Shibboleth\Controller\LoginLinkController',['dummy']);

    }

    protected function tearDown()
    {
        parent::tearDown();
    }

    /**
     * @test
     */
    public function showActionAssignsTheGivenLoginLinkToView()
    {
        $loginLink = 'https://localhost/Shibboleth.sso/LoginTest';
        $view = $this->getMockBuilder(\TYPO3\CMS\Extbase\Mvc\View\ViewInterface::class)->getMock();
        $this->inject($this->subject, 'view', $view);
        $mockLinkService = $this->getMock(\TrustCnct\Shibboleth\Service\LoginUrlService::class,['createLink']);
        $mockLinkService->expects($this->once())->method('createLink')->willReturn($loginLink);
        $this->inject($this->subject, 'loginLinkService', $mockLinkService);
        $view->expects(self::once())->method('assign')->with('loginLinkUrl', $loginLink);

        $this->subject->showAction($loginLink);
    }
}
