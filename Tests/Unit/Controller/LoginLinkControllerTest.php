<?php
namespace Trustcnct\Shibboleth\Tests\Unit\Controller;

/**
 * Test case.
 */
class LoginLinkControllerTest extends \TYPO3\CMS\Core\Tests\UnitTestCase
{
    /**
     * @var \Trustcnct\Shibboleth\Controller\LoginLinkController
     */
    protected $subject = null;

    protected function setUp()
    {
        parent::setUp();
        $this->subject = $this->getMockBuilder(\Trustcnct\Shibboleth\Controller\LoginLinkController::class)
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
        $loginLink = new \Trustcnct\Shibboleth\Domain\Model\LoginLink();

        $view = $this->getMockBuilder(\TYPO3\CMS\Extbase\Mvc\View\ViewInterface::class)->getMock();
        $this->inject($this->subject, 'view', $view);
        $view->expects(self::once())->method('assign')->with('loginLink', $loginLink);

        $this->subject->showAction($loginLink);
    }
}
