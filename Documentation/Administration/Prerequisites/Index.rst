.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../Includes.txt


.. _administration-prerequisites:

Prerequisites
^^^^^^^^^^^^^

You need to install and configure the “Native Service Provider” components of Shibboleth with
your Apache web server. There are installers for various operating systems, including Linux and
Windows. Details on how to install a Shibboleth service provider on an Apache web server are found in
the Shibboleth documentation (https://wiki.shibboleth.net/confluence/display/SHIB2/Installation).

The service provider is devided into two interacting components. The Shibboleth daemon and the
mod_shib Apache module.

The main configuration file is typically named ``shibboleth2.xml`` (Location depending on
operating system, typically somewhere in an “etc” directory). This file defines, how the SP is interacting with
the Identity Providers you will use to authenticate users. It will define:

* How a user is directed to his Identity Provider (IdP), when he wants to log in.
* How the SP is talking to the IdP and which keys and certificates this communication will use.
* How user information is filtered and mapped on your server. [#]_

Please refer to the Shibboleth documentation on details how to
configure this file.

Additionally, you will have to activate Shibboleth authentication **in “lazy mode”**  for your
TYPO3 instance. For FE authentication you will need to protect at least the base path of your
installation\ [#]_, for BE authentication you will have to protect at least the “typo3” sub-folder. To
do so, you need to add the following two Apache directives into your site config or into an
“.htaccess” file in web root.

This extension ships with an example file. It can be found in ``res/_.htaccess``

::

		...
		RewriteEngine On
		# Skip any other rewrite for Shibboleth handler URL.
		RewriteRule ^(Shibboleth.sso/) - [L]
		...

Explanation: Do not rewrite the URL of the Shibboleth module/daemon.
This must be inserted into .htaccess **in front of** any other rewrite or redirect rules.

::

		...
		# Force HTTPS also for all pages, add RewriteCond or edit pattern for RewriteRule to confine to certain FE pages
		RewriteCond %{HTTPS} !=on
		RewriteRule ^(.*)$ https://%{SERVER_NAME}/$1 [L,R=301]
		...

Explanation: Force SSL. Do this unless you exactly know why you don't want it to do. Be aware that you need a special
Shibboleth configuration to allow insecure connections. Take care of all security implications! See https://wiki.cam.ac.uk/raven/SSL,_certificates_and_security_with_Shibboleth

::

        ...
        AuthType shibboleth
        require shibboleth
        ...

Explanation: The first line defines the authentication type to be shibboleth, e.g. in contrast to
“basic”. The second line is needed to activate ´lazy mode´.

.. [#] With the flexibility of this TYPO3 extension, in most cases you don't need to change any mappings within ``shibboleth2.xml``.
.. [#] If you need Shibboleth only for FE, don't be afraid to protect just the complete TYPO3 instance. Just don't activate BE authentication within the extension.