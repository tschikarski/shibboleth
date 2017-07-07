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
     * @var \TrustCnct\Shibboleth\Service\LoginLinkService
     * @inject
     */
    protected $loginLinkService;

    protected function setUp()
    {
        parent::setUp();
        $this->subject = $this->getMockBuilder(\TrustCnct\Shibboleth\Controller\LoginLinkController::class)
            ->setMethods(['redirect', 'forward', 'addFlashMessage'])
            ->disableOriginalConstructor()
            ->getMock();

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
        $loginLink = new \TrustCnct\Shibboleth\Service\LoginLinkService();

        $view = $this->getMockBuilder(\TYPO3\CMS\Extbase\Mvc\View\ViewInterface::class)->getMock();
        $this->inject($this->subject, 'view', $view);
        $view->expects(self::once())->method('assign')->with('loginLink', $loginLink);

        $this->subject->showAction($loginLink);
    }
}
