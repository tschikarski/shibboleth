<?php
namespace TrustCnct\Shibboleth\Controller;

/***
 *
 * This file is part of the "TrustCnct.Shibboleth" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 *  (c) 2017
 *
 ***/

use TrustCnct\Shibboleth\Service\LoginLinkService;

/**
 * LoginLinkController
 */
class LoginLinkController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{
    /**
     * @var LoginLinkService
     * @inject
     */
    protected $loginLinkService;

    /**
     * action show
     *
     * @return void
     */
    //public function showAction(\TrustCnct\Shibboleth\Domain\Model\LoginLink $loginLink)
    public function showAction()
    {
        $loginLink = $this->loginLinkService->createLink();
        $this->view->assign('loginLink', $loginLink);
    }
}
