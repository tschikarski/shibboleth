.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../Includes.txt


.. _configuration-configuration-options:

Configuration Options
^^^^^^^^^^^^^^^^^^^^^

General
-------

=========================  ==========  ===========================================================================================================  ==========================================
Property:                  Data type:  Description:                                                                                                 Default:
=========================  ==========  ===========================================================================================================  ==========================================
mappingConfigPath          path        Point this to your mapping configuration file (for                                                           ``/typo3conf/ext/shibboleth/res/config.txt``
                                       usage of this file, see :ref:`configuration-configuration-options-mapping-configuration-file`).
                                       **In most cases you
                                       will have to change this.**  (Never apply your changes
                                       directly to the sample config files, as these would be
                                       overridden on extension updates.)

                                       Instead, put your configuration file somewhere outside
                                       the extension directory and point this
                                       configuration option to your new file!
-------------------------  ----------  -----------------------------------------------------------------------------------------------------------  ------------------------------------------
sessions_handlerURL        string      Shibboleth session handler URL. If you didn't change the handlerURL attribute within                         ``/Shibboleth.sso``
                                       ``shibboleth2.xml``, the default value would do.
-------------------------  ----------  -----------------------------------------------------------------------------------------------------------  ------------------------------------------
sessionInitiator_Location  string      Session initiator location (relative to handlerURL). Default value is okay, unless you changed this in       ``/Login``
                                       ``shibboleth2.xml``
=========================  ==========  ===========================================================================================================  ==========================================


FE Authentication
-----------------

=========================  ==========  ===========================================================================================================  ==========================================
Property:                  Data type:  Description:                                                                                                 Default:
=========================  ==========  ===========================================================================================================  ==========================================
FE_enable                  boolean     Activate Shibboleth for frontend authentication                                                              FALSE

                                       **ATTENTION**:
                                       Switching this from TRUE to FALSE doesn't deactivate users that might have been imported/
                                       activated for Shibboleth. In special circumstances, these user accounts might be misused to get
                                       unauthorized access to the system. **The same** is true, if you uninstall the shibboleth extension.
-------------------------  ----------  -----------------------------------------------------------------------------------------------------------  ------------------------------------------
FE_autoImport              boolean     Check this to allow automatic import of new Shibboleth                                                       FALSE
                                       users, based on Shibboleth attributes.

                                       **If you don't activate this option, all users must be
                                       manually added to TYPO3, before they can login.**
-------------------------  ----------  -----------------------------------------------------------------------------------------------------------  ------------------------------------------
FE_autoImport_pid          int         New users will be put into page with this pid. Define a sysfolder, e.g. ``FE_USER`` for this.

                                       **Must edit!**
=========================  ==========  ===========================================================================================================  ==========================================


BE Authentication
-----------------

=========================  ==========  ===========================================================================================================  ==========================================
Property:                  Data type:  Description:                                                                                                 Default:
=========================  ==========  ===========================================================================================================  ==========================================
BE_enable                  boolean     Activate Shibboleth for backend authentication                                                               FALSE

                                       **ATTENTION**:
                                       Switching this from TRUE to FALSE doesn't deactivate users that might have been imported/
                                       activated for Shibboleth. In special circumstances, these user accounts might be misused to get
                                       unauthorized access to the system. **The same** is true, if you uninstall the shibboleth extension.
-------------------------  ----------  -----------------------------------------------------------------------------------------------------------  ------------------------------------------
BE_autoImport              boolean     Check this to allow automatic import of new Shibboleth                                                       FALSE
                                       users, based on Shibboleth attributes. If set, any Shibboleth user will get imported as TYPO3
                                       backend user. **Take care to activate this only, if you know exactly that you want this.**

                                       In connection with the ``BE_autoImportDisableuser``, you can automatically create the user and fill in
                                       metadata from Shibboleth, without actually allowing immediate access to the user.
