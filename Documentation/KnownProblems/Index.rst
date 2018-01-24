.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt


.. _known-problems:

Known problems
==============

changed FE-Users PID - unexpected behaviour possible
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

When FE users were imported to a storage folder, later change of the storage PID in the extension config might result
in unexpected behaviour, as the extension does probably not recreate users in the new storage folder.

Server clusters and load balancers
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

The extension is already successfully in use on a server cluster behind a load balancer. However, in the early implementation phase
peculiar behavior has been observed. After a number of changes on the configuration of the shibboleth extension, the
web servers as well as of the load balancers, these issues have not reappeared in production phase.

Unfortunately, a thorough analysis was not possible and no specific advice can be given.

It is worth noting that running shibboleth SP's behind load balancers is not a TYPO3 specific task. Nevertheless, careful
selection of configuration options within the shibboleth extension may help. Specifically, you may want to look into
settings "alwaysFetchUser" as well as timeout settings of login sessions.

For further understanding the issue, please read this: https://wiki.shibboleth.net/confluence/display/SHIB2/NativeSPClustering

Logout
^^^^^^

Logout's can only be done locally and will not end the global Shibboleth session. It is necessary to redirect the user
away from the TYPO3 frontend / backend immediately after the logout. Otherwise, the still active Shibboleth session
will immediately re-login the user to the TYPO3 application.

For a discussion on the difficulties of a global logout see: https://wiki.shibboleth.net/confluence/display/SHIB2/IdPEnableSLO