<?php

if (!defined ('TYPO3_MODE')) {
    die ('Access denied.');
}

$tempColumns = array (
	'tx_shibboleth_shibbolethsessionid' => array (
		'exclude' => 1,
		'label' => 'LLL:EXT:shibboleth/locallang_db.xml:fe_users.tx_shibboleth_shibbolethsessionid',
		'config' => array (
			'type' => 'none',
		)
	),
);


\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('fe_users',$tempColumns);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('fe_users','tx_shibboleth_shibbolethsessionid;;;;1-1-1');
