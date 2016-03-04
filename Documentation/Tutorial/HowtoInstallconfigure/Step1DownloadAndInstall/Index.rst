.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../../Includes.txt


.. _tutorial-howto-installconfigure-step-1-download-and-install:

Step 1: Download and install the installer
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Shibboleth provides an .msi installer file, that can be found at the download page:
`http://shibboleth.internet2.edu/downloads/shibboleth/cppsp/latest/win32/.
<http://shibboleth.internet2.edu/downloads/shibboleth/cppsp/latest/win32/>`__ 

You need to download the file `shibboleth-sp-2.3.1-win32.msi.
<http://shibboleth.internet2.edu/downloads/shibboleth/cppsp/latest/win32/shibboleth-sp-2.3.1-win32.msi>`__


Save the file whereever you want and execute it.

!! On Windows 7 you might get an “Internal Error 2738”. In that case google for this error. It's
not about shibboleth, but some vbscript-settings in the registry. You'll find a solution in google
for that.

Finish the installer with mostly the default settings. The only exception is, that you probably
don't want to install the IIS ….............. (screenshot)

Now you should find a Shibboleth 2 Daemon in your services. You have to start this service manually
after the first installation (or reboot windows ;-)).
