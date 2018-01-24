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

use Nimut\TestingFramework\TestCase\UnitTestCase;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class UserHandlerTest extends UnitTestCase
{
    protected $db_user;
    protected $db_group;

    /**
     * Test setup
     */
    protected function setUp() {
        parent::setUp();
        $GLOBALS['TYPO3_CONF_VARS']['DB']['Connections']['Default'] = array(); // Avoid exception in web/typo3conf/ext/shibboleth/Classes/User/UserHandler.php:76
        $enable_clause = '';
        $this->db_user = array(
            'table' => 'fe_users',
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
        if (class_exists(ConnectionPool::class)) {
            $this->expectException('RuntimeException');
            $this->expectExceptionMessage('The requested database connection named "Default" has not been configured.');

        }

    }

    /**
     * @test
     */
    public function tempTsfeIsFinallyUnsetTest()
    {
        /** @var \TrustCnct\Shibboleth\User\UserHandler $userHandler */
        $userHandler = new UserHandler('FE', $this->db_user, $this->db_group,'Shib_Session_ID',false,'');
        $this->assertFalse($userHandler->tsfeDetected);
        $this->assertEmpty($GLOBALS['TSFE']);
    }

    /**
     * @test
     */
    public function existingTsfeIsFinallyPresentTest()
    {
        $GLOBALS['TSFE'] = new \stdClass();
        $GLOBALS['TSFE']->cObjectDepthCounter = 100;
        /** @var \TrustCnct\Shibboleth\User\UserHandler $userHandler */
        $userHandler = new UserHandler('FE', $this->db_user, $this->db_group,'Shib_Session_ID',false,'');
        $this->assertTrue($userHandler->tsfeDetected);
        $this->assertObjectHasAttribute('cObjectDepthCounter',$GLOBALS['TSFE']);
    }

    /**
     * @test
     */
    public function validCObjCreatedTest() {
        $userHandler = new UserHandler('FE', $this->db_user, $this->db_group,'Shib_Session_ID',false,'');
        $this->assertNotEmpty($userHandler->cObj->data);
        $this->assertNotEmpty($userHandler->cObj->data['USER']);
    }

    /**
     * @test
     */
    public function environmentGoesIntoCObjData() {
        $_SERVER['UserHandlerTestEnvironment'] = 'UserHandlerTestValue';
        $userHandler = new UserHandler('FE', $this->db_user, $this->db_group,'Shib_Session_ID',false,'');
        $this->assertNotEmpty($userHandler->cObj->data['UserHandlerTestEnvironment']);
        $this->assertSame('UserHandlerTestValue',$userHandler->cObj->data['UserHandlerTestEnvironment']);
    }

    /**
     * @test
     */
    public function environmentPrefixIsRecognized() {
        $_SERVER['redirectShibbSomeEnvironment'] = 'ShibbSomeValue';
        $userHandler = new UserHandler('FE', $this->db_user, $this->db_group,'Shib_Session_ID',false,'redirect');
        $this->assertSame('ShibbSomeValue',$userHandler->cObj->data['ShibbSomeEnvironment']);
    }

    /**
     * Helper to debug variable contents
     *
     * @param null $mixed
     * @return string
     */
    private function var_dump_ret($mixed = null) {
        ob_start();
        var_dump($mixed);
        $content = ob_get_contents();
        ob_end_clean();
        return $content;
    }


}
