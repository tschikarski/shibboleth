<?php
/**
 * Created by PhpStorm.
 * User: tschikarski
 * Date: 07.07.17
 * Time: 15:08
 */

if (!defined ('TYPO3_MODE')) {
    die ('Access denied.');
}

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile('shibboleth', 'Configuration/TypoScript', 'Shibboleth');
