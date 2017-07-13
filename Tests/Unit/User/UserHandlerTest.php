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

use TYPO3\CMS\Core\Utility\GeneralUtility;

class UserHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function tempTsfeIsFinallyUnsetTest()
    {
        /** @var \TrustCnct\Shibboleth\User\UserHandler $userHandler */
        $userHandler = new UserHandler('FE','fe_users','fe_groups','Shib_Session_ID',false,'');
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
        $userHandler = new UserHandler('FE','fe_users','fe_groups','Shib_Session_ID',false,'');
        $this->assertTrue($userHandler->tsfeDetected);
        $this->assertObjectHasAttribute('cObjectDepthCounter',$GLOBALS['TSFE']);
    }

    /**
     * @test
     */
    public function validCObjCreatedTest() {
        $userHandler = new UserHandler('FE','fe_users','fe_groups','Shib_Session_ID',false,'');
        $this->assertNotEmpty($userHandler->cObj->data);
        $this->assertNotEmpty($userHandler->cObj->data['USER']);
    }

    /**
     * @test
     */
    public function environmentGoesIntoCObjData() {
        $_SERVER['UserHandlerTestEnvironment'] = 'UserHandlerTestValue';
        $userHandler = new UserHandler('FE','fe_users','fe_groups','Shib_Session_ID',false,'');
        $this->assertNotEmpty($userHandler->cObj->data['UserHandlerTestEnvironment']);
        $this->assertSame('UserHandlerTestValue',$userHandler->cObj->data['UserHandlerTestEnvironment']);
    }

    /**
     * @test
     */
    public function environmentPrefixIsRecognized() {
        $_SERVER['redirectShibbSomeEnvironment'] = 'ShibbSomeValue';
        $userHandler = new UserHandler('FE','fe_users','fe_groups','Shib_Session_ID',false,'redirect');
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
