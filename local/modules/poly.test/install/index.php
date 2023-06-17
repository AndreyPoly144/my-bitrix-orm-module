<?php

use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\EventManager;

Loc::loadMessages(__FILE__);

class poly_test extends CModule
{
    function __construct()
    {
        $arModuleVersion = [];
        include __DIR__ . '/version.php';

        $this->MODULE_ID = 'poly.test';
        $this->MODULE_VERSION = $arModuleVersion['VERSION'];
        $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
        $this->MODULE_NAME = Loc::getMessage('IEX_TEST_MODULE_NAME');
        $this->MODULE_DESCRIPTION = Loc::getMessage('IEX_TEST_MODULE_DESC');
        $this->PARTNER_NAME = Loc::getMessage('IEX_TEST_PARTNER_NAME');
    }

    function isVersionD7()
    {
        return CheckVersion(
            \Bitrix\Main\ModuleManager::getVersion('main'),
            '14.00.00'
        );
    }

    function DoInstall()
    {
        global $APPLICATION;
        if ($this->isVersionD7()) {
            $this->InstallEvents();
            $this->InstallFiles();

            \Bitrix\Main\ModuleManager::registerModule($this->MODULE_ID);
            $this->InstallDB();
        } else {
            $APPLICATION->ThrowException(Loc::getMessage('IEX_TEST_INSTALL_ERROR_VERSION'));
        }
    }

    function DoUninstall()
    {
        $this->UnInstallFiles();
        $this->UnInstallEvents();
        $this->UnInstallDB();
        \Bitrix\Main\ModuleManager::unRegisterModule($this->MODULE_ID);
    }


    function InstallDB()
    {
        \Bitrix\Main\Loader::includeModule($this->MODULE_ID);

        //создаем таблицу список диспетчеров
        $this->createMyTable('poly\test\DisTable');

        //заполняем таблицу пользователями(диспетчерами)
        $this->fillDis();

        //создаем таблицу список объектов
        $this->createMyTable('poly\test\ObjTable');

        //заполняем таблицу объектов (добавляю 3 тестовых объекта)
        $this->fillObj();
    }

    function UnInstallDB()
    {
        \Bitrix\Main\Loader::includeModule($this->MODULE_ID);
        //удаляем таблицу список диспетчеров
        $this->deleteMyTable('poly\test\DisTable');
        //удаляем таблицу список объектов
        $this->deleteMyTable('poly\test\ObjTable');
    }

    function createMyTable($className)
    {
        //проверяем существует ли такая таблица если нет, то создаем ее
        if (!\Bitrix\Main\Application::getConnection($className::getConnectionName())->isTableExists(
            Bitrix\Main\Entity\Base::getInstance($className)->getDBTableName()
        )
        ) {
            Bitrix\Main\Entity\Base::getInstance($className)->createDbTable(         //создание таблицы в БД, если такая таблица уже будет в БД то произойдет ошибка
                $className
            );
        }
    }

    function deleteMyTable($className)
    {
        $tableName = Bitrix\Main\Entity\Base::getInstance($className)
            ->getDBTableName();
        \Bitrix\Main\Application::getConnection(\poly\test\DisTable::getConnectionName())
            ->queryExecute("drop table if exists $tableName");
    }

    public function fillDis()
    {
        $users = self::getUsers();
        foreach ($users as $user) {
            $result = poly\test\DisTable::add([
                'ACTIVITY' => $user['ACTIVE'],
                'USER_ID' => $user['ID'],
                'OBJECT_ID' => rand(1, 3),
                'ACCESS_LEVEL' => rand(1, 12),
                'COMMENT' => 'Какой то комментарий'
            ]);

            if (!$result->isSuccess()) {
                $error = $result->getErrorMessages();
                print_r($error);
            }
        }
    }

    public function fillObj()
    {
        $i = 0;
        while ($i < 4) {
            $i++;
            $result = poly\test\ObjTable::add(array(
                'NAME' => "Имя Объекта$i",
                'ADDRESS' => "Адрес объекта$i",
                'COMMENT' => "Комментарий$i"
            ));

            if (!$result->isSuccess()) {
                $error = $result->getErrorMessages();
                print_r($error);
            }
        }
    }

    public function getUsers()
    {
        $rsUsers = CUser::GetList();
        while ($arUser = $rsUsers->Fetch()) {
            $users[] = $arUser;
        }
        return $users;
    }

    function getInstallPath($abolute = false)
    {
        if ($abolute) {
            return dirname(__FILE__);           //вернет абсолютный путь к install
        } else {
            return str_ireplace(
                \Bitrix\Main\Application::getDocumentRoot(),
                '',
                dirname(__FILE__)
            );
        }
    }

    function installFiles()
    {
        //установка компонента
        $installPath = $this->getInstallPath();
        CopyDirFiles(
            $_SERVER["DOCUMENT_ROOT"] . $installPath . "/components",
            $_SERVER["DOCUMENT_ROOT"] . "/local/components/myyy",
            true,
            true
        );
    }

    function uninstallFiles()
    {
        \Bitrix\Main\IO\Directory::deleteDirectory(
            $_SERVER['DOCUMENT_ROOT'] . '/local/components/myyy/dislist'
        );
    }

    function getEvents()
    {
        return [
            [
                'FROM_MODULE' => 'main',
                'EVENT' => 'OnAfterUserLogin',
                'TO_METHOD' => 'afterUserLogin'
            ],
            [
                'FROM_MODULE' => 'main',
                'EVENT' => 'OnBeforeUserUpdate',
                'TO_METHOD' => 'beforeUserUpdate'
            ],
            [
                'FROM_MODULE' => 'main',
                'EVENT' => 'OnAfterUserUpdate',
                'TO_METHOD' => 'afterUserUpdate'
            ],
            [
                'FROM_MODULE' => 'main',
                'EVENT' => 'OnUserDelete',
                'TO_METHOD' => 'onUserDelete'
            ],
            [
                'FROM_MODULE' => 'main',
                'EVENT' => 'OnAfterUserRegister',
                'TO_METHOD' => 'afterUserRegister'
            ],
        ];
    }

    function InstallEvents()
    {
        $classHandler = 'poly\\test\\eventHandler';
        $eventManager = EventManager::getInstance();

        $arEvents = $this->getEvents();
        foreach ($arEvents as $arEvent) {
            $eventManager->registerEventHandler(
                $arEvent['FROM_MODULE'],
                $arEvent['EVENT'],
                $this->MODULE_ID,
                $classHandler,
                $arEvent['TO_METHOD']
            );
        }
        return true;
    }

    function UnInstallEvents()
    {
        $classHandler = 'poly\\test\\eventHandler';
        $eventManager = EventManager::getInstance();

        $arEvents = $this->getEvents();
        foreach ($arEvents as $arEvent) {
            $eventManager->unregisterEventHandler(
                $arEvent['FROM_MODULE'],
                $arEvent['EVENT'],
                $this->MODULE_ID,
                $classHandler,
                $arEvent['TO_METHOD']
            );
        }
        return true;
    }
}
