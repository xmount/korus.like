<?
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");

use Korus\Like\Entity\UserTable;
use Korus\Like\Entity\ThankTable;
use korus\Like\Entity\DepartmentTable;
use korus\Like\Entity\UserDepartmentTable;
use Bitrix\Main\Loader;

use Bitrix\Main\Type\DateTime;

class adduser extends CBitrixComponent
{
    public function executeComponent()
    {
        global $APPLICATION;
        if (!Loader::includeModule('korus.like')) {
            echo "error!";
        }
        $arNamelOld = file_get_contents("namel.php");
        $arNamelOld = explode("\n", $arNamelOld);
        $arNameOld = file_get_contents("name.php");
        $arNameOld = explode("\n", $arNameOld);
        $nn = 0;
        for ($i = 0; $i < 600; $i++) { //600 на всякий случай если одинаковых пар Имя Фамилия будет 100

            $namel = array_rand($arNamelOld, 1); // Береься случайная фамилия
            $name = array_rand($arNameOld, 1); // Берется случайное имя

            $newName = $arNameOld[$name] . " " . $arNamelOld[$namel]; // И формируем Имя Фамилия

            if (in_array($newName, $arName)) { // Если пара Имя фамилия уже были ранее то пропускаем
                continue;
            }
            $nn++;
            if ($nn > 500) { // Если сгенерировали 500 пар Имя Фамилия то достаточно 
                break;
            }

            $arName[$nn] = $newName;
            echo $newName . "<br>";
            UserTable::add(array('NAME' => $newName)); // Добавляем в базу данных
        }
        for ($i = 0; $i < 70000; $i++) {
            $name = array_rand($arName, 2);
            $int = rand(1293883200, 1615464000);
            $date = DateTime::createFromTimestamp($int);

            ThankTable::add(array('USER_FROM_ID' => $name[0], 'USER_TO_ID' => $name[1], 'DATE' => $date));
        }
    }
}

class department extends CBitrixComponent
{
    public function executeComponent()
    {
        if (!Loader::includeModule('korus.like')) {
            echo "error!";
        }
        $arParent = array(
            "Бухгалтерия",
            "Отдел Продаж",
            "Отдел маркетинга",
            "Департамент1",
            "Департамент2",
            "Отдел кадров"
        );
        foreach ($arParent as $parent) { 
            DepartmentTable::add(array("NAME" => $parent, "PARENT" => 0));
        }
        $nn = 0;
        foreach ($arParent as $id => $parent) { // Берем по порядку каждый департамент и генерируем случайное число подотделов, от 2 до 7
            for ($i = 1; $i <= rand(2, 7); $i++) {
                DepartmentTable::add(array("NAME" => "Подразделение_" . $i, "PARENT" => $id + 1));
                $nn++;
            }
        }
        echo $nn;
        $countParent = count($arParent);
        for ($i = 1; $i <= 500; $i++) { // Назначаем отделы пользователям только подотделы, без Департаментов
            UserDepartmentTable::add(array(
                "USER_ID" => $i,
                "DEPARTMENT_ID" => rand($countParent + 1, $countParent + $nn)
            ));
        }
    }
}

adduser::executeComponent();
department::executeComponent();