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

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Exception;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;

class UserHandler
{
    var $writeDevLog;
    var $tsfeDetected = FALSE;
    var $loginType=''; //FE or BE
    var $user=array();
    var $db_user=array();
    var $db_group=array();
    protected $shibboleth_extConf;
    var $mappingConfigAbsolutePath;
    var $config; // typoscript like configuration for the current loginType
    var $cObj; // local cObj, needed to parse the typoscript configuration
    var $envShibPrefix = '';
    var $shibSessionIdKey;

    /**
     * @var
     */

    /**
     * @var QueryBuilder
     */
    protected $queryBuilder;

    /**
     * @var boolean
     */
    protected $hasQueryBuilder = false;

    /**
     * UserHandler constructor.
     * @param $loginType
     * @param $db_user
     * @param $db_group
     * @param $shibSessionIdKey
     * @param bool $writeDevLog
     * @param string $envShibPrefix
     */
	function __construct($loginType, $db_user, $db_group, $shibSessionIdKey, $writeDevLog = FALSE, $envShibPrefix = '') {
		global $TYPO3_CONF_VARS;
        $this->writeDevLog = ($TYPO3_CONF_VARS['SC_OPTIONS']['shibboleth/lib/class.tx_shibboleth_userhandler.php']['writeMoreDevLog'] AND $writeDevLog);

        $this->shibboleth_extConf = unserialize($TYPO3_CONF_VARS['EXT']['extConf']['shibboleth']);

        $this->loginType = $loginType;
        $this->db_user = $db_user;
        $this->db_group = $db_group;
        $this->shibSessionIdKey = $shibSessionIdKey;
        $this->envShibPrefix = $envShibPrefix;
		$this->config = $this->getTyposcriptConfiguration();

        if (class_exists(ConnectionPool::class)) {
            $this->hasQueryBuilder = true;
        } else {
		    $this->hasQueryBuilder = false;
        }

        $serverEnvReplaced = $_SERVER;
        $pattern = '/^' . $this->envShibPrefix . '/';
        foreach ($serverEnvReplaced as $aKey => $aValue) {
            $replacedKey = preg_replace($pattern, '',$aKey);
            if (!isset($serverEnvReplaced[$replacedKey])) {
                $serverEnvReplaced[$replacedKey] = $aValue;
                unset($serverEnvReplaced[$aKey]);
            }
        }

        if (is_object($GLOBALS['TSFE'])) {
            $this->tsfeDetected = TRUE;
        }
        /** @var \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer $localcObj */
        $localcObj = GeneralUtility::makeInstance(\TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer::class);
        $localcObj->start($serverEnvReplaced);
        if (!$this->tsfeDetected) {
            unset($GLOBALS['TSFE']);
        }

        $this->cObj = $localcObj;
    }

	function lookUpShibbolethUserInDatabase() {

		$idField = $this->config['IDMapping.']['typo3Field'];
		$idValue = $this->getSingle($this->config['IDMapping.']['shibID'],$this->config['IDMapping.']['shibID.']);

        if ($idValue == '') {
            if ($this->writeDevLog)
                GeneralUtility::devLog(
                    'getUserFromDB: Shibboleth data evaluates username to empty string! Extra data may help',
                    '\TrustCnct\Shibboleth\User\UserHandler',
                    3,
                    array(
                        'idField' => $idField,
                        'idValue' => $idValue
                    )
                );
            return 'Shibboleth data evaluates username to empty string!';
        }

        $storagePid = 999999;
        if ($this->loginType == 'FE') {
            $storagePid = $this->shibboleth_extConf['FE_autoImport_pid'];
        };
        if ($this->loginType == 'BE') {
            $storagePid = 0;
        }

        if ($this->hasQueryBuilder) {
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
            $row = $statement->fetch();
        } else {
            $where = $idField . '=\'' . $idValue . '\' ';
            // Next line: Don't use "enable_clause", as it will also exclude hidden users, i.e.
            // will create new users on every login attempt until user is unhidden by admin.
            $where .= ' AND deleted = 0 ';
            $where .= ' AND pid = '.(int) $storagePid;
            //$GLOBALS['TYPO3_DB']->debugOutput = TRUE;
            $table = $this->db_user['table'];
            $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery (
                '*',
                $table,
                $where
            );
            $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);

        }

