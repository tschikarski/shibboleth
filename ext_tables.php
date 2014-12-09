<?php

if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}

$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi1']='layout,select_key';


\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPlugin(array(
	'LLL:EXT:shibboleth/locallang_db.xml:tt_content.list_type_pi1',
	$_EXTKEY . '_pi1',
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath($_EXTKEY) . 'ext_icon.gif'
),'list_type');

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

$tempColumns = array (
	'tx_shibboleth_shibbolethsessionid' => array (		
		'exclude' => 1,		
		'label' => 'LLL:EXT:shibboleth/locallang_db.xml:be_users.tx_shibboleth_shibbolethsessionid',		
		'config' => array (
			'type' => 'none',
		)
	),
);


\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('be_users',$tempColumns);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('be_users','tx_shibboleth_shibbolethsessionid;;;;1-1-1');
?>