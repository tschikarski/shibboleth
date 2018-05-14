.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt


.. _changelog:

ChangeLog
=========

==========  ==============================
Version     Changes:
==========  ==============================
...         Fix target URL when forcing
            SSL.

            Fix unwanted logout, when
            Shibboleth session ID changes
            without changing the user.
----------  ------------------------------
3.1.4-beta  Fix creation of BE users at
            FE login in certain cases.

            Avoid logout of existing local
            BE session at FE login.
----------  ------------------------------
3.1.3-beta  Rename and move template files

            Fix redirect on disabled BE
            users, when debugging was off
----------  ------------------------------
3.1.2-beta  - broken release with no
            improvement
----------  ------------------------------
3.1.1-beta  Fix wrong version number
            3.2.0 shown in extension
            manager
----------  ------------------------------
3.1.0-beta  Use Doctrine query builder,
            if available (Deprecation)

            Confine search for already
            imported users to correct PID

            Improve code base and test
            coverage
----------  ------------------------------
3.0.4       Add handling of prefixed
            server variables

            Allow installation in
            composer mode

            If BE_logoutRedirectUrl
            invalid, prefix correct
            Shibboleth handler URL

            Allow special redirect
            in case of a Shibboleth
            user is not yet enabled for
            BE access

            Reject logins, if user ID
            is empty string
----------  ------------------------------
3.0.3       Fix PHP Warning due to
            dangling reference to
            registerToolbarItem.php
----------  ------------------------------
3.0.2       Fix error message referencing
            removed directory "hooks"
----------  ------------------------------
3.0.1       Removal of obsolete code and
            settings:
            Config BE_loginTemplateCss

            Change sorting of
            LoginProviders - Shibboleth
            now first
----------  ------------------------------
3.0.0       Compatibility with 7.6 LTS:
            internal release without
            updated documentation and
            without cleanup
----------  ------------------------------
2.0.4       Neutral CI for
            logout
            button,
            fixed behavior for
            inactive BE auth:
            Thomas Schikarski
----------  ------------------------------
2.0.3       Modification of
            BE login form and logout
            button for TU München:
            Andreas Groth
----------  ------------------------------
2.0.2       First release for TYPO3 6.2:
            Thomas Schikarski
----------  ------------------------------
1.0.0       Release for TYPO3 4.5:
            Thomas Schikarski
----------  ------------------------------
0.1.0       First draft:
            Thomas Schikarski,
            Irene Höppner
==========  ==============================
