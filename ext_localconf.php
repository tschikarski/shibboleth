<?php
if (!defined ('TYPO3_MODE')) {
 	die ('Access denied.');
}

t3lib_extMgm::addService($_EXTKEY,  'auth' /* sv type */,  'tx_shibboleth_sv1' /* sv key */,
		array(

			'title' => 'Shibboleth Authentication',
			'description' => '',

			'subtype' => 'getUserFE,authUserFE,getUserBE,authUserBE',

			'available' => TRUE,
			'priority' => 50,
			'quality' => 50,

			'os' => '',
			'exec' => 'Shibboleth 2.2 Apache Module mod_shib_22',

			'classFile' => t3lib_extMgm::extPath($_EXTKEY).'sv1/class.tx_shibboleth_sv1.php',
			'className' => 'tx_shibboleth_sv1',
		)
	);


t3lib_extMgm::addPItoST43($_EXTKEY, 'pi1/class.tx_shibboleth_pi1.php', '_pi1', 'list_type', 1);
?>