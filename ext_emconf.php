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
	'description' => 'Shibboleth login for TYPO3',
	'category' => 'services',
	'author' => 'Thomas Schikarski (Trusting Connections UG), Andreas Groth (TYPO3-Team der TU München), Irene Höppner',
	'author_email' => 'thomas.schikarski@trusting-connections.net',
	'shy' => '',
	'dependencies' => 'cms',
	'conflicts' => '',
	'priority' => '',
	'module' => '',
	'state' => 'beta',
	'internal' => '',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearCacheOnLoad' => 1,
	'lockType' => '',
	'author_company' => '',
	'version' => '3.1.5',
	'constraints' => array(
		'depends' => array(
			'typo3' => '7.6.0-8.7.99',
            'extbase' => '1.0.0-0.0.0',
            'fluid' => '1.0.0-0.0.0'
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'suggests' => array(
	),
	'autoload' => array(
		'psr-4' => array('TrustCnct\\Shibboleth\\' => 'Classes/')
	)
);

?>