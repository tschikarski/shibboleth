<?php
/**
 * Created by PhpStorm.
 * User: tschikarski
 * Date: 10.07.17
 * Time: 16:50
 */

namespace TrustCnct\Shibboleth\User;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use Nimut\TestingFramework\TestCase;

class UserHandlerFunctionalTest extends \Nimut\TestingFramework\TestCase\FunctionalTestCase
{
    protected $userHandler;
    protected $db_user;
    protected $db_group;

    protected function setUp() {
        parent::setUp();
        global $TYPO3_CONF_VARS;
        $TYPO3_CONF_VARS['EXT']['extConf']['shibboleth'] = 'a:20:{s:17:"mappingConfigPath";s:62:"/typo3conf/ext/shibboleth/Tests/Functional/Fixtures/config.txt";s:19:"sessions_handlerURL";s:14:"Shibboleth.sso";s:25:"sessionInitiator_Location";s:6:"/Login";s:9:"FE_enable";s:1:"1";s:13:"FE_autoImport";s:1:"1";s:17:"FE_autoImport_pid";s:1:"2";s:9:"BE_enable";s:1:"0";s:13:"BE_autoImport";s:1:"1";s:24:"BE_autoImportDisableUser";s:1:"0";s:20:"BE_loginTemplatePath";s:48:"typo3conf/ext/shibboleth/res/be_form/login7.html";s:20:"BE_logoutRedirectUrl";s:49:"/typo3conf/ext/shibboleth/res/be_form/logout.html";s:26:"BE_disabledUserRedirectUrl";s:53:"/typo3conf/ext/shibboleth/res/be_form/nologinyet.html";s:21:"enableAlwaysFetchUser";s:1:"1";s:8:"entityID";s:0:"";s:8:"forceSSL";s:1:"1";s:16:"FE_applicationID";s:0:"";s:16:"BE_applicationID";s:0:"";s:9:"FE_devLog";s:1:"1";s:9:"BE_devLog";s:1:"0";s:15:"database_devLog";s:1:"0";}';
        $enable_clause = '';
        $this->db_user = array(
            'table' => 'fe_users'.$enable_clause,
            'userid_column' => 'uid',
            'username_column' => 'username',
            'userident_column' => 'password',
            'usergroup_column' => 'usergroup',
            'enable_clause' => $enable_clause,
            'checkPidList' => 0,
            'check_pid_clause' => '`pid` IN (2)'
        );
        $this->db_group = array(
            'table' => 'fe_groups'
        );
        $this->importDataSet('web/typo3conf/ext/shibboleth/Tests/Functional/Fixtures/fe_users.xml');
    }

    /**
     * @test
     */
    public function constructorForFrontendCaseTest()
    {
        /** @var UserHandler $userHandler */
        $userHandler = GeneralUtility::makeInstance(UserHandler::class,'FE','fe_users','fe_groups','Shib_Session_ID',false,'');
        $this->assertFalse($userHandler->tsfeDetected);
    }

    /**
     * @test
     */
    public function getMappingConfigPathTest() {
        /** @var UserHandler $userHandler */
        $userHandler = $this->getAccessibleMock('TrustCnct\Shibboleth\User\UserHandler',['getEnvironmentVariable'], ['FE','fe_users','fe_groups','Shib_Session_ID'],'',false);
        $userHandler->expects($this->once())->method('getEnvironmentVariable')->will($this->returnValue($_SERVER['TYPO3_PATH_ROOT']));
        $loginType = 'FE';
        $db_user = 'fe_users';
        $db_group = 'fe_groups';
        $shibbSessionIdKey = 'Shib_Session_ID';
        $userHandler->_callRef('__construct', $loginType, $db_user, $db_group, $shibbSessionIdKey);
        $expectedPath = $_SERVER['TYPO3_PATH_ROOT'].'/typo3conf/ext/shibboleth/Tests/Functional/Fixtures/config.txt';
        $this->assertSame($expectedPath,$userHandler->mappingConfigAbsolutePath);
    }