-------------------------  ----------  -----------------------------------------------------------------------------------------------------------  ------------------------------------------
BE_autoImportDisableUser   boolean     If this is set and BE_autoImport is active, new BE                                                           TRUE
                                       users will be created in disabled state, yet to be
                                       activated manually by an admin.
-------------------------  ----------  -----------------------------------------------------------------------------------------------------------  ------------------------------------------
BE_loginTemplatePath       string      Customized backend login page. Hide login form for local users and provide link to Shibboleth login.         ``typo3conf/ext/shibboleth/res/be_form/login7.html``

                                       Backend login page is not modified, if this option is empty.

                                       **Do not modify original template file. It will be overridden by extension updates.**
-------------------------  ----------  -----------------------------------------------------------------------------------------------------------  ------------------------------------------
BE_logoutRedirectUrl       string      Redirect to this URL after backend logout. (**Without redirect, backend logout is followed by                ``/typo3conf/ext/shibboleth/res/be_form/logout.html``
                                       immediate re-login.**)

                                       **Change filename or path, if you want to modify these files. Original files will be overwritten
                                       by extension updates!**
-------------------------  ----------  -----------------------------------------------------------------------------------------------------------  ------------------------------------------
BE_disabledUserRedirectUrl  string     (Optional) Redirect to this URL if a successful Shibboleth authentication results to an user record in       ``/typo3conf/ext/shibboleth/res/be_form/nologinyet.html``
                                       state "disabled". This would be typical, if Shibboleth users shall be mapped to TYPO3-BE, but are
                                       required manual activation.

                                       **Change filename or path, if you want to modify these files. Original files will be overwritten
                                       by extension updates!**
=========================  ==========  ===========================================================================================================  ==========================================


Advanced
--------

=========================  ==========  ===========================================================================================================  ==========================================
Property:                  Data type:  Description:                                                                                                 Default:
=========================  ==========  ===========================================================================================================  ==========================================
enableAlwaysFetchUser      boolean     Run shibboleth extension on every page load, as opposed to only login events.                                TRUE
-------------------------  ----------  -----------------------------------------------------------------------------------------------------------  ------------------------------------------
entityID                   string      (optional) Here you can specify an entityID of your IdP. Would overwrite the IdP setting within
                                       ``shibboleth2.xml``
-------------------------  ----------  -----------------------------------------------------------------------------------------------------------  ------------------------------------------
forceSSL                   boolean     Check this to force SSL for the session initiating.                                                          TRUE
                                       This option is of importance only, if the application is run on a non-secure connection.
-------------------------  ----------  -----------------------------------------------------------------------------------------------------------  ------------------------------------------
FE_applicationID           string      (optional) If you don't run your SP under the
                                       “default” application, you will need to enter your
                                       application ID here.

                                       See your shibboleth2.xml file.
-------------------------  ----------  -----------------------------------------------------------------------------------------------------------  ------------------------------------------
BE_applicationID           string      (optional) If you don't run your SP under the
                                       “default” application, you will need to enter your
                                       application ID here.

                                       See your shibboleth2.xml file:
=========================  ==========  ===========================================================================================================  ==========================================

Debugging
---------

=========================  ==========  ===========================================================================================================  ==========================================
Property:                  Data type:  Description:                                                                                                 Default:
=========================  ==========  ===========================================================================================================  ==========================================
FE_devLog                  boolean     Write internal information on frontend logins to 'devlog'                                                    FALSE
-------------------------  ----------  -----------------------------------------------------------------------------------------------------------  ------------------------------------------
BE_devLog                  boolean     Write internal information on backend logins to 'devlog'                                                     FALSE
-------------------------  ----------  -----------------------------------------------------------------------------------------------------------  ------------------------------------------
database_devLog            boolean     When writing devlog info, also include details on database operations.                                       FALSE
=========================  ==========  ===========================================================================================================  ==========================================



.. toctree::
    :maxdepth: 2
    :titlesonly:

    MappingConfigurationFile/Index
