<?php
die('RUN INTO ext_autoload.php, which is not up to date!');
$extpath = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('shibboleth');
return array(
	'TrustCnct\\Shibboleth\\tx_shibboleth_sv1' => $extpath . 'sv1/class.tx_shibboleth_sv1.php',
    'TrustCnct\\Shibboleth\\User\\UserHandler' => $extpath . 'Classes/User/UserHandler.php',
	'tx_shibboleth_toolbar' => $extpath . 'hooks/class.tx_shibboleth_toolbar.php',
);
