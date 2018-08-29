<?php
namespace sorokinmedia\asset_manager;

/**
 * Class AssetVersion
 * @package sorokinmedia\asset_manager
 *
 * класс для работы с файлом assetVersion где хранится цифра, которая апается после каждого билда фронта
 */
class AssetVersion
{
    const ASSET_VERSION = 'assetVersion';

    /**
     * Тут лежит файл хранящий версию ассетов
     * Не в базе так как используется в конфигах, когда подключение к базе еще не установлено
     * @return string
     */
    public static function assetConfigPath()
    {
        return \Yii::getAlias('@config') . DIRECTORY_SEPARATOR . self::ASSET_VERSION;
    }

    /**
     * Получить версию ассета для хэша
     * @return mixed
     */
    public static function assetVersion()
    {
        return file_get_contents(self::assetConfigPath());
    }
}