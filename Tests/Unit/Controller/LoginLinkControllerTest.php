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
    protected $loginUrlService;

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
    public function showActionAssignsTheGivenLoginUrlToView()
    {
        $loginUrl = 'https://localhost/Shibboleth.sso/LoginTest';
        $view = $this->getMockBuilder(\TYPO3\CMS\Extbase\Mvc\View\ViewInterface::class)->getMock();
        $this->inject($this->subject, 'view', $view);
        $mockUrlService = $this->getMock(\TrustCnct\Shibboleth\Service\LoginUrlService::class,['createUrl']);
        $mockUrlService->expects($this->once())->method('createUrl')->willReturn($loginUrl);
        $this->inject($this->subject, 'loginUrlService', $mockUrlService);
        $view->expects(self::once())->method('assign')->with('loginLinkUrl', $loginUrl);

        $this->subject->showAction($loginUrl);
    }
}
