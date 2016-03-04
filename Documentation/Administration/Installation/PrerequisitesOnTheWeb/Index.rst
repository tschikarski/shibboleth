.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../../Includes.txt


.. _administration-installation-prerequisites-on-the-web:

Prerequisites on the web server
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

You need to install and configure the “Native Service Provider” components of Shibboleth with
your Apache web server. There are installers for various operating systems, including Linux and
Windows. Details on how to install a Shiboleth service provider on an Apache web server are found in
the Shibboleth documentation (https://spaces.internet2.edu/display/SHIB2/Installation).

There are two components that interact to form the service provider: The Shibboleth daemon and the
mod_shib Apache module.

The main configuration file is typically named “shibboleth2.xml” (Location depending on
operating system, typically somewhere in an “etc” directory). We will refer to this file in the
next section, when explaining the extension configuration.

Additionally, you will have to activate Shibboleth authentication **in “lazy mode”**  for your
TYPO3 instance. For FE authentication you will need to protect at least the base path of your
installation, for BE authentication you will have to protect at least the “typo3” sub-folder. To
do so, you need to add the following two Apache directives into your site config or into an
“.htaccess” file.

::

        AuthType shibboleth
        require shibboleth

Explanation: The first line defines the authentication type to be shibboleth, e.g. in contrast to
“basic”. The second line is a pseudo-option, not forcing authentication on every http request
(“lazy mode”). 
