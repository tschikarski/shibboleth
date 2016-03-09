.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../Includes.txt


.. _configuration-configuration-options-mapping-configuration-file:

Mapping Configuration File
^^^^^^^^^^^^^^^^^^^^^^^^^^

Under ``/typo3conf/ext/shibboleth/res/sample-config.txt`` you find a sample config file.
It makes use of the Typoscript parser to achieve maximum flexibility.
To better understand, what it does, let's examine it's structure.

::

    tx_shibboleth {
        FE {
            // definitions for frontend authentication and authorization
        }
        BE {
            // definitions for backend authentication and authorization
        }
    }

Explanations:

As you can see, there are two different sections for frontend and backend authentication and authorization.
Both work identically. However, you might need to set different fields in the respective TYPO3 user table.

Let's start with a backend example.

::

    tx_shibboleth {
        BE {
            IDMapping {
                // Map Shibboleth ID to TYPO3 username
                shibID = TEXT
                shibID.field = eppn
                typo3Field = username
            }
            userControls {
                // Control, how the user is imported and logged in
                // 'allowUser' decides, if the user is accepted by TYPO3. Set to 0 or 1.
                allowUser = TEXT
                allowUser.value = 1
                createUserFieldsMapping {
                    // Set additional fields, when the user is imported for the FIRST TIME
                    email = TEXT
                    email.field = REMOTE_USER

                    admin = TEXT
                    admin.value = 0
                }
                updateUserFieldsMapping {
                    // Update the user with these field values at EVERY LOGIN
                    admin = TEXT
                    admin.value = 0
                }
            }
        }
    }

Explanations:

Within ``IDMapping`` you have to tell TYPO3, where in the Shibboleth data it finds the username.
Set ``shibID`` to the desired value by referencing the
appropriate Shibboleth attribute (``REMOTE_USER`` in our example). In standard TYPO3 installations
``typo3Field`` must be set to ``username``. Take care to select a unique Shibboleth field.

Within ``userControls`` there are three parts.
For simplicity, in our example we use just one-to-one mappings and hard-coded values.
In real world, you might want to use the full toolset of Typoscript (e.g. ``CASE, COA, noTrimWrap`` etc.) to set values.

* ``allowUser`` represents (your) decision to accept the user for TYPO3. If this evaluates to 1, the user will be accepted.
* ``createUserFieldsMapping`` is a section that is applied only once, i.e. when a user is auto-imported for the first time.
* ``updateUserFieldsMapping`` is a section that is applied each time shibboleth authenticates an user that is already in the TYPO3 database. **It is not run for new users.**

In our example, we accept all Shibboleth users. ( ``allowUser.value = 1`` )

In both ``*UserFieldsMapping`` sections you can use basically any TYPO3 field that exists in the user table. Take care of the few differences between fe_users and be_users (e.g. ``admin`` only exists in be_users).

On auto-import, we set the email address field of the new TYPO3 user to the value transferred in ``REMOTE_USER``.
However, we do not update this field later on. This makes sense here, as the source field is also the user ID.

On each subsequent authentication the user record is updated. In our example, only the ``admin`` flag is affected by the update.
The idea here is to make sure a user is not given the "admin" privilege manually. (Again, this is a decision to be made in accordance to your requirements.)

**Important hint:** If you want a field to be set in all cases, you have to put the configuration in *both* sections, ``createUserFieldsMapping`` **and** ``updateUserFieldsMapping``.

Now to a frontend example:

::

	tx_shibboleth {
		FE {
			IDMapping {
				shibID = TEXT
				shibID.field = eppn
				typo3Field = username
			}
			userControls {
				allowUser = TEXT
				allowUser.value = 1
				createUserFieldsMapping {
					email = TEXT
					email.field = mail

					usergroup = TEXT
					usergroup.value = 1

					name = TEXT
					name.field = Shib-realname
				}
				updateUserFieldsMapping {
					email = TEXT
					email.field = mail

					name = TEXT
					name.field = Shib-realname
				}
			}
		}

**Important to note:** You have to set the ``usergroup`` field for FE users. Creating a user without a usergroup entry doesn't work, as the user will not be accepted by TYPO3.

