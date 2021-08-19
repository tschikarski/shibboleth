<?php
/**
 * Created by PhpStorm.
 * User: tschikarski
 * Date: 10.07.17
 * Time: 16:50
 */

namespace TrustCnct\Shibboleth\User;

use TYPO3\CMS\Core\Core\Environment;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

class UserHandlerFunctionalTest extends FunctionalTestCase
{
    use \Prophecy\PhpUnit\ProphecyTrait;

    protected $testExtensionsToLoad = [
        'typo3conf/ext/shibboleth',
    ];

    /**
     * @var UserHandler
     */
    protected $userHandler;

    /**
     * @var array
     */
    protected $db_user = [
        'table' => 'fe_users',
        'userid_column' => 'uid',
        'username_column' => 'username',
        'userident_column' => 'password',
        'usergroup_column' => 'usergroup',
        'enable_clause' => '',
        'checkPidList' => 0,
        'check_pid_clause' => '`pid` IN (2)'
    ];

    /**
     * @var string[]
     */
    protected $db_group = [
        'table' => 'fe_groups'
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['shibboleth']  = [
            "mappingConfigPath" => "/typo3conf/ext/shibboleth/Tests/Functional/Fixtures/config.txt",
            "sessions_handlerURL" => "Shibboleth.sso",
            "sessionInitiator_Location" => "/Login",
            "FE_enable" => "1",
            "FE_autoImport" => "1",
            "FE_autoImport_pid" => "2",
            "BE_enable" => "0",
            "BE_autoImport" => "1",
            "BE_autoImportDisableUser" => "0",
            "BE_loginTemplatePath" => "typo3conf/ext/shibboleth/res/be_form/login7.html",
            "BE_logoutRedirectUrl" => "/typo3conf/ext/shibboleth/res/be_form/logout.html",
            "BE_disabledUserRedirectUrl" => "/typo3conf/ext/shibboleth/res/be_form/nologinyet.html",
            "enableAlwaysFetchUser" => "1",
            "entityID" => "",
            "forceSSL" => "1",
            "FE_applicationID" => "",
            "BE_applicationID" => "",
            "FE_devLog" => "1",
            "BE_devLog" => "0",
            "database_devLog" => "0"
        ];
        $this->importDataSet(ORIGINAL_ROOT . 'typo3conf/ext/shibboleth/Tests/Functional/Fixtures/fe_users.xml');
        $this->importDataSet(ORIGINAL_ROOT . 'typo3conf/ext/shibboleth/Tests/Functional/Fixtures/be_users.xml');
    }

    /**
     * @test
     */
    public function getMappingConfigPathTest() {
        $userHandler = $this->getAccessibleMock(
            UserHandler::class,
            ['getEnvironmentVariable'],
            ['FE','fe_users','fe_groups','Shib_Session_ID'],
            '',
            false
        );
        $loginType = 'FE';
        $db_user = 'fe_users';
        $db_group = 'fe_groups';
        $shibbSessionIdKey = 'Shib_Session_ID';
        $userHandler->_call('__construct', $loginType, $db_user, $db_group, $shibbSessionIdKey);
        $expectedPath = Environment::getPublicPath() . '/typo3conf/ext/shibboleth/Tests/Functional/Fixtures/config.txt';
        $this->assertSame($expectedPath,$userHandler->_get('mappingConfigAbsolutePath'));
    }

    /**
     * @test
     */
    public function mockGetTyposcriptConfigurationTest() {
        $userHandler = $this->getAccessibleMock(\TrustCnct\Shibboleth\User\UserHandler::class,['getEnvironmentVariable'],array(
            // $loginType, $db_user, $db_group, $shibSessionIdKey, $writeDevLog = FALSE, $envShibPrefix = ''
                'FE',
                'fe_users',
                'fe_groups',
                'Shib_Session_ID',
                false,
                ''),
        '',false);
        $loginType = 'FE';
        $db_user = 'fe_users';
        $db_group = 'fe_groups';
        $shibbSessionIdKey = 'Shib_Session_ID';
        $userHandler->_call('__construct', $loginType, $db_user, $db_group, $shibbSessionIdKey);
        $this->assertSame('TEXT',$userHandler->_get('config')['IDMapping.']['shibID']);
    }

