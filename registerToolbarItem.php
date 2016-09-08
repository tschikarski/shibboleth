<?php
if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}

// Configuration of authentication service.
$EXT_CONFIG = unserialize($TYPO3_CONF_VARS['EXT']['extConf']['shibboleth']);

if ((TYPO3_MODE === 'BE') and ($EXT_CONFIG['BE_enable'])) {
	// Now register the class as toolbar item
	//$GLOBALS['TYPO3backend']->addToolbarItem('shibboleth', 'TrustCnct\Shibboleth\tx_shibboleth_toolbar');
}
