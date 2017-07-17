<?php

namespace TrustCnct\Shibboleth\Tests\Unit\Service;

use Nimut\TestingFramework\MockObject\AccessibleMockObjectInterface;
use Nimut\TestingFramework\TestCase\UnitTestCase;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TrustCnct\Shibboleth\Service\LoginUrlService;

class LoginUrlServiceTest extends \Nimut\TestingFramework\TestCase\UnitTestCase
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
        $mockedUrlService = $this->getMock('TrustCnct\Shibboleth\Service\LoginUrlService',['dummy']);
        preg_match('|^(.+)\:.*|',$mockedUrlService->createUrl(),$matches);
        $this->assertRegExp('|http|', $matches[1]);
    }

    /**
     * @test
     */
    public function loginLinkProtocolIsForcedHttps() {
        $mockedUrlService = $this->getAccessibleMock('TrustCnct\Shibboleth\Service\LoginUrlService',['dummy']);
        $specialExtConf = $mockedUrlService->_get('extConf');
        $specialExtConf['forceSSL'] = true;
        $mockedUrlService->_set('extConf',$specialExtConf);
        preg_match('|^(.+)\:.*|',$mockedUrlService->createUrl(),$matches);
        $this->assertRegExp('|https|', $matches[1]);
    }

    /**
     * @test
     */
    public function loginLinkContainsShibbolethHandlerUrl() {
        $mockedUrlService = $this->getMock('TrustCnct\Shibboleth\Service\LoginUrlService',['dummy']);
        $this->assertRegExp('|/'.$this->extConf['sessions_handlerURL'].'/|', $mockedUrlService->createUrl());
    }

    /**
     * @test
     */
    public function loginLinkContainsSessionInitiatorLocation() {
        $mockedUrlService = $this->getMock('TrustCnct\Shibboleth\Service\LoginUrlService',['dummy']);
        $link = $mockedUrlService->createUrl();
        $this->assertNotEmpty($link);
        $this->assertRegExp('|/'.$this->extConf['sessionsInitiator_location'].'|', $link);
    }

    /**
     * @test
     */
    public function loginLinkContainsTargetParameter() {
        $mockedUrlService = $this->getMock('TrustCnct\Shibboleth\Service\LoginUrlService',['dummy']);
        $parameters = $this->getParameterArrayFromUrl($mockedUrlService->createUrl());
        $this->assertArrayHasKey('target',$parameters);
    }

    /**
     * @test
     */
    public function loginLinkTargetParameterIsUrl() {
        $mockedUrlService = $this->getMock('TrustCnct\Shibboleth\Service\LoginUrlService',['dummy']);
        $parameters = $this->getParameterArrayFromUrl($mockedUrlService->createUrl());
        $targetUrl = urldecode($parameters['target']);
        $this->assertRegExp('|^https?://.*$|', $targetUrl,'Link target must have URL format.');
    }

    /**
     * @test
     */
    public function loginLinkContainsNoEntityIdParameter() {
        $mockedUrlService = $this->getMock('TrustCnct\Shibboleth\Service\LoginUrlService',['dummy']);
        $parameters = $this->getParameterArrayFromUrl($mockedUrlService->createUrl());
        $this->assertArrayNotHasKey('entityID',$parameters);
    }

    /**
     * @test
     */
    public function loginLinkContainsOptionalEntityIdParameter() {
        $mockedUrlService = $this->getAccessibleMock('TrustCnct\Shibboleth\Service\LoginUrlService',['dummy']);
        $specialExtConf = $mockedUrlService->_get('extConf');
        $specialExtConf['entityID'] = 'EntityIdTest';
        $mockedUrlService->_set('extConf',$specialExtConf);
        $parameters = $this->getParameterArrayFromUrl($mockedUrlService->createUrl());
        $this->assertArrayHasKey('entityID',$parameters,'We expect here the additional parameter "entityID".');
        $this->assertSame('EntityIdTest',$parameters['entityID']);
    }
}
