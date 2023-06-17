<?php

namespace poly\test;
use Bitrix\Main\Entity;

//описание сущности список диспетчеров
class DisTable extends Entity\DataManager
{
    public static function getTableName()
    {
        return 'my_dispatchers';
    }

    public static function getMap()
    {
        return array(
            //ID
            new Entity\IntegerField('ID', array(
                'primary' => true,                             //первичный ключ
                'autocomplete' => true                //автозаполнение
            )),

            //Дата и время создание записи
            new Entity\DatetimeField('DATE_OF_CREATION', [
                'default_value' => new \Bitrix\Main\Type\DateTime()           //значение по умолчанию будет текущее время
            ]),

            //Активность
            new Entity\BooleanField(
                'ACTIVITY',
                ['values' => ['N', 'Y']]
            ),

            //Дата окончания активности
            new Entity\DatetimeField('ACTIVITY_END_DATE'),

            //ID пользователя из b_user
            new Entity\IntegerField('USER_ID'),

            //Комментарий
            new Entity\TextField('COMMENT'),

            //Уровень доступа (число от 1 до 12)
            new Entity\IntegerField('ACCESS_LEVEL'),

            //ID объекта
            new Entity\IntegerField('OBJECT_ID'),
        );
    }
}
