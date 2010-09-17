<?php
if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}

t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi1']='layout,select_key';


t3lib_extMgm::addPlugin(array(
	'LLL:EXT:shibboleth/locallang_db.xml:tt_content.list_type_pi1',
	$_EXTKEY . '_pi1',
	t3lib_extMgm::extRelPath($_EXTKEY) . 'ext_icon.gif'
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


t3lib_div::loadTCA('fe_users');
t3lib_extMgm::addTCAcolumns('fe_users',$tempColumns,1);
t3lib_extMgm::addToAllTCAtypes('fe_users','tx_shibboleth_shibbolethsessionid;;;;1-1-1');

$tempColumns = array (
	'tx_shibboleth_shibbolethsessionid' => array (		
		'config' => array (
			'type' => 'passthrough',
		)
	),
);


t3lib_div::loadTCA('be_groups');
t3lib_extMgm::addTCAcolumns('be_groups',$tempColumns,1);
t3lib_extMgm::addToAllTCAtypes('be_groups','tx_shibboleth_shibbolethsessionid;;;;1-1-1');
?>