    /**
     * @test
     */
    public function typo3IdFieldFromConfigFileTest() {
        $userHandler = $this->getAccessibleMock(\TrustCnct\Shibboleth\User\UserHandler::class,['getEnvironmentVariable'],array(
            'FE',
            'fe_users',
            'fe_groups',
            'Shib_Session_ID',
            false,
            ''),
            '',false);
        $loginType = 'FE';
        $db_user = 'fe_users';
        $db_group = 'fe_groups';
        $shibbSessionIdKey = 'Shib_Session_ID';
        $userHandler->_call('__construct', $loginType, $db_user, $db_group, $shibbSessionIdKey);
        $idField = $userHandler->_get('config')['IDMapping.']['typo3Field'];
        $this->assertSame('username',$idField);
    }

    /**
     * @test
     */
    public function lookUpShibbolethUserInDatabaseReportsErrorOnEmptyIdValue() {
        $userHandler = $this->getAccessibleMock(\TrustCnct\Shibboleth\User\UserHandler::class,['getEnvironmentVariable'],array(
            'FE',
            'fe_users',
            'fe_groups',
            'Shib_Session_ID',
            false,
            ''),
            '',false);
        $_SERVER['eppn'] = '';
        $loginType = 'FE';
        $db_user = 'fe_users';
        $db_group = 'fe_groups';
        $shibbSessionIdKey = 'Shib_Session_ID';
        $userHandler->_call('__construct', $loginType, $db_user, $db_group, $shibbSessionIdKey);
        $this->assertSame('Shibboleth data evaluates username to empty string!', $userHandler->lookUpShibbolethUserInDatabase());
    }

    /**
     * @test
     */
    public function lookUpShibbolethUserInDatabaseReturnsExistingFeUser() {
        $userHandler = $this->getAccessibleMock(\TrustCnct\Shibboleth\User\UserHandler::class,['getEnvironmentVariable'],array(
            'FE',
            'fe_users',
            'fe_groups',
            'Shib_Session_ID',
            false,
            ''),
            '',false);
        $_SERVER['eppn'] = 'myself@testshib.org';
        $loginType = 'FE';
        $db_user = 'fe_users';
        $db_group = 'fe_groups';
        $shibbSessionIdKey = 'Shib_Session_ID';
        $userHandler->_call('__construct', $loginType, $this->db_user, $this->db_group, $shibbSessionIdKey);
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
        $userHandler = $this->getAccessibleMock(\TrustCnct\Shibboleth\User\UserHandler::class,['getEnvironmentVariable'],array(
            'FE',
            'fe_users',
            'fe_groups',
            'Shib_Session_ID',
            false,
            ''),
            '',false);
        $_SERVER['eppn'] = 'disabled@testshib.org';
        $loginType = 'FE';
        $db_user = 'fe_users';
        $db_group = 'fe_groups';
        $shibbSessionIdKey = 'Shib_Session_ID';
        $userHandler->_call('__construct', $loginType, $this->db_user, $this->db_group, $shibbSessionIdKey);
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
        $userHandler = $this->getAccessibleMock(\TrustCnct\Shibboleth\User\UserHandler::class,['getEnvironmentVariable'],array(
            'FE',
            'fe_users',
            'fe_groups',
            'Shib_Session_ID',
            false,
            ''),
            '',false);
        $_SERVER['eppn'] = 'false@testshib.org';
        $loginType = 'FE';
        $db_user = 'fe_users';
        $db_group = 'fe_groups';
        $shibbSessionIdKey = 'Shib_Session_ID';
        /** @var UserHandler $userHandler */
        $userHandler->_call('__construct', $loginType, $this->db_user, $this->db_group, $shibbSessionIdKey);
        $userFromDB = $userHandler->lookUpShibbolethUserInDatabase();
        $this->assertFalse(is_array($userFromDB),'Did not expect array');
        $this->assertFalse($userFromDB);
    }

    /**
     * @test
     */
    public function lookUpShibbolethUserInDatabaseReturnsNullIfFeUserIsDeleted() {
        $userHandler = $this->getAccessibleMock(\TrustCnct\Shibboleth\User\UserHandler::class,['getEnvironmentVariable'],array(
            'FE',
            'fe_users',
            'fe_groups',
            'Shib_Session_ID',
            false,
            ''),
            '',false);
        $_SERVER['eppn'] = 'deleted@testshib.org';
        $loginType = 'FE';
        $db_user = 'fe_users';
        $db_group = 'fe_groups';
        $shibbSessionIdKey = 'Shib_Session_ID';
        /** @var UserHandler $userHandler */
        $userHandler->_call('__construct', $loginType, $this->db_user, $this->db_group, $shibbSessionIdKey);
        $userFromDB = $userHandler->lookUpShibbolethUserInDatabase();
        $this->assertFalse(is_array($userFromDB),'Did not expect array');
        $this->assertFalse($userFromDB);
    }

