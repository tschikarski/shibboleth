.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../Includes.txt


.. _administration-specialfiles:

Special Files
^^^^^^^^^^^^^

Sample files
------------

+-------+--------------------+------------+--------------------------------+------------------------------------------------------------+
| Path  | File               | Must have  | Must edit                      | Remarks                                                    |
+=======+====================+============+================================+============================================================+
| res/  | _.htaccess         | Yes        | Yes (add/rename to .htaccess)  | Always insert as first lines of your global .htaccess [#]_ |
+-------+--------------------+------------+--------------------------------+------------------------------------------------------------+
| res/  | sample-config.txt  | Yes        | Yes (New file name!)           | Path and name configurable                                 |
+-------+--------------------+------------+--------------------------------+------------------------------------------------------------+

.. [#] In case you only want to protect the backend, you might edit/add to an .htaccess file within the typo3 directory, instead.

Resources within the file system
--------------------------------

+----------------------+--------------------+------------+------------------------------------+------------------------------------------------------------+
| Path                 | File               | Must edit  | Purpose                            | Remarks                                                    |
+======================+====================+============+====================================+============================================================+
| res/be_form          | login7.html        | No         | Template for BE login form         | used by ShibbolethLoginProvider, needs Layout file         |
+----------------------+--------------------+------------+------------------------------------+------------------------------------------------------------+
| res/be_form          | logout.html        | No         | Sample message after BE logout     | Needed as redirect target after logout to avoid re-login   |
+----------------------+--------------------+------------+------------------------------------+------------------------------------------------------------+
| res/be_form          | nologinyet.html    | Yes        | Sample message after login attempt | Needed as redirect target for not-yet-enabled users        |
+----------------------+--------------------+------------+------------------------------------+------------------------------------------------------------+
| res/Private/Layouts  | Login7Layout.html  | No         | Layout for BE login form           | used by ShibbolethLoginProvider                            |
+----------------------+--------------------+------------+------------------------------------+------------------------------------------------------------+

