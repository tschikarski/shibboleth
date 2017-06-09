<?php
namespace Trustcnct\Shibboleth\Controller;

/***
 *
 * This file is part of the "Trustcnct.Shibboleth" Extension for TYPO3 CMS.
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
     * action show
     *
     * @param \Trustcnct\Shibboleth\Domain\Model\LoginLink $loginLink
     * @return void
     */
    //public function showAction(\Trustcnct\Shibboleth\Domain\Model\LoginLink $loginLink)
    public function showAction()
    {
        $loginLink = 'Mein Dummylink in showAction.';
        $this->view->assign('loginLink', $loginLink);
    }
}