    /**
     * @test
     */
    public function mockGetTyposcriptConfigurationTest() {
        /** @var UserHandler $userHandler */
        $userHandler = $this->getAccessibleMock(\TrustCnct\Shibboleth\User\UserHandler::class,['getEnvironmentVariable'],array(
            // $loginType, $db_user, $db_group, $shibSessionIdKey, $writeDevLog = FALSE, $envShibPrefix = ''
                'FE',
                'fe_users',
                'fe_groups',
                'Shib_Session_ID',
                false,
                ''),
        '',false);
        $userHandler->expects($this->once())->method('getEnvironmentVariable')->will($this->returnValue($_SERVER['TYPO3_PATH_ROOT']));
        $loginType = 'FE';
        $db_user = 'fe_users';
        $db_group = 'fe_groups';
        $shibbSessionIdKey = 'Shib_Session_ID';
        $userHandler->_callRef('__construct', $loginType, $db_user, $db_group, $shibbSessionIdKey);
        $this->assertSame('TEXT',$userHandler->config['IDMapping.']['shibID']);
    }

    /**
     * @test
     */
    public function typo3IdFieldFromConfigFileTest() {
        /** @var UserHandler $userHandler */
        $userHandler = $this->getAccessibleMock(\TrustCnct\Shibboleth\User\UserHandler::class,['getEnvironmentVariable'],array(
            // $loginType, $db_user, $db_group, $shibSessionIdKey, $writeDevLog = FALSE, $envShibPrefix = ''
            'FE',
            'fe_users',
            'fe_groups',
            'Shib_Session_ID',
            false,
            ''),
            '',false);
        $userHandler->expects($this->once())->method('getEnvironmentVariable')->will($this->returnValue($_SERVER['TYPO3_PATH_ROOT']));
        $loginType = 'FE';
        $db_user = 'fe_users';
        $db_group = 'fe_groups';
        $shibbSessionIdKey = 'Shib_Session_ID';
        $userHandler->_callRef('__construct', $loginType, $db_user, $db_group, $shibbSessionIdKey);
        $idField = $userHandler->config['IDMapping.']['typo3Field'];
        $this->assertSame('username',$idField);
    }

    /**
     * @test
     */
    public function lookUpShibbolethUserInDatabaseReportsErrorOnEmptyIdValue() {
        /** @var UserHandler $userHandler */
        $userHandler = $this->getAccessibleMock(\TrustCnct\Shibboleth\User\UserHandler::class,['getEnvironmentVariable'],array(
            // $loginType, $db_user, $db_group, $shibSessionIdKey, $writeDevLog = FALSE, $envShibPrefix = ''
            'FE',
            'fe_users',
            'fe_groups',
            'Shib_Session_ID',
            false,
            ''),
            '',false);
        $userHandler->expects($this->once())->method('getEnvironmentVariable')->will($this->returnValue($_SERVER['TYPO3_PATH_ROOT']));
        $_SERVER['eppn'] = '';
        $loginType = 'FE';
        $db_user = 'fe_users';
        $db_group = 'fe_groups';
        $shibbSessionIdKey = 'Shib_Session_ID';
        $userHandler->_callRef('__construct', $loginType, $db_user, $db_group, $shibbSessionIdKey);
        $this->assertSame('Shibboleth data evaluates username to empty string!', $userHandler->lookUpShibbolethUserInDatabase());

    }

    /**
     * @test
     */
    public function lookUpShibbolethUserInDatabaseReturnsExistingFeUser() {
        /** @var UserHandler $userHandler */
        $userHandler = $this->getAccessibleMock(\TrustCnct\Shibboleth\User\UserHandler::class,['getEnvironmentVariable'],array(
            // $loginType, $db_user, $db_group, $shibSessionIdKey, $writeDevLog = FALSE, $envShibPrefix = ''
            'FE',
            'fe_users',
            'fe_groups',
            'Shib_Session_ID',
            false,
            ''),
            '',false);
        $userHandler->expects($this->once())->method('getEnvironmentVariable')->will($this->returnValue($_SERVER['TYPO3_PATH_ROOT']));
        $_SERVER['eppn'] = 'myself@testshib.org';
        $loginType = 'FE';
        $db_user = 'fe_users';
        $db_group = 'fe_groups';
        $shibbSessionIdKey = 'Shib_Session_ID';
        $userHandler->_callRef('__construct', $loginType, $this->db_user, $this->db_group, $shibbSessionIdKey);
        $userFromDB = $userHandler->lookUpShibbolethUserInDatabase();
        $this->assertTrue(is_array($userFromDB),'Expected array');
        $this->assertArrayHasKey('uid', $userFromDB);
        $this->assertSame(2, (int) $userFromDB['uid']);
        $this->assertStringStartsWith('myself', $userFromDB['username']);

    }

