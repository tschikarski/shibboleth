.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../Includes.txt


.. _configuration-checklists:

Checklists
^^^^^^^^^^

FE authentication
-----------------

* Is ``FE_enable`` enabled?
* Did you place the Shibboleth login plugin on the page?
* Does config ``mappingConfigPath`` point to an existing file? *Don't use one of the sample files directly, it will be overwritten at extension updates.*
* Do you have a sysfolder for your users? Is ``FE_autoImport_pid`` set to this folder?
* Is ``FE_autoImport`` enabled or do you have created user records matching with your (test) users?
* Does the mapping configuration set ``allowUser`` to 1?
* Will the user be assigned to at least one frontend user group?

BE authentication
-----------------

* Is ``BE_enable`` enabled?
* Is the timeout for BE users (practically) disabled by setting [BE][sessionTimeout] to 86400 or higher (recommended)?
* Is there an entry for ``BE_loginTemplatePath`` and ``BE_loginTemplateCss``?
* If you changed such templates, did you change all file names to protect the files from overwriting by extension updates?
* Is there an entry for ``BE_logoutRedirectUrl`` (recommended)?
* If you changed the sample files for logout redirection, did you change all file names to protect the files from overwriting by extension updates?
* Does config ``mappingConfigPath`` point to an existing file? *Don't use one of the sample files directly, it will be overwritten at extension updates.*
* Is ``BE_autoImport`` enabled or do you have created user records matching with your (test) users?
* If ``BE_autoImportDisableUser`` is enabled, are you aware that you have to enable users after their first login attempt?
* Does the mapping configuration set ``allowUser`` to 1?


.. toctree::
    :maxdepth: 2
    :titlesonly:

    MappingConfigurationFile/Index
