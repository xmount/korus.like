<?php
defined('B_PROLOG_INCLUDED') || die;

use Korus\Like\Entity\UserTable;
use Korus\Like\Entity\ThankTable;
use Korus\Like\Entity\DepartmentTable;
use Korus\Like\Entity\UserDepartmentTable;
use Bitrix\Main\Application;
use Bitrix\Main\Config\Option;
use Bitrix\Main\EventManager;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;

class korus_like extends CModule
{
    const MODULE_ID = 'korus.like';
    var $MODULE_ID = self::MODULE_ID;
    var $MODULE_VERSION;
    var $MODULE_VERSION_DATE;
    var $MODULE_NAME;
    var $MODULE_DESCRIPTION;
    var $MODULE_CSS;
    var $strError = '';

    function __construct()
    {
        $arModuleVersion = array();
        include(dirname(__FILE__) . '/version.php');
        $this->MODULE_VERSION = $arModuleVersion['VERSION'];
        $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];

        $this->MODULE_NAME = Loc::getMessage('KORUS_LIKE.MODULE_NAME');
        $this->MODULE_DESCRIPTION = Loc::getMessage('KORUS_LIKE.MODULE_DESC');

        $this->PARTNER_NAME = Loc::getMessage('KORUS_LIKE.PARTNER_NAME');
        $this->PARTNER_URI = Loc::getMessage('KORUS_LIKE.PARTNER_URI');
    }

    function DoInstall()
    {
        ModuleManager::registerModule(self::MODULE_ID);

        $this->InstallDB();
        $this->InstallFiles();
        $this->InstallEvents();
    }

    function DoUninstall()
    {
        $this->UnInstallEvents();
        $this->UnInstallFiles();
        $this->UnInstallDB();

        ModuleManager::unRegisterModule(self::MODULE_ID);
    }

    function InstallDB()
    {
        Loader::includeModule('korus.like');

        $db = Application::getConnection();

        $userEntity = UserTable::getEntity();
        if (!$db->isTableExists($userEntity->getDBTableName())) {
            $userEntity->createDbTable();
        }

        $thankEntity = ThankTable::getEntity();
        if (!$db->isTableExists($thankEntity->getDBTableName())) {
            $thankEntity->createDbTable();
        }

        $departmentEntity = DepartmentTable::getEntity();
        if (!$db->isTableExists($departmentEntity->getDBTableName())) {
            $departmentEntity->createDbTable();
        }

        $user_departmentEntity = UserDepartmentTable::getEntity();
        if (!$db->isTableExists($user_departmentEntity->getDBTableName())) {
            $user_departmentEntity->createDbTable();
        }
    }

    function UnInstallDB()
    {
        $db = Application::getConnection();
        if ($db->isTableExists('korus_like_user')) {
            $db->dropTable('korus_like_user');
        }
        if ($db->isTableExists('korus_like_thank')) {
            $db->dropTable('korus_like_thank');
        }
        if ($db->isTableExists('korus_like_department')) {
            $db->dropTable('korus_like_department');
        }
        if ($db->isTableExists('korus_like_user_department')) {
            $db->dropTable('korus_like_user_department');
        }
    }

    function InstallEvents()
    {

    }

    function UnInstallEvents()
    {

    }

    function InstallFiles()
    {
        $documentRoot = Application::getDocumentRoot();

        CopyDirFiles(
            __DIR__ . '/components',
            $documentRoot . '/local/components',
            true,
            true
        );

    }

    function UnInstallFiles()
    {
        DeleteDirFilesEx('/local/components/korus.like');

    }
}