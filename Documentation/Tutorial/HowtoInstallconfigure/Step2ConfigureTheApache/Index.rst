.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../../Includes.txt


.. _tutorial-howto-installconfigure-step-2-configure-the-apache:

Step 2: Configure the apache
^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Add the following lines to your httpd.conf file (C:\xampp\apache\conf\httpd.conf):

::

    # Shibboleth settings
    Include "C:\opt\shibboleth-sp\etc\shibboleth\apache22.config"

Restart the apache.
