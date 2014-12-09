<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "shibboleth".
 *
 * Auto generated 09-12-2014 09:57
 *
 * Manual updates:
 * Only the data in the array - everything else is removed by next
 * writing. "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Shibboleth Authentication and SSO',
	'description' => '',
	'category' => 'services',
	'author' => 'Thomas Schikarski, Irene Höppner',
	'author_email' => 'thomas.schikarski@trusting-connections.net',
	'shy' => '',
	'dependencies' => 'cms',
	'conflicts' => '',
	'priority' => '',
	'module' => '',
	'state' => 'stable',
	'internal' => '',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearCacheOnLoad' => 1,
	'lockType' => '',
	'author_company' => '',
	'version' => '1.0.0',
	'constraints' => array(
		'depends' => array(
			'typo3' => '6.2.0-6.2.99',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => 'a:22:{s:9:"ChangeLog";s:4:"322d";s:21:"ext_conf_template.txt";s:4:"df4e";s:12:"ext_icon.gif";s:4:"538d";s:17:"ext_localconf.php";s:4:"ba6e";s:14:"ext_tables.php";s:4:"699b";s:14:"ext_tables.sql";s:4:"569b";s:16:"locallang_db.xml";s:4:"8bfa";s:10:"README.txt";s:4:"ee2d";s:14:"doc/manual.sxw";s:4:"8651";s:19:"doc/wizard_form.dat";s:4:"4f8f";s:20:"doc/wizard_form.html";s:4:"762b";s:21:"doc/screenshots/1.png";s:4:"963a";s:21:"doc/screenshots/2.png";s:4:"d345";s:36:"hooks/class.tx_shibboleth_beform.php";s:4:"5380";s:39:"lib/class.tx_shibboleth_userhandler.php";s:4:"14e6";s:31:"pi1/class.tx_shibboleth_pi1.php";s:4:"9a94";s:17:"pi1/locallang.xml";s:4:"3c3c";s:14:"res/_.htaccess";s:4:"f76c";s:14:"res/config.txt";s:4:"a2c8";s:18:"res/shibb.htaccess";s:4:"fd15";s:19:"res/shibboleth2.xml";s:4:"799a";s:31:"sv1/class.tx_shibboleth_sv1.php";s:4:"2f21";}',
	'suggests' => array(
	),
);

?>