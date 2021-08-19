<?php

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

namespace TrustCnct\Shibboleth\User;

use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\TestingFramework\Core\AccessibleObjectInterface;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class UserHandlerTest extends UnitTestCase
{

    protected $resetSingletonInstances = true;

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
     * @var array
     */
    protected $db_group = [
        'table' => 'fe_groups'
    ];

    /**
     * @var UserHandler|MockObject|AccessibleObjectInterface
     */
    protected $userHandler;

    /**
     * Test setup
     */
    protected function setUp(): void
    {
        parent::setUp();
        $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['shibboleth'] = [
            'BE_applicationID' => '',
            'BE_autoImport' => '0',
            'BE_autoImportDisableUser' => '1',
            'BE_disabledUserRedirectUrl' => '/typo3conf/ext/shibboleth/Resources/Public/LogoutPages/nologinyet.html',
            'BE_enable' => '0',
            'BE_loginTemplatePath' => 'typo3conf/ext/shibboleth/Resources/Private/Templates/BeForm/login.html',
            'BE_logoutRedirectUrl' => '/typo3conf/ext/shibboleth/Resources/Public/LogoutPages/logout.html',
            'FE_applicationID' => '',
            'FE_autoImport' => '0',
            'FE_autoImport_pid' => '',
            'FE_enable' => '0',
            'debugLog' => '0',
            'enableAlwaysFetchUser' => '1',
            'entityID' => '',
            'forceSSL' => '1',
            'mappingConfigPath' => '/typo3conf/ext/shibboleth/Resources/Private/config.txt',
            'pageUidForTSFE' => '1',
            'sessionInitiator_Location' => '/Login',
            'sessions_handlerURL' => 'Shibboleth.sso',
        ];
        $GLOBALS['TYPO3_CONF_VARS']['DB']['Connections']['Default'] = []; // Avoid exception in web/typo3conf/ext/shibboleth/Classes/User/UserHandler.php:76
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        unset($GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['shibboleth']);
    }

    /**
     * @test
     */
    public function tempTsfeIsFinallyUnsetTest()
    {
        $GLOBALS['TSFE'] = null;
        $this->userHandler = $this->getAccessibleMock(UserHandler::class, null, ['FE', $this->db_user, $this->db_group,'Shib_Session_ID',false]);
        $this->assertFalse($this->userHandler->_get('tsfeDetected'));
        $this->assertFalse(isset($GLOBALS['TSFE']));
    }

    /**
     * @test
     */
    public function existingTsfeIsFinallyPresentTest()
    {
        $GLOBALS['TSFE'] = new \stdClass();
        $GLOBALS['TSFE']->cObjectDepthCounter = 100;
        $this->userHandler = $this->getAccessibleMock(UserHandler::class, null, ['FE', $this->db_user, $this->db_group,'Shib_Session_ID',false]);
        $this->assertTrue($this->userHandler->_get('tsfeDetected'));
        $this->assertObjectHasAttribute('cObjectDepthCounter',$GLOBALS['TSFE']);
    }

    /**
     * @test
     */
    public function validCObjCreatedTest()
    {
        $GLOBALS['TSFE'] = null;
        $this->userHandler = $this->getAccessibleMock(UserHandler::class, null, ['FE', $this->db_user, $this->db_group,'Shib_Session_ID',false]);
        $this->assertNotEmpty($this->userHandler->_get('cObj')->data);
    }

    /**
     * @test
     */
    public function environmentGoesIntoCObjData()
    {
        $GLOBALS['TSFE'] = null;
        $_SERVER['UserHandlerTestEnvironment'] = 'UserHandlerTestValue';
        $this->userHandler = $this->getAccessibleMock(UserHandler::class, null, ['FE', $this->db_user, $this->db_group,'Shib_Session_ID',false]);
        $this->assertNotEmpty($this->userHandler->_get('cObj')->data['UserHandlerTestEnvironment']);
        $this->assertSame('UserHandlerTestValue',$this->userHandler->_get('cObj')->data['UserHandlerTestEnvironment']);
    }

    /**
     * @test
     */
    public function environmentPrefixIsRecognized()
    {
        $GLOBALS['TSFE'] = null;
        $_SERVER['redirectShibbSomeEnvironment'] = 'ShibbSomeValue';
        $this->userHandler = $this->getAccessibleMock(UserHandler::class, null, ['FE', $this->db_user, $this->db_group,'Shib_Session_ID',false]);
        $this->assertSame('ShibbSomeValue',$this->userHandler->_get('cObj')->data['redirectShibbSomeEnvironment']);
    }

}
