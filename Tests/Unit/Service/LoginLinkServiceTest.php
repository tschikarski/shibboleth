<?php

namespace TrustCnct\Shibboleth\Tests\Unit\Service;

use Nimut\TestingFramework\MockObject\AccessibleMockObjectInterface;
use Nimut\TestingFramework\TestCase\UnitTestCase;
use TrustCnct\Shibboleth\Service\LoginLinkService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class LoginLinkServiceTest extends \Nimut\TestingFramework\TestCase\UnitTestCase
{
    /**
     * @var \TrustCnct\Shibboleth\Controller\LoginLinkService
     */
    protected $subject = null;

    protected $extConf;

    protected $testExtConf = array(
        'sessions_handlerURL' => 'ShibbolethTest.sso',
        'sessionInitiator_Location' => 'LoginTest',
        'forceSSL' => false,
        'entityID' => ''
    );

    protected function setUp()
    {
        parent::setUp();
        $GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['shibboleth'] = serialize($this->testExtConf);
        $this->subject = $this->getMockBuilder(\TrustCnct\Shibboleth\Service\LoginLinkService::class)
            ->setMethods(['dummy'])
            ->enableOriginalConstructor()
            ->getMock();
//        $this->subject = new LoginLinkService();
    }

    protected function tearDown()
    {
        unset($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['shibboleth']);
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
    public function loginLinkProtocolIsHttpOrHttps() {
        preg_match('|^(.+)\:.*|',$this->subject->createLink(),$matches);
        $this->assertRegExp('|https?|', $matches[1]);
    }

    /**
     * @test
     */
    public function loginLinkContainsShibbolethHandlerUrl() {
        $this->assertRegExp('|/'.$this->extConf['sessions_handlerURL'].'/|', $this->subject->createLink());
    }

    /**
     * @test
     */
    public function loginLinkContainsSessionInitiatorLocation() {
        $link = $this->subject->createLink();
        $this->assertRegExp('|/'.$this->extConf['sessionsInitiator_location'].'|', $link);
    }

    /**
     * @test
     */
    public function loginLinkContainsTargetParameter() {
        $parameters = $this->getParameterArrayFromUrl($this->subject->createLink());
        $this->assertArrayHasKey('target',$parameters);
    }

    /**
     * @test
     */
    public function loginLinkTargetParameterIsUrl() {
        $parameters = $this->getParameterArrayFromUrl($this->subject->createLink());
        $targetUrl = urldecode($parameters['target']);
        $this->assertRegExp('|^https?://.*$|', $targetUrl,'Link target must have URL format.');
    }

    /**
     * @test
     */
    public function loginLinkContainsOptionalEntityIdParameter() {
        $specialExtConf = $this->testExtConf;
        $specialExtConf['entityID'] = 'EntityIdTest';
        $GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['shibboleth'] = serialize($specialExtConf);
        $parameters = $this->getParameterArrayFromUrl($this->subject->createLink());
        $this->assertArrayHasKey('entityID',$parameters,'We expect here the additional parameter "entityID".');
    }
}
