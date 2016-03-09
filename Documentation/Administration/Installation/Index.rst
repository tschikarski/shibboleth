.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../Includes.txt


.. _administration-installation:

Installation
------------

Install the extension like any other TYPO3 extension. If you want to use Shibboleth for FE
authentication, you will have to insert the plug-in “Shibboleth Login” somewhere on your web
site.

The extension is tested as local as well as a system-wide extension.

After installing the extension you **must**  edit some of the extension configuration parameters, as
described in the “Configuration” section below. Additionally, you **must** add directives to your Apache configuration
or to your ``.htaccess`` file.
See :ref:`administration-specialfiles`.

If you decide to modify files (e.g. template files with the ``res`` directory), be sure to change their name or location.
See section :ref:`configuration-configuration-options` for the required changes to the configuration options.

As additional guidance, you should go through the :ref:`configuration-checklists`.



.. toctree::
    :maxdepth: 2
    :titlesonly:

    InstallationOfTheExtension/Index
