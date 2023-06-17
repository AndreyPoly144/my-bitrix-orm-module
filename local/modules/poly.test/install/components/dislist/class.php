<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

use poly\test\DisTable;

class testList extends CBitrixComponent
{
    public function executeComponent()
    {
        \Bitrix\Main\Loader::includeModule('poly.test');

        //формируем необходимые данные для вывода в шаблоне (к сущности список диспетчеров джойнятся 2 сущности список объектов и  b_user)
        $this->arResult=self::composeData();
        $this->includeComponentTemplate();
    }

    static function composeData()
    {
        //сущность по таблице Список диспетчеров
        $disEntity = poly\test\DisTable::getEntity();
        //сущность по таблице Список объектов
        $objEntity = poly\test\ObjTable::getEntity();
        //сущность по таблице b_user
        $userEntity = Bitrix\Main\UserTable::getEntity();

        //добавляем поле USER к ведущей сущности $disEntity
        $disEntity->addField(
            (new \Bitrix\Main\ORM\Fields\Relations\Reference(
                'USER',
                $userEntity,
                \Bitrix\Main\ORM\Query\Join::on(
                'this.USER_ID',
                'ref.ID'
            )
            ))->configureJoinType('left')
        );

        //добавляем поле OBJECT к ведущей сущности $disEntity
        $disEntity->addField(
            (new \Bitrix\Main\ORM\Fields\Relations\Reference(
                'OBJECT',
                $objEntity,
                \Bitrix\Main\ORM\Query\Join::on(
                'this.OBJECT_ID',
                'ref.ID'
            )
            ))->configureJoinType('left')
        );

        $query = new Bitrix\Main\ORM\Query\Query($disEntity);
        $result = $query
            ->setSelect(
                [
                    'ID',
                    'USER_LAST_NAME' => 'USER.LAST_NAME',
                    'USER_NAME' => 'USER.NAME',
                    'ACCESS_LEVEL',
                    'LAST_LOGIN' => 'USER.LAST_LOGIN',
                    'COMMENT',
                    'OBJECT_NAME' => 'OBJECT.NAME'
                ]
            )
            ->setCacheTtl(3000)
            ->cacheJoins(true)
            ->exec();
        return $result->fetchAll();
    }
}