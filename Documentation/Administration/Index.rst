.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt


.. _administration:

Administration
==============

Basics
------

The extension is highly flexible and can be used in many constellations. That's why providing a simple step-by-step
instruction is a little bit difficult.

Before you start, a number of things should be clarified:

* Which parts of the TYPO3 application will be protected by Shibboleth? Backend and/or frontend?
* Which elements of the frontend/backend shall be accessible for Shibbleth users?
* Will there be other means of login, e.g. local BE login for emergency administration?
* Which groups of people will need access to the application?
* Will there be different access levels for people logging in via Shibboleth?
* On what criteria shall access be granted to people/groups logging in via Shibboleth?

The last point often is somehow connected to the question, which organisational unit is responsible for the TYPO3 instance.
For example, you might want an automatic mapping of some Shibboleth metadata to TYPO3 user groups.
Or you let Shibboleth just deliver the user ID and do all other user administration from the TYPO3 backend.

As you will have to put together a number of components for a working environment, you really have to make yourself
familiar with some of these components. Except for the "shibboleth" extension, introduction to these components is
beyond the scope of this manual.

* On a central server: Shibboleth IdP and how it is linked to your user database (e.g. LDAP).
* On your server: Apache and Shibboleth SP. Shibboleth comes in two parts. One is the Shibboleth daemon, the other is the Apache module "mod_shib2".
* An unambiguous "entityID" for your TYPO3 application.
* A configuration entry for your TYPO3 application within your Shibboleth configuration, which defines the way your SP will find and talk to your IdP(s).
* At least one test account within Shibboleth that has all parameters set as if to access your TYPO3 application.
* This extension within your TYPO3 instance.

Shibboleth's "lazy mode"
------------------------

Assuming you have activated Shibboleth within your Apache configuration by the directive ``AuthType Shiboleth``,
a very simple way of protecting web content by Shibboleth would be the ``require valid-user`` directive.
Every user not already authenticated by Shibboleth would be immediately redirected to the Shibboleth authentication page.

However, in case of TYPO3 this would not be acceptable for most installations.
For example, you probably have parts of the TYPO3 site, which should be accessible to all users,
including un-authenticated users from all over the world. Also, in case of problems with your Shibboleth environment,
you would loose any chance of logging into your backend.

Therefore, this extension assumes that you use the so-called "lazy mode" of Shibboleth.
It is activated by the directive ``require shibboleth`` within the Apache configuration. Basically, this means that
you have to care for a Shibboleth login yourself.

As TYPO3 user, you now need a means to initiate a login process via your “Identity Provider”.

Therefore, the user is supplied with a link to “Login with Shibboleth”. This extension generates
the link automatically and offers a way to integrate it into your FE (by a plug-in) and into your BE
login form.

After clicking on to that link the user will be redirected to an identity provider (IdP), as defined
by the configuration made in your Shibboleth installation.

When the user authenticates himself to the IdP, he is redirected back to TYPO3. The native service
provider installation does it's magic by supplying the Apache server with additional environment
variables that contain all information about the user, the IdP is configured to transfer to our
application. The shibboleth extension will evaluate these attributes to decide on how to authorize
the user depending on the extension configuration.


.. toctree::
    :maxdepth: 2
    :titlesonly:

    Installation/Index
