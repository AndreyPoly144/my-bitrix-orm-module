<?php

namespace poly\test;

class eventHandler
{
    public static array $oldValues;         // поля пользователя до изменения

    public static function beforeUserUpdate(&$arFields)
    {
        $entityUser = \Bitrix\Main\UserTable::getEntity();
        $query = new \Bitrix\Main\ORM\Query\Query($entityUser);
        $result = $query
            ->setFilter(['ID' => $arFields['ID']])
            ->setSelect(['ID', 'LAST_NAME', 'NAME', 'ACTIVE'])
            ->exec();
        self::$oldValues = $result->fetchAll()[0];     //записываю старые значения b_user в $oldValues
    }

    public static function afterUserUpdate(&$arFields)
    {
        if ($arFields["RESULT"]) {
            $oldValues = self::$oldValues;
            //если изменили хоть одно поле в b_user, которое мы выводим в шаблоне, то сбросим кеш
            if ($oldValues['LAST_NAME'] != $arFields['LAST_NAME'] ||
                $oldValues['NAME'] != $arFields['NAME']
            ) {
                DisTable::getEntity()->cleanCache();
            }
            //если изменилась активность юзера, то меняем ее и в таблице Диспетчеров
            if ($oldValues['ACTIVE'] != $arFields['ACTIVE'])
                self::updateRow($arFields['ID'], $arFields['ACTIVE']);
        }
    }

    public static function afterUserLogin(&$arParams)
    {
        //если пользователь авторизовался, значит изменилась Дата и время последнего входа в систему, следовательно надо сбросить кеш (т.к. мы выводим дату в шаблоне компонента
        if ($arParams['USER_ID'] > 0) {
            DisTable::getEntity()->cleanCache();
        }
    }

    public static function onUserDelete($userId)
    {
        $entity = DisTable::getEntity();
        $query = new \Bitrix\Main\ORM\Query\Query($entity);
        $result = $query->setSelect(['ID'])
            ->setFilter(['USER_ID' => $userId])
            ->exec();
        $row = $result->fetch();
        DisTable::delete($row['ID']);
    }

    public static function afterUserRegister(&$arFields)
    {
        if ($arFields['USER_ID'] > 0) {
            DisTable::add([
                'ACTIVITY' => 'Y',
                'USER_ID' => $arFields['USER_ID'],
                'OBJECT_ID' => rand(1, 3),
                'ACCESS_LEVEL' => rand(1, 12),
                'COMMENT' => 'Какой то комментарий'
            ]);
        }
    }


    public static function updateRow($userId, $activity)
    {
        //получаем сущность таблицы Диспетчеров
        $entity = DisTable::getEntity();
        //получаем сущность для работы с таблицей b_user
        $entityUser = \Bitrix\Main\UserTable::getEntity();
        //при любом апдейте юзера мы апдейтим соотв строку нашей таблицы
        $query = new \Bitrix\Main\ORM\Query\Query($entity);
        $result = $query
            ->setFilter(['USER_ID' => $userId])
            ->setSelect(['ID', 'USERID' => 'USER.ID'])
            ->registerRuntimeField(
                'USER',
                (new \Bitrix\Main\ORM\Fields\Relations\Reference(
                    'USER',
                    $entityUser,
                    \Bitrix\Main\ORM\Query\Join::on(
                        'this.USER_ID',
                        'ref.ID'
                    )
                ))->configureJoinType('left')
            )
            ->exec();
        $obj = $result->fetchObject();
        $obj->set('ACTIVITY', $activity)->save();
        if ($activity == 'N') {
            $time = new \Bitrix\Main\Type\DateTime();
            $obj->set('ACTIVITY_END_DATE', $time)->save();
        }
    }
}