    /**
     * @test
     */
    public function lookUpShibbolethUserInDatabaseReturnsDisabledFeUser() {
        /** @var UserHandler $userHandler */
        $userHandler = $this->getAccessibleMock(\TrustCnct\Shibboleth\User\UserHandler::class,['getEnvironmentVariable'],array(
            // $loginType, $db_user, $db_group, $shibSessionIdKey, $writeDevLog = FALSE, $envShibPrefix = ''
            'FE',
            'fe_users',
            'fe_groups',
            'Shib_Session_ID',
            false,
            ''),
            '',false);
        $userHandler->expects($this->once())->method('getEnvironmentVariable')->will($this->returnValue($_SERVER['TYPO3_PATH_ROOT']));
        $_SERVER['eppn'] = 'disabled@testshib.org';
        $loginType = 'FE';
        $db_user = 'fe_users';
        $db_group = 'fe_groups';
        $shibbSessionIdKey = 'Shib_Session_ID';
        $userHandler->_callRef('__construct', $loginType, $this->db_user, $this->db_group, $shibbSessionIdKey);
        $userFromDB = $userHandler->lookUpShibbolethUserInDatabase();
        $this->assertTrue(is_array($userFromDB),'Expected array');
        $this->assertArrayHasKey('uid', $userFromDB);
        $this->assertSame(4, (int) $userFromDB['uid']);
        $this->assertStringStartsWith('disabled', $userFromDB['username']);

    }

    /**
     * @test
     */
    public function lookUpShibbolethUserInDatabaseReturnsNullIfFeUserDoesNotExist() {
        /** @var UserHandler $userHandler */
        $userHandler = $this->getAccessibleMock(\TrustCnct\Shibboleth\User\UserHandler::class,['getEnvironmentVariable'],array(
            // $loginType, $db_user, $db_group, $shibSessionIdKey, $writeDevLog = FALSE, $envShibPrefix = ''
            'FE',
            'fe_users',
            'fe_groups',
            'Shib_Session_ID',
            false,
            ''),
            '',false);
        $userHandler->expects($this->once())->method('getEnvironmentVariable')->will($this->returnValue($_SERVER['TYPO3_PATH_ROOT']));
        $_SERVER['eppn'] = 'false@testshib.org';
        $loginType = 'FE';
        $db_user = 'fe_users';
        $db_group = 'fe_groups';
        $shibbSessionIdKey = 'Shib_Session_ID';
        /** @var UserHandler $userHandler */
        $userHandler->_callRef('__construct', $loginType, $this->db_user, $this->db_group, $shibbSessionIdKey);
        $userFromDB = $userHandler->lookUpShibbolethUserInDatabase();
        $this->assertFalse(is_array($userFromDB),'Did not expect array');
        $this->assertEmpty($userFromDB);

    }

    /**
     * @test
     */
    public function lookUpShibbolethUserInDatabaseReturnsNullIfFeUserIsDeleted() {
        /** @var UserHandler $userHandler */
        $userHandler = $this->getAccessibleMock(\TrustCnct\Shibboleth\User\UserHandler::class,['getEnvironmentVariable'],array(
            // $loginType, $db_user, $db_group, $shibSessionIdKey, $writeDevLog = FALSE, $envShibPrefix = ''
            'FE',
            'fe_users',
            'fe_groups',
            'Shib_Session_ID',
            false,
            ''),
            '',false);
        $userHandler->expects($this->once())->method('getEnvironmentVariable')->will($this->returnValue($_SERVER['TYPO3_PATH_ROOT']));
        $_SERVER['eppn'] = 'deleted@testshib.org';
        $loginType = 'FE';
        $db_user = 'fe_users';
        $db_group = 'fe_groups';
        $shibbSessionIdKey = 'Shib_Session_ID';
        /** @var UserHandler $userHandler */
        $userHandler->_callRef('__construct', $loginType, $this->db_user, $this->db_group, $shibbSessionIdKey);
        $userFromDB = $userHandler->lookUpShibbolethUserInDatabase();
        $this->assertFalse(is_array($userFromDB),'Did not expect array');
        $this->assertEmpty($userFromDB);

    }

