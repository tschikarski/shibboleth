<?php
$extpath = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('shibboleth');
return array(
	'TrustCnct\\Shibboleth\\tx_shibboleth_sv1' => $extpath . 'sv1/class.tx_shibboleth_sv1.php',
	'TrustCnct\\Shibboleth\\tx_shibboleth_userhandler' => $extpath . 'lib/class.tx_shibboleth_sv1.php',
	'tx_shibboleth_toolbar' => $extpath . 'hooks/class.tx_shibboleth_toolbar.php',
);
