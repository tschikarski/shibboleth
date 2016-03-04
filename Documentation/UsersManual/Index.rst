.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt


.. _users-manual:

Users manual
============

This extension is an authentication extension, which allows to use Shibboleth as login system for FE
as well as BE authentication. It requires the mod_shib Apache module installed and configured on the
web server. It offers versatile options to handle users being authenticated by Shibboleth. 

“The Shibboleth® System is a standards based, open source software package for web single sign-on
across or within organizational boundaries. It allows sites to make informed authorization decisions
for individual access of protected online resources in a privacy-preserving manner.” (Cited from
http://shibboleth.internet2.edu/about.html).

As FE or BE user, you now need a means to initiate a login process via your “Identity Provider”.
We don't want to do this by a forced redirect to the Shibboleth login page, because this would make
any anonymous access as well as any other type of authentication impossible. 

Fortunately, Shibboleth offers the so-called “lazy mode”, i.e. Shibboleth will not redirect the
user automatically to the login process. 

Instead, the user is supplied with a link to “Login with Shibboleth”. This extension generates
the link automatically and offers a way to integrate it into your FE (by a plug-in) and into your BE
login form.

After clicking on to that link the user will be redirected to an identity provider (IdP), as defined
by the configuration made in your Shibboleth installation. Instead, it is also possible to route the
user to a so-called “Where are you from” Server (WAYF).

When the user authenticates himself to the IdP, he is redirected back to TYPO3. The native service
provider installation does it's magic by supplying the Apache server with additional environment
variables that contain all information about the user, the IdP is configured to transfer to our
application. The shibboleth extension will evaluate these attributes to decide on how to authorize
the user depending on the extension configuration.

.. toctree::
    :maxdepth: 2
    :titlesonly:

    Faq/Index