    /**
     * @test
     */
    public function existingUserIsUpdatedCorrectly() {
        /** @var UserHandler $userHandler */
        $userHandler = $this->getAccessibleMock(\TrustCnct\Shibboleth\User\UserHandler::class,['getEnvironmentVariable'],array(
            // $loginType, $db_user, $db_group, $shibSessionIdKey, $writeDevLog = FALSE, $envShibPrefix = ''
            'FE',
            'fe_users',
            'fe_groups',
            'Shib_Session_ID',
            false,
            ''),
            '',false);
        $userHandler->expects($this->once())->method('getEnvironmentVariable')->will($this->returnValue($_SERVER['TYPO3_PATH_ROOT']));
        $_SERVER['eppn'] = 'myself@testshib.org';
        $_SERVER['affiliation'] = 'goes to company';
        $loginType = 'FE';
        $db_user = 'fe_users';
        $db_group = 'fe_groups';
        $shibbSessionIdKey = 'Shib_Session_ID';
        /** @var UserHandler $userHandler */
        $userHandler->_callRef('__construct', $loginType, $this->db_user, $this->db_group, $shibbSessionIdKey);
        $userBefore = $userHandler->lookUpShibbolethUserInDatabase();
        $userBefore = $userHandler->transferShibbolethAttributesToUserArray($userBefore);
        unset($userBefore['_allowUser']);
        unset($userBefore['tx_shibboleth_shibbolethsessionid']);
        $uidBefore = $userBefore['uid'];
        $this->assertSame(2, (int) $uidBefore);
        $uidReported = $userHandler->synchronizeUserData($userBefore);
        $this->assertSame(2, (int) $uidReported);
        $userAfter = $userHandler->lookUpShibbolethUserInDatabase();
        $this->assertSame('goes to company', $userAfter['company']);
        $this->assertSame('first time set', $userAfter['fax']);

    }
    /**
     * @test
     */
    public function nonExistingUserIsInsertedCorrectly() {
        /** @var UserHandler $userHandler */
        $userHandler = $this->getAccessibleMock(\TrustCnct\Shibboleth\User\UserHandler::class,['getEnvironmentVariable'],array(
            // $loginType, $db_user, $db_group, $shibSessionIdKey, $writeDevLog = FALSE, $envShibPrefix = ''
            'FE',
            'fe_users',
            'fe_groups',
            'Shib_Session_ID',
            false,
            ''),
            '',false);
        $userHandler->expects($this->once())->method('getEnvironmentVariable')->will($this->returnValue($_SERVER['TYPO3_PATH_ROOT']));
        $_SERVER['eppn'] = 'new@testshib.org';
        $_SERVER['affiliation'] = 'goes to company';
        $_SERVER['entitlement'] = 'first time set';
        $loginType = 'FE';
        $db_user = 'fe_users';
        $db_group = 'fe_groups';
        $shibbSessionIdKey = 'Shib_Session_ID';
        /** @var UserHandler $userHandler */
        $userHandler->_callRef('__construct', $loginType, $this->db_user, $this->db_group, $shibbSessionIdKey);
        $userBefore = $userHandler->transferShibbolethAttributesToUserArray(NULL);
        unset($userBefore['_allowUser']);
        unset($userBefore['tx_shibboleth_shibbolethsessionid']);
        $uidReported = $userHandler->synchronizeUserData($userBefore);
        $this->assertSame(5, (int) $uidReported);
        $userAfter = $userHandler->lookUpShibbolethUserInDatabase();
        $this->assertSame('goes to company', $userAfter['company']);
        $this->assertSame('first time set', $userAfter['fax']);

    }

}