    /**
     * @test
     */
    public function lookUpShibbolethUserInDatabaseReturnsNullOnPidMismatchFe() {
        $userHandler = $this->getAccessibleMock(\TrustCnct\Shibboleth\User\UserHandler::class,['getEnvironmentVariable'],array(
            'FE',
            'fe_users',
            'fe_groups',
            'Shib_Session_ID',
            false,
            ''),
            '',false);
        $_SERVER['eppn'] = 'wrongpid@testshib.org';
        $loginType = 'FE';
        $db_user = 'fe_users';
        $db_group = 'fe_groups';
        $shibbSessionIdKey = 'Shib_Session_ID';
        $userHandler->_call('__construct', $loginType, $this->db_user, $this->db_group, $shibbSessionIdKey);
        $userFromDB = $userHandler->lookUpShibbolethUserInDatabase();
        $this->assertFalse(is_array($userFromDB),'Did not expect array');
        $this->assertFalse($userFromDB);
    }

    /**
     * @test
     */
    public function existingFeUserIsUpdatedCorrectly() {
        $userHandler = $this->getAccessibleMock(\TrustCnct\Shibboleth\User\UserHandler::class,['getEnvironmentVariable'],array(
            'FE',
            'fe_users',
            'fe_groups',
            'Shib_Session_ID',
            false,
            ''),
            '',false);
        $_SERVER['eppn'] = 'myself@testshib.org';
        $_SERVER['affiliation'] = 'goes to company';
        $loginType = 'FE';
        $db_user = 'fe_users';
        $db_group = 'fe_groups';
        $shibbSessionIdKey = 'Shib_Session_ID';

        $userHandler->_call('__construct', $loginType, $this->db_user, $this->db_group, $shibbSessionIdKey);
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
    public function existingFeUserUpdateFailsOnUnknownField() {
        $userHandler = $this->getAccessibleMock(\TrustCnct\Shibboleth\User\UserHandler::class,['getEnvironmentVariable'],array(
            'FE',
            'fe_users',
            'fe_groups',
            'Shib_Session_ID',
            false,
            ''),
            '',false);
        $_SERVER['eppn'] = 'myself@testshib.org';
        $_SERVER['affiliation'] = 'goes to company';
        $loginType = 'FE';
        $db_user = 'fe_users';
        $db_group = 'fe_groups';
        $shibbSessionIdKey = 'Shib_Session_ID';
        $userHandler->_call('__construct', $loginType, $this->db_user, $this->db_group, $shibbSessionIdKey);
        $userBefore = $userHandler->lookUpShibbolethUserInDatabase();
        $userBefore = $userHandler->transferShibbolethAttributesToUserArray($userBefore);
        unset($userBefore['_allowUser']);
        unset($userBefore['tx_shibboleth_shibbolethsessionid']);
        $userBefore['nonExistingField'] = 'dummy';
        $uidReported = $userHandler->synchronizeUserData($userBefore);
        $this->assertSame(0, (int) $uidReported);

    }

    /**
     * @test
     */
    public function nonExistingFeUserIsInsertedCorrectly() {
        $userHandler = $this->getAccessibleMock(\TrustCnct\Shibboleth\User\UserHandler::class,['getEnvironmentVariable'],array(
            'FE',
            'fe_users',
            'fe_groups',
            'Shib_Session_ID',
            false,
            ''),
            '',false);
        $_SERVER['eppn'] = 'new@testshib.org';
        $_SERVER['affiliation'] = 'goes to company';
        $_SERVER['entitlement'] = 'first time set';
        $loginType = 'FE';
        $db_user = 'fe_users';
        $db_group = 'fe_groups';
        $shibbSessionIdKey = 'Shib_Session_ID';
        $userHandler->_call('__construct', $loginType, $this->db_user, $this->db_group, $shibbSessionIdKey);
        $userBefore = $userHandler->transferShibbolethAttributesToUserArray(NULL);
        unset($userBefore['_allowUser']);
        unset($userBefore['tx_shibboleth_shibbolethsessionid']);
        $uidReported = $userHandler->synchronizeUserData($userBefore);
        $this->assertSame(6, (int) $uidReported);
        $userAfter = $userHandler->lookUpShibbolethUserInDatabase();
        $this->assertSame('goes to company', $userAfter['company']);
        $this->assertSame('first time set', $userAfter['fax']);
    }

    /**
     * @test
     */
    public function nonExistingFeUserInsertFailsOnUnknownField() {
        $userHandler = $this->getAccessibleMock(\TrustCnct\Shibboleth\User\UserHandler::class,['getEnvironmentVariable'],array(
            'FE',
            'fe_users',
            'fe_groups',
            'Shib_Session_ID',
            false,
            ''),
            '',false);
        $_SERVER['eppn'] = 'new@testshib.org';
        $_SERVER['affiliation'] = 'goes to company';
        $_SERVER['entitlement'] = 'first time set';
        $loginType = 'FE';
        $db_user = 'fe_users';
        $db_group = 'fe_groups';
        $shibbSessionIdKey = 'Shib_Session_ID';
        $userHandler->_call('__construct', $loginType, $this->db_user, $this->db_group, $shibbSessionIdKey);
        $userBefore = $userHandler->transferShibbolethAttributesToUserArray(NULL);
        unset($userBefore['_allowUser']);
        unset($userBefore['tx_shibboleth_shibbolethsessionid']);
        $userBefore['nonExistingField'] = 'dummy';
        $uidReported = $userHandler->synchronizeUserData($userBefore);
        $this->assertSame(0, (int) $uidReported);
    }

    /**
     * @test
     */
    public function lookUpShibbolethBeUserInDatabaseReturnsNullOnNonZeroPid() {
        $this->db_user = array(
            'table' => 'be_users',
            'userid_column' => 'uid',
            'username_column' => 'username',
            'userident_column' => 'password',
            'usergroup_column' => 'usergroup',
            'enable_clause' => ''
        );
        $this->db_group = array(
            'table' => 'be_groups'
        );
        $userHandler = $this->getAccessibleMock(\TrustCnct\Shibboleth\User\UserHandler::class,['getEnvironmentVariable'],array(
            'BE',
            'be_users',
            'be_groups',
            'Shib_Session_ID',
            false,
            ''
        ),
            '',false);
        $_SERVER['eppn'] = 'wrongpid@testshib.org';
        $loginType = 'BE';
        $db_user = 'be_users';
        $db_group = 'be_groups';
        $shibbSessionIdKey = 'Shib_Session_ID';
        $userHandler->_call('__construct', $loginType, $this->db_user, $this->db_group, $shibbSessionIdKey);
        $userFromDB = $userHandler->lookUpShibbolethUserInDatabase();
        $this->assertFalse(is_array($userFromDB),'Did not expect array');
        $this->assertEmpty($userFromDB);
    }

    /**
     * @test
     */
    public function lookUpShibbolethUserInDatabaseReturnsExistingBeUser() {
        $this->db_user = array(
            'table' => 'be_users',
            'userid_column' => 'uid',
            'username_column' => 'username',
            'userident_column' => 'password',
            'usergroup_column' => 'usergroup',
            'enable_clause' => ''
        );
        $this->db_group = array(
            'table' => 'be_groups'
        );
        $userHandler = $this->getAccessibleMock(\TrustCnct\Shibboleth\User\UserHandler::class,['getEnvironmentVariable'],array(
            'BE',
            'be_users',
            'be_groups',
            'Shib_Session_ID',
            false,
            ''),
            '',false);
        $_SERVER['eppn'] = 'myself@testshib.org';
        $loginType = 'BE';
        $shibbSessionIdKey = 'Shib_Session_ID';
        $userHandler->_call('__construct', $loginType, $this->db_user, $this->db_group, $shibbSessionIdKey);
        $userFromDB = $userHandler->lookUpShibbolethUserInDatabase();
        $this->assertTrue(is_array($userFromDB),'Expected array');
        $this->assertArrayHasKey('uid', $userFromDB);
        $this->assertSame(1, (int) $userFromDB['uid']);
        $this->assertStringStartsWith('myself', $userFromDB['username']);
    }

}
