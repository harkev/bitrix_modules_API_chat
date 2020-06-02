<?php


class chat_send extends CModule
{
    public $MODULE_ID = 'chat.send';

    public $MODULE_VERSION = '1.0.0';

    public $MODULE_VERSION_DATE = '2020-05-27 16:00:00';

    public $MODULE_NAME = 'Chat';

    public $MODULE_DESCRIPTION = 'Модуль для отправки сообщения пользователям по миру ID';

    public function __construct()
    {
        // Это тут чтобы можно было разместить модуль в Маркетплейс Битрикс
        $this->PARTNER_NAME = 'harkev';
        $this->PARTNER_URI = 'https://harkev.ru';
    }

    // Установка модуля
    public function doInstall()
    {
        // Регистрация модуля в системе
        \Bitrix\Main\ModuleManager::registerModule($this->MODULE_ID);

        // Регистрация обработчика события "OnRestServiceBuildDescription" модуля "rest"
        \Bitrix\Main\EventManager::getInstance()->registerEventHandler(
            'rest',
            'OnRestServiceBuildDescription',
            'chat.send',
            '\chat\send\Rest\Service',
            'getDescription'
        );

        // Регистрация обработчика события "onFindMethodDescription" модуля "rest"
        \Bitrix\Main\EventManager::getInstance()->registerEventHandler(
            'rest',
            'onFindMethodDescription',
            'chat.send',
            '\chat\send\Rest\Service',
            'findMethodDescription'
        );
    }

    // Деинсталяция модуля
    public function doUninstall()
    {
        // Отмена регистрации обработчика события "onFindMethodDescription" модуля "rest"
        \Bitrix\Main\EventManager::getInstance()->unRegisterEventHandler(
            'rest',
            'onFindMethodDescription',
            'chat.send',
            '\chat\send\Rest\Service',
            'findMethodDescription'
        );

        // Отмена регистрации обработчика события "OnRestServiceBuildDescription" модуля "rest"
        \Bitrix\Main\EventManager::getInstance()->unRegisterEventHandler(
            'rest',
            'OnRestServiceBuildDescription',
            'chat.send',
            '\chat\send\Rest\Service',
            'getDescription'
        );
        // Отмена регистрации модуля в системе
        \Bitrix\Main\ModuleManager::unRegisterModule($this->MODULE_ID);
    }

    // Чтобы была возможность включить у модуля демо период
    public function InstallDB()
    {

    }
}