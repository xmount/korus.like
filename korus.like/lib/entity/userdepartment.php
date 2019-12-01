<?php

namespace Korus\Like\Entity;

use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\Entity\IntegerField;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\Entity\StringField;
use Korus\Like\Entity\User;
use Korus\Like\Entity\DepartmentTable;

class UserDepartmentTable extends DataManager
{
    public static function getTableName()
    {
        return 'korus_like_user_department';
    }

    public static function getMap()
    {
        return array(
            new IntegerField('ID', array('primary' => true, 'autocomplete' => true)),
            new IntegerField('USER_ID'),
            new IntegerField('DEPARTMENT_ID'),
            
            new ReferenceField(
                'USER',
                UserTable::getEntity(),
                array('=this.USER_ID' => 'ref.ID')
            ),
            
            new ReferenceField(
                'DEPARTMENT',
                DepartmentTable::getEntity(),
                array('=this.DEPARTMENT_ID' => 'ref.ID')
            )
        );
    }
}
