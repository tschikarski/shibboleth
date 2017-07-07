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

/**
 * LoginLinkController
 */
class LoginLinkController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{
    /**
     * @var \TrustCnct\Shibboleth\Service\LoginLinkService
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
        $this->view->assign('loginLinkUrl', $this->loginLinkService->createLink());
    }
}
