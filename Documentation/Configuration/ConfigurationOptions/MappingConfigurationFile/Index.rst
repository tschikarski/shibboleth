.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../../Includes.txt


.. _configuration-configuration-options-mapping-configuration-file:

Mapping Configuration File
^^^^^^^^^^^^^^^^^^^^^^^^^^

Under /typo3conf/ext/shibboleth/res/config.txt you find a sample config file with a content like
this:

::

    tx_shibboleth {
        FE {
            IDMapping {
                shibID = TEXT
                shibID.field = REMOTE_USER
                typo3Field = username
            }
            userControls {
                allowUser = TEXT
                allowUser.value = 1
                createUserFieldsMapping {
                    email = TEXT
                    email.field = REMOTE_USER
                }
                updateUserFieldsMapping {
                    email = TEXT
                    email.field = REMOTE_USER
                }
            }
        }
        BE {
            IDMapping {
                shibID = TEXT
                shibID.field = REMOTE_USER
                typo3Field = username
            }
            userControls {
                allowUser = TEXT
                allowUser.value = 1
                createUserFieldsMapping {
                    email = TEXT
                    email.field = REMOTE_USER
                }
                updateUserFieldsMapping {
                    admin = TEXT
                    admin.value = 0
                }
            }
        }
    }

Explanations:

All options are set inside “tx_shibboleth”. Configuration for front-end and back-end
authentication is separate (elements “FE” and “BE”). For further discussion, let's
concentrate on the BE.

There, you see at first an element called “IDMapping”. Here, from what Shibboleth attribute(s)
you will form the TYPO3 username. Set “shibID” to the desired value by referencing the
appropriate Shibboleth attribute (REMOTE_USER in our example). In standard TYPO3 installations
“typo3Field” must be set to “username”.

“userControls” contains three different elements: allowUser, createUserFieldsMapping, and
updateUserFieldsMapping.

“allowUser” is used to decide, if the user will be given access to TYPO3 at all. If
“allowUser” evaluates to 0, the user would be rejected. In our example, it is set to 1
statically, but in other scenarios it might be useful to use “CASE” on some Shibboleth attribute
to decide on the authorization of the user. This attributes could indicate the user's membership of
some user group.

Definitions inside “createUserFieldsMapping” will map Shibboleth attributes to other properties
of a **newly generated user** . In our example at auto-import of a user the email field is set to
the value of REMOTE_USER. These settings do not apply for already existing users.

Definitions inside “updateUserFieldsMapping” are used when an already existing user is
re-authenticated. If you want to override any manual changes to the user record at each login, you
can do this here. In our example, we will remove the Admin priviledge from every BE user that is
authenticated by Shibboleth. In the FE example, we will force the email field to the value
transferred from Shibboleth at each login.
