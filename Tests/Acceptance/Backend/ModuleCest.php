<?php

declare(strict_types=1);
namespace TrustCnct\Shibboleth\Tests\Acceptance\Backend;

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

use TrustCnct\Shibboleth\Tests\Acceptance\Support\BackendTester;

/**
 * Tests the styleguide backend module can be loaded
 */
class ModuleCest
{

    /**
     * @param BackendTester $I
     */
    public function _before(BackendTester $I)
    {
        $I->useExistingSession('admin');
        $I->switchToContentFrame();
    }

}
