.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt


.. _administration:

Administration
==============

.. _defining-requirements:

Defining your requirements
--------------------------

The extension is highly flexible and can be used in many constellations. That's why providing a simple step-by-step
instruction is a little bit difficult.

Before you start, a number of things should be clarified:

* Which parts of the TYPO3 application will be protected by Shibboleth? Backend and/or frontend?
* Which elements of the frontend/backend shall be accessible for Shibboleth users?
* Will there be other means of login, e.g. local BE login for emergency administration?
* Which groups of people will need access to the application?
* Will there be different access levels for people logging in via Shibboleth?
* On what criteria shall access be granted to people/groups logging in via Shibboleth?

The last point often is somehow connected to the question, which organisational unit is responsible for the TYPO3 instance.
For example, you might want an automatic mapping of some Shibboleth metadata to TYPO3 user groups.
Or you let Shibboleth just deliver the user ID and do all other user administration from the TYPO3 backend.
The first case fits situations, where access to all applications including TYPO3 is regulated centrally.
In contrast, the latter case would fit to situations, where responsibility for the TYPO3 instance is held by an independent organisational unit.

You even can decide to auto-import backend users at their first login attempt with the "disabled" flag set.
You will then have the chance to set access levels manually before activating those user accounts.

Shibboleth Overview
-------------------

As you will have to put together a number of components for a working environment, you really have to make yourself
familiar with some of these components. Except for the "shibboleth" extension, introduction to these components is
beyond the scope of this manual.

* On a central server: Shibboleth Identity Provider (IdP) and how it is linked to your user database (e.g. LDAP). [#]_
* On your server: Apache and Shibboleth Service Provider 2 (SP). Shibboleth SP comes in two interacting parts. One is the Shibboleth daemon, the other is the Apache module "mod_shib". [#]_
* An unambiguous "entityID" for your TYPO3 application. Typically, you choose to take your URL as "entityID".
* A configuration entry for your TYPO3 application within your Shibboleth configuration, which defines the way your SP will find and talk to your IdP(s).
* At least one test account within Shibboleth that allows testing Shibboleth login. When using Shibboleth metadata for authorization decisions, you should have test accounts covering all test cases.
* This extension within your TYPO3 instance.

.. [#] If you are just testing this extension and don't have your own IdP,
   look at http://www.testshib.org/, instead. You can register your (test) application there and will get access to an IdP.
   Also, this page will help you find a working example for ``shibboleth2.xml``.

.. [#] This extension is tested with ``shibboleth 2.5.6``



.. _about-lazymode:

Shibboleth's "lazy mode" and this extension
-------------------------------------------

Assuming you have activated Shibboleth within your Apache configuration by the directive ``AuthType Shibboleth``,
a very simple way of protecting web content by Shibboleth would be the ``require valid-user`` directive.
Every user not already authenticated by Shibboleth would be immediately redirected to the Shibboleth authentication page.

However, in case of TYPO3 this would not be acceptable in most cases.
For example, you probably have parts of the TYPO3 site, which should be accessible to all users,
including un-authenticated users from all over the world. Also, in case of problems with your Shibboleth environment,
you would loose any chance of logging into your backend.

Therefore, this extension assumes that you use the so-called "lazy mode" of Shibboleth. [#]_
It is activated by the directive ``require shibboleth`` within the Apache configuration. Basically, this means that
you have to care for a Shibboleth login yourself and "mod_shib" is not blocking access to TYPO3 for unauthenticated users.

As TYPO3 user, you now need a means to initiate a login process via your “Identity Provider”.

Therefore, the user has to be supplied with a link to “Login with Shibboleth”. This extension generates
the link automatically and offers a way to integrate it into your FE (by a plug-in) and into your BE
login form.

After clicking on to that link the user will be redirected to an identity provider (IdP), as defined
by the configuration made in your Shibboleth installation.

When the user authenticates himself to the IdP, he is (finally) redirected back to TYPO3. In this process, the IdP
transfers the user ID together with other metadata to the Shibboleth SP residing on your server.

That is, where this extension comes into play. It will take this information and - depending on your configuration -
create or update a user record, authenticate the user to the frontend or backend, set or update metadata of the TYPO3
user record, add the user to one or more groups, enable admin mode for a backend user etc.

.. [#] If you don't want to use "lazy mode", the extension will still work and can be useful.


A word on logout buttons
------------------------

* Don't think a logout is a simple thing in a federated SSO environment like "Shibboleth".
* Don't place a logout button for frontend users.
* Use the modified logout for backend users.

Closing the browser (all windows) is momentarily the only way of safely logging out from all Shibboleth-enabled applications.


.. toctree::
    :maxdepth: 2
    :titlesonly:

    Prerequisites/Index
    Installation/Index
    SpecialFiles/Index
