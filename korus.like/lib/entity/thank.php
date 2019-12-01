<?php

namespace Korus\Like\Entity;

use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\Entity\IntegerField;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\Entity\StringField;
use Bitrix\Main\Entity\DatetimeField;
use Korus\Like\Entity\User;

class ThankTable extends DataManager
{
    public static function getTableName()
    {
        return 'korus_like_thank';
    }

    public static function getMap()
    {
        return array(
            new IntegerField('ID', array('primary' => true, 'autocomplete' => true)),
            new IntegerField('USER_FROM_ID'),
            new IntegerField('USER_TO_ID'),
            new DatetimeField('DATE'),

            new ReferenceField(
                'USER_FROM',
                UserTable::getEntity(),
                array('=this.USER_FROM_ID' => 'ref.ID')
            ),

            new ReferenceField(
                'USER_TO',
                UserTable::getEntity(),
                array('=this.USER_TO_ID' => 'ref.ID')
            )

        );
    }
}
