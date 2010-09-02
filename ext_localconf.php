<?php
if (!defined ('TYPO3_MODE')) {
 	die ('Access denied.');
}

$TYPO3_CONF_VARS['SVCONF']['auth']['setup']['FE_fetchUserIfNoSession'] = '1'; // default ****
//$TYPO3_CONF_VARS['SVCONF']['auth']['setup']['FE_alwaysFetchUser'] = '1'; // default

$subtypes = 'getUserFE,authUserFE,getUserBE,authUserBE';
$subtypes = 'getUserFE'; // ****

t3lib_extMgm::addService($_EXTKEY,  'auth' /* sv type */,  'tx_shibboleth_sv1' /* sv key */,
		array(

			'title' => 'Shibboleth Authentication',
			'description' => '',

			'subtype' => $subtypes,

			'available' => TRUE,
			'priority' => 500,
			'quality' => 500,

			'os' => '',
			'exec' => '',

			'classFile' => t3lib_extMgm::extPath($_EXTKEY).'sv1/class.tx_shibboleth_sv1.php',
			'className' => 'tx_shibboleth_sv1',
		)
	);


t3lib_extMgm::addPItoST43($_EXTKEY, 'pi1/class.tx_shibboleth_pi1.php', '_pi1', 'list_type', 1);
?>