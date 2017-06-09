<?php
namespace TrustCnct\Shibboleth\Tests\Unit\Domain\Model;

/**
 * Test case.
 */
class LoginLinkTest extends \TYPO3\CMS\Core\Tests\UnitTestCase
{
    /**
     * @var \TrustCnct\Shibboleth\Domain\Model\LoginLink
     */
    protected $subject = null;

    protected function setUp()
    {
        parent::setUp();
        $this->subject = new \TrustCnct\Shibboleth\Domain\Model\LoginLink();
    }

    protected function tearDown()
    {
        parent::tearDown();
    }

    /**
     * @test
     */
    public function dummyTestToNotLeaveThisFileEmpty()
    {
        self::markTestIncomplete();
    }
}
