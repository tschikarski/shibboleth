<?php
if (!defined ('TYPO3_MODE')) {
 	die ('Access denied.');
}

$GLOBALS['TYPO3_CONF_VARS']['SVCONF']['auth']['setup']['FE_fetchUserIfNoSession'] = '1';
$GLOBALS['TYPO3_CONF_VARS']['SVCONF']['auth']['setup']['BE_fetchUserIfNoSession'] = '1';


// Configuration of authentication service.
$EXT_CONFIG = $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['shibboleth'];

if ($EXT_CONFIG['enableAlwaysFetchUser']) {
	// Activate the following two lines, in case you want to give your Shibboleth-SP
	// full control over logging in and out. However, in that case you have to ensure
	// that the Shibboleth-SP is maintaining it's session during the whole user session,
	// which might be a problem, if used in connection with load balancing.
	// Additionally, this will imply a strange behaviour of the Logout button as well as
	// the BE timeout warning window.

	$GLOBALS['TYPO3_CONF_VARS']['SVCONF']['auth']['setup']['FE_alwaysFetchUser'] = '1'; // default
	$GLOBALS['TYPO3_CONF_VARS']['SVCONF']['auth']['setup']['BE_alwaysFetchUser'] = '1'; // default
}

if ($EXT_CONFIG['FE_enable']) {
	$subtypesArray[] = 'getUserFE';
	$subtypesArray[] = 'authUserFE';
}

if ($EXT_CONFIG['BE_enable']) {
	$subtypesArray[] = 'getUserBE';
	$subtypesArray[] = 'authUserBE';
}

if (is_array($subtypesArray)) {
	$subtypesArray = array_unique($subtypesArray);
	$subtypes = implode(',',$subtypesArray);
}

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService(
	$_EXTKEY,
	'auth' /* sv type */,
	\TrustCnct\Shibboleth\ShibbolethAuthentificationService::class /* sv key */,
	array(
		'title' => 'Shibboleth Authentication',
		'description' => '',
		'subtype' => $subtypes,
		'available' => TRUE,
		'priority' => 80,       // tx_svauth_sv1 has 50, t3sec_saltedpw has 70, rsaauth has 60. This service must have higher priority!
		'quality' => 80,
		'os' => '',
		'exec' => '',
		'className' => 'TrustCnct\\Shibboleth\\ShibbolethAuthentificationService',
	)
);

if ($EXT_CONFIG['BE_enable']) {
	$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['backend']['loginProviders'][1473415709] = array(
		'provider' => \TrustCnct\Shibboleth\LoginProvider\ShibbolethLoginProvider::class,
		'sorting' => 75,
		'icon-class' => 'fa-key',
		'label' => 'LLL:EXT:shibboleth/Resources/Private/Language/locallang.xlf:loginprovider.login.link'
	);
	// Modify logout button; replacing TYPO3 original
	$GLOBALS['TYPO3_CONF_VARS']['BE']['toolbarItems'][1435433111] = \TrustCnct\Shibboleth\Toolbar\UserToolbarItem::class;
}

if ($EXT_CONFIG['debugLog']) {
    $GLOBALS['TYPO3_CONF_VARS']['LOG']['TrustCnct']['Shibboleth']['writerConfiguration'] = [
        \TYPO3\CMS\Core\Log\LogLevel::DEBUG => [
            'TYPO3\\CMS\\Core\\Log\\Writer\\FileWriter' => [
            ]
        ],
    ];
}





call_user_func(
    function($extkey)
    {

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'TrustCnct.Shibboleth',
            'LoginLink',
            [
                'LoginLink' => 'show'
            ],
            // non-cacheable actions
            [
                'LoginLink' => ''
            ]
        );

        $iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class);
        $iconRegistry->registerIcon(
            'tx-shibboleth-loginlink',
            \TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider::class,
            ['source' => 'EXT:'.$extkey.'/Resources/Public/Icons/user_plugin_loginlink.png']
        );

        // wizards
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig(
            'mod {
             wizards.newContentElement.wizardItems.plugins {
                elements {
                    loginlink {
                        iconIdentifier = tx-shibboleth-loginlink
                        title = LLL:EXT:shibboleth/Resources/Private/Language/locallang_db.xlf:tx_shibboleth_domain_model_loginlink
                        description = LLL:EXT:shibboleth/Resources/Private/Language/locallang_db.xlf:tx_shibboleth_domain_model_loginlink.description
                        tt_content_defValues {
                            CType = list
                            list_type = shibboleth_loginlink
                        }
                    }
                }
                show = *
            }
       }'
        );
    },
    $_EXTKEY
);

