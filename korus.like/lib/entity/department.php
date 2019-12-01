<?php

namespace Korus\Like\Entity;

use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\Entity\IntegerField;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\Entity\StringField;


class DepartmentTable extends DataManager
{
    public static function getTableName()
    {
        return 'korus_like_department';
    }

    public static function getMap()
    {
        return array(
            new IntegerField('ID', array('primary' => true, 'autocomplete' => true)),
            new StringField('NAME'),
            new IntegerField('PARENT'),
        );
    }
}
