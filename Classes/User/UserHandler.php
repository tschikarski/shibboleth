<?php

namespace TrustCnct\Shibboleth\User;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Configuration\SiteConfiguration;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Localization\Locales;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\TypoScript\Parser\TypoScriptParser;
use TYPO3\CMS\Core\TypoScript\TemplateService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

class UserHandler implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var bool
     */
    protected $tsfeDetected = false;

    /**
     * @var string
     */
    protected $loginType = ''; //FE or BE

    /**
     * @var array
     */
    protected $user = [];

    /**
     * @var array
     */
    protected $db_user = [];

    /**
     * @var array
     */
    protected $db_group = [];

    /**
     * @var array
     */
    protected $shibboleth_extConf;

    /**
     * @var string
     */
    protected $mappingConfigAbsolutePath;

    /**
     * @var array|mixed
     */
    protected $config; // typoscript like configuration for the current loginType

    /**
     * @var \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer
     */
    protected $cObj; // local cObj, needed to parse the typoscript configuration

    /**
     * @var string
     */
    protected $envShibPrefix = '';

    /**
     * @var string
     */
    protected $shibSessionIdKey;

    /**
     * @var QueryBuilder
     */
    protected $queryBuilder;

    /**
     * UserHandler constructor.
     * @param $loginType
     * @param $db_user
     * @param $db_group
     * @param $shibSessionIdKey
     * @param string $envShibPrefix
     */
    public function __construct($loginType, $db_user, $db_group, $shibSessionIdKey, $envShibPrefix = '')
    {
        $this->logger = GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__);
        $this->shibboleth_extConf = GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('shibboleth');
        $this->loginType = $loginType;
        $this->db_user = $db_user;
        $this->db_group = $db_group;
        $this->shibSessionIdKey = $shibSessionIdKey;
        $this->envShibPrefix = $envShibPrefix;
        $this->config = $this->getTyposcriptConfiguration();

        $serverEnvReplaced = $_SERVER;
        $pattern = '/^' . $this->envShibPrefix . '/';
        foreach ($serverEnvReplaced as $aKey => $aValue) {
            $replacedKey = preg_replace($pattern, '', $aKey);
            if (!isset($serverEnvReplaced[$replacedKey])) {
                $serverEnvReplaced[$replacedKey] = $aValue;
                unset($serverEnvReplaced[$aKey]);
            }
        }

        if (is_object($GLOBALS['TSFE'])) {
            $this->tsfeDetected = true;
        }

        $this->initializeTypoScriptFrontend((int)$this->shibboleth_extConf['pageUidForTSFE']);

        /** @var \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer $localcObj */
        $localcObj = GeneralUtility::makeInstance(\TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer::class);
        $localcObj->start($serverEnvReplaced);
        if (!$this->tsfeDetected) {
            unset($GLOBALS['TSFE']);
        }
        $this->cObj = $localcObj;
    }

    /**
     * @param int $pageId
     */
    private function initializeTypoScriptFrontend($pageId, $languageId = 0)
    {
        $context = GeneralUtility::makeInstance(Context::class);
        $config = GeneralUtility::makeInstance(SiteConfiguration::class, '');
        $siteFinder = GeneralUtility::makeInstance(SiteFinder::class, $config);
        $site = $siteFinder->getSiteByPageId($pageId);
        $siteLanguage = $site->getLanguageById($languageId);

        /** @var ServerRequest $request */
        $request = GeneralUtility::makeInstance(ServerRequest::class);
        $request = $request->withAttribute('site', $site);
        $GLOBALS['TYPO3_REQUEST'] = $request->withAttribute('language', $siteLanguage);

        $feUser = GeneralUtility::makeInstance(FrontendUserAuthentication::class);
        GeneralUtility::setIndpEnv('TYPO3_REQUEST_URL', (string)$site->getBase());
        $GLOBALS['TSFE'] = GeneralUtility::makeInstance(TypoScriptFrontendController::class, $context, $site, $siteLanguage, null, $feUser);
        $GLOBALS['TSFE']->id = $pageId;
        $GLOBALS['TSFE']->type = 0;

        $GLOBALS['TSFE']->sys_page = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Domain\Repository\PageRepository::class);
        $GLOBALS['TSFE']->getPageAndRootlineWithDomain($pageId, $GLOBALS['TYPO3_REQUEST']);

        $template = GeneralUtility::makeInstance(TemplateService::class, $context);
        $GLOBALS['TSFE']->tmpl = $template;
        $GLOBALS['TSFE']->forceTemplateParsing = true;
        $GLOBALS['TSFE']->no_cache = true;
        $GLOBALS['TSFE']->tmpl->start($GLOBALS['TSFE']->rootLine);
        $GLOBALS['TSFE']->no_cache = false;
        $GLOBALS['TSFE']->getConfigArray();
        $GLOBALS['TSFE']->settingLanguage();
        $GLOBALS['TSFE']->newCObj();
        $GLOBALS['TSFE']->calculateLinkVars([]);
        Locales::setSystemLocaleFromSiteLanguage($siteLanguage);
    }

    function lookUpShibbolethUserInDatabase()
    {
        $idField = $this->config['IDMapping.']['typo3Field'];
        $idValue = $this->getSingle($this->config['IDMapping.']['shibID'], $this->config['IDMapping.']['shibID.']);

        if ($idValue == '') {
            $this->logger->debug(
                'getUserFromDB: Shibboleth data evaluates username to empty string! Extra data may help',
                array(
                    'idField' => $idField,
                    'idValue' => $idValue
                )
            );
            return 'Shibboleth data evaluates username to empty string!';
        }

        $storagePid = 999999;
        if ($this->loginType === 'FE') {
            $storagePid = $this->shibboleth_extConf['FE_autoImport_pid'];
        };
        if ($this->loginType === 'BE') {
            $storagePid = 0;
        }

        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
        /** @var \TYPO3\CMS\Core\Database\Query\QueryBuilder $query */
        $query = $connectionPool->getQueryBuilderForTable($this->db_user['table']);
        $query->getRestrictions()->removeAll()->add(GeneralUtility::makeInstance(DeletedRestriction::class));
        $query
            ->select('*')
            ->from($this->db_user['table'])
            ->where(
                $query->expr()->eq($idField, $query->createNamedParameter($idValue))
            )
            ->andWhere(
                $query->expr()->eq('pid', $query->createNamedParameter($storagePid))
            );
        $statement = $query->execute();
        $row = $statement->fetchAssociative();

        if ($row) {
            $this->logger->debug('getUserFromDB returning user record ($row)', [$row]);
            return $row;
        }
        $this->logger->debug('getUserFromDB returning FALSE (no record found)', [$row]);
        return false;

    }

    function transferShibbolethAttributesToUserArray($user)
    {
        // We will need part of the config array when writing user to DB in "synchronizeUserData"; let's put it into $user
        $user['tx_shibboleth_config'] = $this->config['userControls.'];
        $user['tx_shibboleth_shibbolethsessionid'] = $_SERVER[$this->shibSessionIdKey];

        $user['_allowUser'] = $this->getSingle($user['tx_shibboleth_config']['allowUser'],
            $user['tx_shibboleth_config']['allowUser.']);

        // Always create random password, as might have to fight against attempts to set a known password for the user.
        $user[$this->db_user['userident_column']] = 'shibb:' . sha1(mt_rand());

        // Force idField and idValue to be consistent with the IDMapping config, overwriting
        // any possible mis-configuration from the other fields mapping entries
        $idField = $this->config['IDMapping.']['typo3Field'];
        $idValue = $this->getSingle($this->config['IDMapping.']['shibID'], $this->config['IDMapping.']['shibID.']);

        if ($idValue == '') {
            $this->logger->debug(
                'transferShibbolethAttributesToUserArray: Shibboleth data evaluates username to empty string! Extra data may help',
                array(
                    'idField' => $idField,
                    'idValue' => $idValue
                )
            );
            return 'Shibboleth data evaluates username to empty string!';
        }

        $user[$idField] = $idValue;

        $this->logger->debug('transferShibbolethAttributesToUserArray: newUserArray', [$user]);
        return $user;
    }

    function synchronizeUserData(&$user)
    {
        $this->logger->debug('synchronizeUserData', [$user]);

        if ($user['uid']) {
            $user = $this->updateUser($user);

        } else {
            $user = $this->insertUser($user);
        }

        if ($user === null) {
            $uid = 0;
        } else {
            $uid = $user['uid'];
        }
        $this->logger->debug('synchronizeUserData: After update/insert; $uid=' . $uid);
        return $uid;
    }

    function getTyposcriptConfiguration()
    {
        $this->mappingConfigAbsolutePath = $this->getEnvironmentVariable('TYPO3_DOCUMENT_ROOT') . $this->shibboleth_extConf['mappingConfigPath'];
        $configString = GeneralUtility::getURL($this->mappingConfigAbsolutePath);
        if ($configString === false) {
            $this->logger->debug('Could not find config file, please check extension setting for correct path!');
            return [];
        }

        $parser = GeneralUtility::makeInstance(TypoScriptParser::class);
        $parser->parse($configString);
        $completeSetup = $parser->setup;
        return $completeSetup['tx_shibboleth.'][$this->loginType . '.'];
    }

    function getSingle($conf, $subConf)
    {
        if (is_array($subConf)) {
            if ($GLOBALS['TSFE']->cObjectDepthCounter == 0) {
                if (!is_object($GLOBALS['TSFE'])) {
                    $GLOBALS['TSFE'] = new \stdClass();
                }
                $GLOBALS['TSFE']->cObjectDepthCounter = 100;
            }

            $result = $this->cObj->cObjGetSingle($conf, $subConf);
        } else {
            $result = $conf;
        }
        if (!$this->tsfeDetected) {
            unset($GLOBALS['TSFE']);
        }
        return $result;
    }

    /**
     * @param $user
     * @return mixed
     */
    private function mapAdditionalFields($user, $forInsert = false)
    {
        $configPartString = 'updateUserFieldsMapping.';
        if ($forInsert) {
            $configPartString = 'createUserFieldsMapping.';
        }
        foreach ($user['tx_shibboleth_config'][$configPartString] as $field => $fieldConfig) {
            $newFieldValue = $this->getSingle($user['tx_shibboleth_config'][$configPartString][$field],
                $user['tx_shibboleth_config'][$configPartString][$field . '.']);
            if (substr(trim($field), -1) !== '.') {
                $user[$field] = $newFieldValue;
            }
        }
        return $user;
    }

    /**
     * Wrapper function for GeneralUtility::getIndpEnv()
     *
     * @param string $key Name of the "environment variable"/"server variable" you wish to get.
     * @return string
     * @see GeneralUtility::getIndpEnv
     */
    protected function getEnvironmentVariable($key)
    {
        return GeneralUtility::getIndpEnv($key);
    }

    /**
     * @param $user
     * @return array
     */
    private function updateUser($user)
    {
// User is in DB, so we have to update, therefore remove uid from DB record and save it for later
        $uid = $user['uid'];
        unset($user['uid']);
        // We have to update the tstamp field, in any case.
        $user['tstamp'] = time();

        $user = $this->mapAdditionalFields($user);

        // Remove that data from $user - otherwise we get an error updating the user record in DB
        unset($user['tx_shibboleth_config']);

        $this->logger->debug('synchronizeUserData: Updating $user with uid=' . intval($uid) . ' in DB', [$user]);

        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
        $query = $connectionPool->getQueryBuilderForTable($this->db_user['table']);
        $query
            ->update($this->db_user['table'])
            ->where($query->expr()->eq('uid', $uid));
        foreach ($user as $userKey => $userValue) {
            $query->set($userKey, $userValue);
        }
        try {
            $numAffectedRows = $query->execute();
        } catch (\Exception $e) {
            $this->logger->debug('synchronizeUserData: Could not update $user in DB.', [$user]);
            $this->logger->debug('synchronizeUserData: Could not update $user in DB. ExceptionMessage',
                [$e->getMessage()]);
            return null;
        }
        if ($numAffectedRows != 1) {
            $this->logger->debug('synchronizeUserData: Could not insert $user into DB.', [$user]);
            return null;
        }
        $user['uid'] = $uid;
        return $user;
    }

    /**
     * @param $user
     * @return array
     */
    private function insertUser($user)
    {
        // We will insert a new user
        // We have to set crdate and tstamp correctly
        $user['crdate'] = time();
        $user['tstamp'] = time();
        $user = $this->mapAdditionalFields($user, true);
        // Remove that data from $user - otherwise we get an error inserting the user record into DB
        unset($user['tx_shibboleth_config']);
        // Determine correct pid for new user
        if ($this->loginType == 'FE') {
            $user['pid'] = intval($this->shibboleth_extConf['FE_autoImport_pid']);
        } else {
            $user['pid'] = 0;
        }
        // In BE Autoimport might be done with disable=1, i.e. BE User has to be enabled manually after first login attempt.
        if ($this->loginType == 'BE' && $this->shibboleth_extConf['BE_autoImportDisableUser']) {
            $user['disable'] = 1;
        }
        // Insert
        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
        $query = $connectionPool->getQueryBuilderForTable($this->db_user['table']);
        $query->insert($this->db_user['table'])->values($user);
        $this->logger->debug('synchronizeUserData: Inserting $user into DB table ' . $this->db_user['table'], [$user]);
        try {
            $numAffectedRows = $query->execute();
        } catch (\Exception $e) {
            $this->logger->debug('synchronizeUserData: Could not insert $user into DB.', [$user]);
            return null;
        }

        if ($numAffectedRows != 1) {
            $this->logger->debug('synchronizeUserData: Could not insert $user into DB.', [$user]);
            return null;
        }
        $user = $this->lookUpShibbolethUserInDatabase();
        $this->logger->debug('synchronizeUserData: Got new uid ' . $user['uid']);
        return $user;
    }

}
