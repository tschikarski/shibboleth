<?php

namespace TrustCnct\Shibboleth\Tests\Unit\Service;

use Nimut\TestingFramework\MockObject\AccessibleMockObjectInterface;
use Nimut\TestingFramework\TestCase\UnitTestCase;
use TrustCnct\Shibboleth\Service\LoginLinkService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class LoginLinkServiceTest extends \Nimut\TestingFramework\TestCase\UnitTestCase
{
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
    public function loginLinkProtocolIsHttp() {
        $mockedLinkService = $this->getMock('TrustCnct\Shibboleth\Service\LoginLinkService',['dummy']);
        preg_match('|^(.+)\:.*|',$mockedLinkService->createLink(),$matches);
        $this->assertRegExp('|http|', $matches[1]);
    }

    /**
     * @test
     */
    public function loginLinkProtocolIsForcedHttps() {
        $mockedLinkService = $this->getAccessibleMock('TrustCnct\Shibboleth\Service\LoginLinkService',['dummy']);
        $specialExtConf = $mockedLinkService->_get('extConf');
        $specialExtConf['forceSSL'] = true;
        $mockedLinkService->_set('extConf',$specialExtConf);
        preg_match('|^(.+)\:.*|',$mockedLinkService->createLink(),$matches);
        $this->assertRegExp('|https|', $matches[1]);
    }

    /**
     * @test
     */
    public function loginLinkContainsShibbolethHandlerUrl() {
        $mockedLinkService = $this->getMock('TrustCnct\Shibboleth\Service\LoginLinkService',['dummy']);
        $this->assertRegExp('|/'.$this->extConf['sessions_handlerURL'].'/|', $mockedLinkService->createLink());
    }

    /**
     * @test
     */
    public function loginLinkContainsSessionInitiatorLocation() {
        $mockedLinkService = $this->getMock('TrustCnct\Shibboleth\Service\LoginLinkService',['dummy']);
        $link = $mockedLinkService->createLink();
        $this->assertRegExp('|/'.$this->extConf['sessionsInitiator_location'].'|', $link);
    }

    /**
     * @test
     */
    public function loginLinkContainsTargetParameter() {
        $mockedLinkService = $this->getMock('TrustCnct\Shibboleth\Service\LoginLinkService',['dummy']);
        $parameters = $this->getParameterArrayFromUrl($mockedLinkService->createLink());
        $this->assertArrayHasKey('target',$parameters);
    }

    /**
     * @test
     */
    public function loginLinkTargetParameterIsUrl() {
        $mockedLinkService = $this->getMock('TrustCnct\Shibboleth\Service\LoginLinkService',['dummy']);
        $parameters = $this->getParameterArrayFromUrl($mockedLinkService->createLink());
        $targetUrl = urldecode($parameters['target']);
        $this->assertRegExp('|^https?://.*$|', $targetUrl,'Link target must have URL format.');
    }

    /**
     * @test
     */
    public function loginLinkContainsNoEntityIdParameter() {
        $mockedLinkService = $this->getMock('TrustCnct\Shibboleth\Service\LoginLinkService',['dummy']);
        $parameters = $this->getParameterArrayFromUrl($mockedLinkService->createLink());
        $this->assertArrayNotHasKey('entityID',$parameters);
    }

    /**
     * @test
     */
    public function loginLinkContainsOptionalEntityIdParameter() {
        $mockedLinkService = $this->getAccessibleMock('TrustCnct\Shibboleth\Service\LoginLinkService',['dummy']);
        $specialExtConf = $mockedLinkService->_get('extConf');
        $specialExtConf['entityID'] = 'EntityIdTest';
        $mockedLinkService->_set('extConf',$specialExtConf);
        $parameters = $this->getParameterArrayFromUrl($mockedLinkService->createLink());
        $this->assertArrayHasKey('entityID',$parameters,'We expect here the additional parameter "entityID".');
        $this->assertSame('EntityIdTest',$parameters['entityID']);
    }
}
