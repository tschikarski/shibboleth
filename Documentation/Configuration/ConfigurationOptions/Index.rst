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
mappingConfigPath          path        Point this to your mapping configuration file (for                                                           /typo3conf/ ext/shibboleth/ res/config.txt
                                       usage of this file, see below). **In most cases you
                                       will have to change this.**  (Never apply your changes
                                       to the original config.txt, as these would be
                                       overridden on extension updates.)

                                       Instead, put your configuration file somewhere outside
                                       the extension directory and point to this file by this
                                       configuration option!
-------------------------  ----------  -----------------------------------------------------------------------------------------------------------  ------------------------------------------
entityID                   string      (optional) Here you can specify an entityID (= unique
                                       identifier for your SP). Influences how the link to
                                       your session initiator is generated. If set, the value
                                       will be added as “entityID”-parameter to the
                                       session initiator link.
-------------------------  ----------  -----------------------------------------------------------------------------------------------------------  ------------------------------------------
sessions_handlerURL        string      We need to create the link for Login, i.e. ...  <a                                                           /Shibboleth.sso
                                       href="`http://www.example.org
                                       <http://testshibb.abezetdom.local/Shibboleth.sso/TestShib?target=http%3A%2F%2Ftestshibb.abezetdom.local>`__
                                       `**/Shibboleth.sso**
                                       <http://testshibb.abezetdom.local/Shibboleth.sso/TestShib?target=http%3A%2F%2Ftestshibb.abezetdom.local>`__
                                       `/Login?target=
                                       <http://testshibb.abezetdom.local/Shibboleth.sso/TestShib?target=http%3A%2F%2Ftestshibb.abezetdom.local>`__
                                       `http://www.example.org
                                       <http://testshibb.abezetdom.local/Shibboleth.sso/TestShib?target=http%3A%2F%2Ftestshibb.abezetdom.local>`__
                                       ">Shibboleth-Login</a>  Must coincide with the
                                       handlerURL attribute of the Sessions element in your
                                       shibboleth2.xml file, for example: <Sessions
                                       lifetime="28800" timeout="3600" checkAddress="false"
                                       handlerURL="**/Shibboleth.sso** " handlerSSL="false">
-------------------------  ----------  -----------------------------------------------------------------------------------------------------------  ------------------------------------------
forceSSL                   boolean     Check this to force SSL for the session initiating.                                                          TRUE
-------------------------  ----------  -----------------------------------------------------------------------------------------------------------  ------------------------------------------
sessionInitiator_Location  string      We need to create the link for Login, i.e. ...  <a                                                           /Login
                                       href="`http://www.example.org/Shibboleth.sso
                                       <http://testshibb.abezetdom.local/Shibboleth.sso/TestShib?target=http%3A%2F%2Ftestshibb.abezetdom.local>`__
                                       `**/Login**
                                       <http://testshibb.abezetdom.local/Shibboleth.sso/TestShib?target=http%3A%2F%2Ftestshibb.abezetdom.local>`__
                                       `?target
                                       <http://testshibb.abezetdom.local/Shibboleth.sso/TestShib?target=http%3A%2F%2Ftestshibb.abezetdom.local>`__
                                       `**=**
                                       <http://testshibb.abezetdom.local/Shibboleth.sso/TestShib?target=http%3A%2F%2Ftestshibb.abezetdom.local>`__
                                       `http://www.example.org
                                       <http://testshibb.abezetdom.local/Shibboleth.sso/TestShib?target=http%3A%2F%2Ftestshibb.abezetdom.local>`__
                                       ">Shibboleth-Login</a>  <SessionInitiator type="SAML2"
                                       Location="**/Login** " isDefault="true"
                                       defaultACSIndex="1" id="TestShib"
                                       entityID="https://idp.testshib.org/idp/shibboleth"
                                       template="bindingTemplate.html" />
-------------------------  ----------  -----------------------------------------------------------------------------------------------------------  ------------------------------------------
FE_applicationID           string      (optional) If you don't run your SP under the
                                       “default” application, you will need to enter your
                                       application ID here.

                                       See your shibboleth2.xml file:

                                       <ApplicationDefaults **id="default"** ... >
-------------------------  ----------  -----------------------------------------------------------------------------------------------------------  ------------------------------------------
FE_autoImport              boolean     Check this to allow automatic import of new Shibboleth                                                       TRUE
                                       users, based on Shibboleth attributes. (**Default is
                                       TRUE!** )
-------------------------  ----------  -----------------------------------------------------------------------------------------------------------  ------------------------------------------
FE_autoImport_pid          int         New users will be put into page with this pid.                                                               29
-------------------------  ----------  -----------------------------------------------------------------------------------------------------------  ------------------------------------------
BE_linkInLoginForm         boolean     Check this to activate JavaScript insertion of a link                                                        TRUE
                                       to the Shibboleth authentication in the BE login form.
-------------------------  ----------  -----------------------------------------------------------------------------------------------------------  ------------------------------------------
BE_applicationID           string      (optional) If you don't run your SP under the
                                       “default” application, you will need to enter your
                                       application ID here.

                                       See your shibboleth2.xml file:

                                       <ApplicationDefaults **id="default"** ... >
-------------------------  ----------  -----------------------------------------------------------------------------------------------------------  ------------------------------------------
BE_autoImport              boolean     Check this to allow automatic import of new Shibboleth                                                       FALSE
                                       users, based on Shibboleth attributes. Inactive by
                                       default.
-------------------------  ----------  -----------------------------------------------------------------------------------------------------------  ------------------------------------------
BE_autoImportDisableUser   boolean     If this is set and BE_autoImport is active, new BE                                                           TRUE
                                       users will be created in disabled state, yet to be
                                       activated by an admin.
=========================  ==========  ===========================================================================================================  ==========================================

.. toctree::
    :maxdepth: 2
    :titlesonly:

    MappingConfigurationFile/Index
