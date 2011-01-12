<?php
if (!defined ('TYPO3_MODE')) {
 	die ('Access denied.');
}

	// TODO: Why did we comment "default" to the following lines?
$TYPO3_CONF_VARS['SVCONF']['auth']['setup']['FE_fetchUserIfNoSession'] = '1'; // default 
$TYPO3_CONF_VARS['SVCONF']['auth']['setup']['BE_fetchUserIfNoSession'] = '1'; // default 
# Don't re-check after user is logged in!
# This would forbid to log off. Additionally, it would make necessary that the 
# Shibboleth SP daemon will keep login information all over your session, which
# is problematic, especially in load balancing situations.
#$TYPO3_CONF_VARS['SVCONF']['auth']['setup']['FE_alwaysFetchUser'] = '1'; // default
#$TYPO3_CONF_VARS['SVCONF']['auth']['setup']['BE_alwaysFetchUser'] = '1'; // default

	// TODO: Test for BE login (Auto/Non-Auto)
$subtypes = 'getUserFE,authUserFE,getUserBE,authUserBE';
#$subtypes = 'getUserFE,authUserFE';

t3lib_extMgm::addService($_EXTKEY,  'auth' /* sv type */,  'tx_shibboleth_sv1' /* sv key */,
		array(

			'title' => 'Shibboleth Authentication',
			'description' => '',

			'subtype' => $subtypes,

			'available' => TRUE,
			'priority' => 50,
			'quality' => 50,

			'os' => '',
			'exec' => '',

			'classFile' => t3lib_extMgm::extPath($_EXTKEY).'sv1/class.tx_shibboleth_sv1.php',
			'className' => 'tx_shibboleth_sv1',
		)
	);

// Hook for the link in the backendform
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['shibboleth']['originalLoginScriptHook'] = $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['typo3/index.php']['loginScriptHook']['sv'];
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['typo3/index.php']['loginScriptHook']['sv'] = 'EXT:' . $_EXTKEY . '/hooks/class.tx_shibboleth_beform.php:tx_shibboleth_beform->addShibbolethJavaScript';

$TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_userauth.php']['writeDevLog'] = TRUE;
$TYPO3_CONF_VARS['SC_OPTIONS']['shibboleth/lib/class.tx_shibboleth_userhandler.php']['writeDevLog'] = TRUE;

t3lib_extMgm::addPItoST43($_EXTKEY, 'pi1/class.tx_shibboleth_pi1.php', '_pi1', 'list_type', 1);
?>