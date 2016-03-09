.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt


.. _debugging:

Debugging
=============

Checking your Apache Environment
--------------------------------

**First make sure, your Shibboleth environment is operational.** See section :ref:`configuration`.

Note that at this point, you still don't know exactly, if the Apache Shibboleth module is also active for your TYPO3 directory.
You will have to go through the next steps, if you can't log in via Shibboleth at this point.

Make sure to recognize a successful login
-----------------------------------------

For frontend authentication, you need to have a page that clearly shows you, if you are authenticated.
Place a content element on the page and apply "Show at login" access right to it.

Use two different browsers for backend and for testing the frontend. Do not log in to the backend with the browser used for testing.

Create a local FE user and verify that you can recognize an authenticated session. Log this local user out again.

Activating devlog debugging
---------------------------

This extension is able to write debug information via 'devlog'. Install the extension 'devlog'.
If you have access to the server, you might want to use the file based variant 'rlmp_filedevlog' instead and run something like ``tail -f debug.log`` on the console.

With this installed, go to the extension configuration and select the "debugging" tab. Here, you can activate debug logs for frontend and/or backend.

Don't start with additional debugging info, as it fills up your debug log with even more information.

*Hint:* Debugging BE authentication is a bit tricky, when you have the TYPO3 backend open, as you might be confused by AJAX requests showing up in the log.
That's a good reason to switch to file based logging. It will allow you to keep the BE closed while testing.

Checking if the extension is active
-----------------------------------

Run a login attempt. Search the log for 'shibboleth'.

If no result:

* Extension installed?
* Extension configuration: frontend/backend authentication activated as required?
* Debugging active for frontend/backend?

Checking if the Shibboleth module is active
-------------------------------------------

From now on concentrate on debug log entries from 'shibboleth'. Check latest entry "getUser ($_SERVER)".
Check the list of server environment variables. Are the Shibboleth variables set, like in the basic test from :ref:`configuration`?

If not, make sure to activate the Apache configuration directives (e.g. within ``.htaccess``).

.. code-block:: none

	AuthType shibboleth
	require shibboleth

Checking if the user is created within the TYPO3 database
---------------------------------------------------------

Check the database table for an entry of the new user. You might need something like this to identify a Shibboleth user:

``SELECT * FROM fe_users WHERE password LIKE 'shibb:%';``

If the user is not created, check the extension configuration for the "auto import" flag.

Search the log for entries that might explain, why the user is not accepted / created.

Be sure that your mapping configuration sets ``allowUser`` to 1. This log entry will help: "authUser: ($user)". In the data, look for "_allowUser".

Checking why a user is created, but not authenticated to TYPO3
--------------------------------------------------------------

Double check that the user is not authenticated. Reload the browser.

Note that you might actually have a successful login followed by an immediate logout.
Skimming through the log will tell you the difference.

Successful logins show up in the devlog by entries from ``TYPO3\CMS\Core\Authentication\AbstractUserAuthentication`` containing the word "authenticated".

Look up the user in the database and check for inconsistencies in the field values. You might want to create a functional local user and compare it to the Shibboleth user record.

For frontend users, again make sure there is at least one user group assigned.

If all this fails, sleep one night and try again.



.. toctree::
    :maxdepth: 2
    :titlesonly:

