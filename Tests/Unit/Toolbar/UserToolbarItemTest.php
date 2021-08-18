<?php
/**
 * Created by PhpStorm.
 * User: tschikarski
 * Date: 17.07.17
 * Time: 11:59
 */

namespace TrustCnct\Shibboleth\Toolbar;

use TYPO3\CMS\Core\Utility\GeneralUtility;

class UserToolbarItemTest extends \Nimut\TestingFramework\TestCase\UnitTestCase
{

    protected function setUp()
    {
        parent::setUp();
        $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['shibboleth'] = [
            "mappingConfigPath" => "/typo3conf/ext/shibboleth/Resources/Private/config.txt",
            "sessions_handlerURL" => "Shibboleth.sso",
            "sessionInitiator_Location" => "/Login",
            "FE_enable" => "1",
            "FE_autoImport" => "1",
            "FE_autoImport_pid" => "2",
            "BE_enable" => "0",
            "BE_autoImport" => "1",
            "BE_autoImportDisableUser" => "0",
            "BE_loginTemplatePath" => "typo3conf/ext/shibboleth/res/be_form/login7.html",
            "BE_logoutRedirectUrl" => "Logout?return=/typo3conf/ext/shibboleth/res/be_form/logout.html",
            "BE_disabledUserRedirectUrl" => "/typo3conf/ext/shibboleth/res/be_form/nologinyet.html",
            "enableAlwaysFetchUser" => "1",
            "entityID" => "",
            "forceSSL" => "1",
            "FE_applicationID" => "",
            "BE_applicationID" => "",
            "FE_devLog" => "1",
            "BE_devLog" => "0",
            "database_devLog" => "1"
        ];
    }

    protected function tearDown()
    {
        parent::tearDown();
        unset($GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['shibboleth']);
    }

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
        $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['shibboleth'] = [
            "mappingConfigPath" => "/typo3conf/ext/shibboleth/Resources/Private/config.txt",
            "sessions_handlerURL" => "Shibboleth.sso",
            "sessionInitiator_Location" => "/Login",
            "FE_enable" => "1",
            "FE_autoImport" => "1",
            "FE_autoImport_pid" => "2",
            "BE_enable" => "0",
            "BE_autoImport" => "1",
            "BE_autoImportDisableUser" => "0",
            "BE_loginTemplatePath" => "typo3conf/ext/shibboleth/res/be_form/login7.html",
            "BE_logoutRedirectUrl" => "Logout?return=/typo3conf/ext/shibboleth/res/be_form/logout.html",
            "BE_disabledUserRedirectUrl" => "/typo3conf/ext/shibboleth/res/be_form/nologinyet.html",
            "enableAlwaysFetchUser" => "1",
            "entityID" => "",
            "forceSSL" => "1",
            "FE_applicationID" => "",
            "BE_applicationID" => "",
            "FE_devLog" => "1",
            "BE_devLog" => "0",
            "database_devLog" => "1"
        ];
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
