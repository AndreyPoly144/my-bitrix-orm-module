<?php

namespace poly\test;
use Bitrix\Main\Entity;

//описание сущности список объектов
class ObjTable extends Entity\DataManager
{
    public static function getTableName()
    {
        return 'my_objects';
    }

    public static function getMap()
    {
        return array(
            //ID
            new Entity\IntegerField('ID', array(
                'primary' => true,
                'autocomplete' => true
            )),

            //Дата и время создание записи
            new Entity\DatetimeField('DATE_OF_CREATION', [
                'default_value' => new \Bitrix\Main\Type\DateTime()
            ]),

            //Наименование
            new Entity\StringField('NAME'),

            //Адрес
            new Entity\TextField('ADDRESS'),

            //Комментарий
            new Entity\TextField('COMMENT'),
        );
    }
}
