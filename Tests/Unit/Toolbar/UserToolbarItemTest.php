<?php
/**
 * Created by PhpStorm.
 * User: tschikarski
 * Date: 17.07.17
 * Time: 11:59
 */

namespace TrustCnct\Shibboleth\Toolbar;

use Nimut\TestingFramework\MockObject\AccessibleMockObjectInterface;
use Nimut\TestingFramework\TestCase\UnitTestCase;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class UserToolbarItemTest extends \Nimut\TestingFramework\TestCase\UnitTestCase
{
    /**
     * @test
     */
    public function getSecureLogoutRedirectUrlContainsLoginReturnStringOnce() {
        /** @var $backendUser \TYPO3\CMS\Core\Authentication\BackendUserAuthentication */
        $backendUser = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Authentication\BackendUserAuthentication::class);
        $GLOBALS['BE_USER'] = $backendUser;
        $GLOBALS['BE_USER']->user['tx_shibboleth_shibbolethsessionid'] = 'something';
        $userToolbarItem = $this->getAccessibleMock(\TrustCnct\Shibboleth\Toolbar\UserToolbarItem::class,
            null);
        $returnValue = $userToolbarItem->_call('getSecureLogoutRedirectUrl');
        $this->assertSame(1, preg_match_all('/Logout\?return\=/',
            $returnValue),'Expected "Logout?return=" exactly once');
    }

    /**
     * @test
     */
    public function getSecureLogoutRedirectUrlContainsLoginUrlLoginReturnStringOnceIfInConfig()
    {
        /** @var $backendUser \TYPO3\CMS\Core\Authentication\BackendUserAuthentication */
        $backendUser = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Authentication\BackendUserAuthentication::class);
        $GLOBALS['BE_USER'] = $backendUser;
        $GLOBALS['BE_USER']->user['tx_shibboleth_shibbolethsessionid'] = 'something';
        $userToolbarItem = $this->getAccessibleMock(\TrustCnct\Shibboleth\Toolbar\UserToolbarItem::class,
            null);
        $GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['shibboleth'] = 'a:20:{s:17:"mappingConfigPath";s:40:"/typo3conf/ext/shibboleth/Resources/Private/config.txt";s:19:"sessions_handlerURL";s:14:"Shibboleth.sso";s:25:"sessionInitiator_Location";s:6:"/Login";s:9:"FE_enable";s:1:"1";s:13:"FE_autoImport";s:1:"1";s:17:"FE_autoImport_pid";s:1:"2";s:9:"BE_enable";s:1:"1";s:13:"BE_autoImport";s:1:"1";s:24:"BE_autoImportDisableUser";s:1:"0";s:20:"BE_loginTemplatePath";s:48:"typo3conf/ext/shibboleth/res/be_form/login7.html";s:20:"BE_logoutRedirectUrl";s:63:"Logout?return=/typo3conf/ext/shibboleth/res/be_form/logout.html";s:26:"BE_disabledUserRedirectUrl";s:53:"/typo3conf/ext/shibboleth/res/be_form/nologinyet.html";s:21:"enableAlwaysFetchUser";s:1:"1";s:8:"entityID";s:0:"";s:8:"forceSSL";s:1:"1";s:16:"FE_applicationID";s:0:"";s:16:"BE_applicationID";s:0:"";s:9:"FE_devLog";s:1:"1";s:9:"BE_devLog";s:1:"0";s:15:"database_devLog";s:1:"1";}';
        $returnValue = $userToolbarItem->_call('getSecureLogoutRedirectUrl');
        $this->assertSame(1, preg_match_all('/Logout\?return\=/',
            $returnValue),'Expected "Logout?return=" exactly once');
    }


    /**
     * @test
     */
    public function getSecureLogoutRedirectUrlDoesNotContainLoginUrlIfNoShibbolethUser() {
        $userToolbarItem = $this->getAccessibleMock(\TrustCnct\Shibboleth\Toolbar\UserToolbarItem::class,
            null);
        $returnValue = $userToolbarItem->_call('getSecureLogoutRedirectUrl');
        $this->assertGreaterThanOrEqual(0, preg_match('/Logout\?return\=/',
            $returnValue),'Expected "Logout?return="');
    }
}
