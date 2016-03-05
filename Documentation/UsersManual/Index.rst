.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt


.. _users-manual:

Users manual
============

Your TYPO3 installation is protected by Shibboleth. It may be only the frontend, the backend, or both.

At first you need an account that can be used via Shibboleth. This normally is an account global to your
organisation. For obtaining this account you typically have to contact some central IT department. Please make sure
that they are informed about the application you want to access.

If you are used to a standard TYPO3 installation, you may notice some differences.

Depending on the specific configuration, you will need to initiate a Shibboleth login to access TYPO3.
That is, after clicking on a login button, you will see a page provided by Shibboleth, which allows you to
authenticate yourself to the global account. After doing so, you will be redirected to the TYPO3 application and
immediately logged in there.

Your specific TYPO3 implementation might offer you a logout or not. In all cases, this is only a local logout!
Be aware that your global Shibboleth session will remain active. This will enable you and every other person who gets access
to your browser to re-login to TYPO3 and any other application in your network that is protected by Shibboleth.

Therefore, be sure to close all windows of the browser, before you leave your work place!


.. toctree::
    :maxdepth: 2
    :titlesonly:

    Faq/Index