        if ($row)  {
			if ($this->writeDevLog) GeneralUtility::devlog('getUserFromDB returning user record ($row)','\TrustCnct\Shibboleth\User\UserHandler',0,$row);
			return $row;
		}
        if ($this->writeDevLog) GeneralUtility::devlog('getUserFromDB returning FALSE (no record found)','\TrustCnct\Shibboleth\User\UserHandler',0,$row);
        return false;

    }

    function transferShibbolethAttributesToUserArray($user) {
            // We will need part of the config array when writing user to DB in "synchronizeUserData"; let's put it into $user
		$user['tx_shibboleth_config'] = $this->config['userControls.'];
		$user['tx_shibboleth_shibbolethsessionid'] = $_SERVER[$this->shibSessionIdKey];

		$user['_allowUser'] = $this->getSingle($user['tx_shibboleth_config']['allowUser'],$user['tx_shibboleth_config']['allowUser.']);

		// Always create random password, as might have to fight against attempts to set a known password for the user.
		$user[$this->db_user['userident_column']] = 'shibb:' . sha1(mt_rand());

		// Force idField and idValue to be consistent with the IDMapping config, overwriting
		// any possible mis-configuration from the other fields mapping entries
		$idField = $this->config['IDMapping.']['typo3Field'];
		$idValue = $this->getSingle($this->config['IDMapping.']['shibID'],$this->config['IDMapping.']['shibID.']);

        if ($idValue == '') {
            if ($this->writeDevLog)
                GeneralUtility::devLog(
                    'transferShibbolethAttributesToUserArray: Shibboleth data evaluates username to empty string! Extra data may help',
                    '\TrustCnct\Shibboleth\User\UserHandler',
                    3,
                    array(
                        'idField' => $idField,
                        'idValue' => $idValue
                    )
                );
            return 'Shibboleth data evaluates username to empty string!';
        }

        $user[$idField] = $idValue;

		if ($this->writeDevLog) GeneralUtility::devlog('transferShibbolethAttributesToUserArray: newUserArray','\TrustCnct\Shibboleth\User\UserHandler',0,$user);
		return $user;
	}

	function synchronizeUserData(&$user) {
		if ($this->writeDevLog) GeneralUtility::devlog('synchronizeUserData','\TrustCnct\Shibboleth\User\UserHandler',0,$user);

		if($user['uid']) {
            $user = $this->updateUser($user);

        } else {
            $user = $this->insertUser($user);

        }

        if ($user === NULL) {
		    $uid = 0;
        } else {
		    $uid = $user['uid'];
        }
		if ($this->writeDevLog) GeneralUtility::devLog('synchronizeUserData: After update/insert; $uid='.$uid,'\TrustCnct\Shibboleth\User\UserHandler');
		return $uid;
	}

	function getTyposcriptConfiguration() {

		#$incFile = $GLOBALS['TSFE']->tmpl->getFileName($fName);
		#$GLOBALS['TSFE']->tmpl->fileContent($incFile);

        $this->mappingConfigAbsolutePath = $this->getEnvironmentVariable('TYPO3_DOCUMENT_ROOT') . $this->shibboleth_extConf['mappingConfigPath'];
        $configString = GeneralUtility::getURL($this->mappingConfigAbsolutePath);
		if($configString === FALSE) {
			if ($this->writeDevLog) GeneralUtility::devlog('Could not find config file, please check extension setting for correct path!','\TrustCnct\Shibboleth\User\UserHandler',3);
			return array();
		}

		$parser = GeneralUtility::makeInstance('TYPO3\CMS\Backend\Configuration\TsConfigParser');
        $parser->parse($configString);

        $completeSetup = $parser->setup;

        $localSetup = $completeSetup['tx_shibboleth.'][$this->loginType . '.'];

		return $localSetup;
	}

	function getSingle($conf,$subconf='') {
		if(is_array($subconf)) {
			if ($GLOBALS['TSFE']->cObjectDepthCounter == 0) {
				if (!is_object($GLOBALS['TSFE'])) {
					$GLOBALS['TSFE'] = new \stdClass();
				}
				$GLOBALS['TSFE']->cObjectDepthCounter = 100;
			}
			$result = $this->cObj->cObjGetSingle($conf, $subconf);
		} else {
			$result = $conf;
		}
		if (!$this->tsfeDetected) {
			unset($GLOBALS['TSFE']);
		}
		return $result;
	}

	/**
	 * Creating a single static cached instance of TSFE to use with this class.
	 *
	 * @return	tslib_fe		New instance of tslib_fe
	 */
	private static function getTSFE() {
		// Cached instance
		static $tsfe = null;

		if (is_null($tsfe)) {
			$tsfe = GeneralUtility::makeInstance('tslib_fe', $GLOBALS['TYPO3_CONF_VARS'], 0, 0);
		}

		return $tsfe;
	}

    /**
     * @param $user
     * @return mixed
     */
    private function mapAdditionalFields($user, $forInsert = False)
    {
        $configPartString = 'updateUserFieldsMapping.';
        if ($forInsert) { $configPartString = 'createUserFieldsMapping.'; }
        foreach ($user['tx_shibboleth_config'][$configPartString] as $field => $fieldConfig) {
            $newFieldValue = $this->getSingle($user['tx_shibboleth_config'][$configPartString][$field],
                $user['tx_shibboleth_config'][$configPartString][$field . '.']);
            if (substr(trim($field), -1) != '.') {
                $user[$field] = $newFieldValue;
            }
        }
        return $user;
    }

    /**
     * Wrapper function for GeneralUtility::getIndpEnv()
     *
     * @see GeneralUtility::getIndpEnv
     * @param string $key Name of the "environment variable"/"server variable" you wish to get.
     * @return string
     */
    protected function getEnvironmentVariable($key) {
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

        if ($this->writeDevLog) {
            GeneralUtility::devlog('synchronizeUserData: Updating $user with uid=' . intval($uid) . ' in DB',
                '\TrustCnct\Shibboleth\User\UserHandler', 0, $user);
        }

        if ($this->hasQueryBuilder) {
            $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
            $query = $connectionPool->getQueryBuilderForTable($this->db_user['table']);
            $query
                ->update($this->db_user['table'])
                ->where($query->expr()->eq('uid', $uid));
            foreach ($user AS $userKey => $userValue) {
                $query->set($userKey, $userValue);
            }
            try
            {
                $numAffectedRows = $query->execute();

            } catch (\Exception $e) {
                if ($this->writeDevLog) {
                    GeneralUtility::devlog('synchronizeUserData: Could not update $user in DB.', '\TrustCnct\Shibboleth\User\UserHandler', 3, $user);
                }
                return NULL;

            }
            if ($numAffectedRows != 1) {
                if ($this->writeDevLog) {
                    GeneralUtility::devlog('synchronizeUserData: Could not insert $user into DB.', '\TrustCnct\Shibboleth\User\UserHandler', 3, $user);
                }
                return NULL;
            }

            $user['uid'] = $uid;
            return $user;
        }
        // Update
        $table = $this->db_user['table'];
        $where = 'uid=' . intval($uid);
        #$where=$GLOBALS['TYPO3_DB']->fullQuoteStr($inputString,$table);
        $fields_values = $user;
        $GLOBALS['TYPO3_DB']->exec_UPDATEquery(
            $table,
            $where,
            $fields_values
        );
        $sql_errno = $GLOBALS['TYPO3_DB']->sql_errno();
        if ($sql_errno) {
            if ($this->writeDevLog) {
                GeneralUtility::devlog('synchronizeUserData: DB Error No. = ' . $sql_errno, '\TrustCnct\Shibboleth\User\UserHandler');
                if ($sql_errno > 0) {
                    GeneralUtility::devLog('synchronizeUserData: DB Error Msg: ' . $GLOBALS['TYPO3_DB']->sql_error(),
                        '\TrustCnct\Shibboleth\User\UserHandler');
                }
            }
            return NULL;
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
        $user = $this->mapAdditionalFields($user, True);
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
        if ($this->hasQueryBuilder) {
            $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
            $query = $connectionPool->getQueryBuilderForTable($this->db_user['table']);
            $query->insert($this->db_user['table'])->values($user);
            if ($this->writeDevLog) {
                GeneralUtility::devlog('synchronizeUserData: Inserting $user into DB table ' . $this->db_user['table'],
                    '\TrustCnct\Shibboleth\User\UserHandler', 0, $user);
            }
            try
            {
                $numAffectedRows = $query->execute();

            } catch (\Exception $e) {
                if ($this->writeDevLog) {
                    GeneralUtility::devlog('synchronizeUserData: Could not insert $user into DB.', '\TrustCnct\Shibboleth\User\UserHandler', 3, $user);
                }
                return NULL;

            }

            if ($numAffectedRows != 1) {
                if ($this->writeDevLog) {
                    GeneralUtility::devlog('synchronizeUserData: Could not insert $user into DB.', '\TrustCnct\Shibboleth\User\UserHandler', 3, $user);
                }
                return NULL;
            }
            $user = $this->lookUpShibbolethUserInDatabase();
            if ($this->writeDevLog) {
                GeneralUtility::devLog('synchronizeUserData: Got new uid ' . $user['uid'], '\TrustCnct\Shibboleth\User\UserHandler');
            }
            return $user;

        }
        // Use old style database access
        $table = $this->db_user['table'];
        $insertFields = $user;
        if ($this->writeDevLog) {
            GeneralUtility::devlog('synchronizeUserData: Inserting $user into DB table ' . $table,
                '\TrustCnct\Shibboleth\User\UserHandler', 0, $user);
        }
        $GLOBALS['TYPO3_DB']->exec_INSERTquery(
            $table,
            $insertFields
        );
        $sql_errno = $GLOBALS['TYPO3_DB']->sql_errno();
        if ($sql_errno) {
            if ($this->writeDevLog) {
                GeneralUtility::devlog('synchronizeUserData: DB Error No. = ' . $sql_errno, '\TrustCnct\Shibboleth\User\UserHandler');
                if ($sql_errno > 0) {
                    GeneralUtility::devLog('synchronizeUserData: DB Error Msg: ' . $GLOBALS['TYPO3_DB']->sql_error(),
                        '\TrustCnct\Shibboleth\User\UserHandler');
                }
            }
            return NULL;
        }
        $uid = $GLOBALS['TYPO3_DB']->sql_insert_id();
        $user['uid'] = $uid;
        if ($this->writeDevLog) {
            GeneralUtility::devLog('synchronizeUserData: Got new uid ' . $uid, '\TrustCnct\Shibboleth\User\UserHandler');
        }
        return $user;
    }

}
