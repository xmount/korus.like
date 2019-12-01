<?php

use \Bitrix\Main;
use \Bitrix\Main\Application;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Type;
use Korus\Like\Entity;
use Korus\Like\Entity\ThankTable;
use Korus\Like\Entity\UserTable;
use Korus\Like\Entity\DepartmentTable;
use Korus\Like\Entity\UserDepartmentTable;
use \Bitrix\Main\Entity\Query;
use Bitrix\Main\Context;

class likeList extends CBitrixComponent
{

    private function checkModules()
    {
        if (!Main\Loader::includeModule('korus.like')) {
            throw new Main\LoaderException(Loc::getMessage('KORUS_LIKE_MODULE_NOT_INSTALLED'));
        }
    }

    private function getLike($way_like, $filter = null)
    {
        // Если не выбрано кто/кому сказал спасибо то по умолчанию выводится "Кто сказал спасибо"
        if ($way_like == "user_to") {
            $way_like = "USER_TO";
        } else {
            $way_like = "USER_FROM";
        }

        $nav = new \Bitrix\Main\UI\PageNavigation("nav-more-likes"); // Инифиализируем постраничную навигацию
        $nav->allowAllRecords(true)
            ->setPageSize(20)
            ->initFromUri();

        $q = new Query(ThankTable::getEntity());
        $q->registerRuntimeField("LIKE_CNT", array("data_type" => "integer", "expression" => array("COUNT('ID')")));

        $q->setSelect(array("LIKE_CNT"));
        $q->addSelect($way_like, "USER_");
        $q->addSelect("USER", "USERDEP_");
        $q->registerRuntimeField("USER",
            array("data_type" => "UserDepartment", 'reference' => array('=this.USER_ID' => 'ref.ID')));
        $q->addSelect("DEPARTMENT", "DEPARTMENT_");
        $q->registerRuntimeField("DEPARTMENT",
            array("data_type" => "Department", 'reference' => array('=this.USERDEP_DEPARTMENT_ID' => 'ref.ID')));

        if ($filter) {
            $q->setFilter($filter);
        }
        
        $q->setOrder(array('LIKE_CNT' => 'DESC'));
        $sql = $q->getQuery();

        $q->setOffset($nav->getOffset());
        $q->setLimit($nav->getLimit());
        $result = $q->exec();

        // Определяем сколько всего строк вернул запрос с установленными фильтрами без лимитом
        // К сожалению, проще не получилось, поэтому выше сохранили сгенерированный запрос в переменную sql,
        // и подсчитываем в ней число строк:
        if ($resultTotal = Application::getConnection()->queryScalar(sprintf('SELECT count(*) FROM (%s) as TOTAL',
            $sql))) {
            $total = $resultTotal;
        }

        $nav->setRecordCount($total);

        return array("USER" => $result->fetchAll(), "NAV" => $nav);
    }

    private function getDepartment()
    {
        $q = new Query(DepartmentTable::getEntity());
        $q->setSelect(array("*"));
        $result = $q->exec()->fetchAll();

        $return = array();
        foreach ($result as $value) {
            $return[$value["PARENT"]][] = $value;
        }

        return $return;
    }

    private function getDepartmentTree($arDepartment, $parent_id, $level)
    {
        if (isset($arDepartment[$parent_id])) { //Если категория с таким parent_id существует
            foreach ($arDepartment[$parent_id] as $value) { //Обходим ее
                $this->return[$value["ID"]] = array("NAME" => $value["NAME"], "PARENT" => $value["PARENT"]);
                $level++; //Увеличиваем уровень вложености
                //Рекурсивно вызываем этот же метод, но с новым $parent_id и $level
                $this->getDepartmentTree($arDepartment, $value["ID"], $level);
                $level--; //Уменьшаем уровень вложености
            }
        }
        return $this->return;
    }


    // Преобразуем массив с департаментами чтобы в шаблоне было проще определять отдел
    private function convertDepartment($arDepartment)
    {
        foreach ($arDepartment as $department) {
            if ($department["PARENT"] == 0) {
                $array[$department["ID"]] = $department["NAME"];
            }
        }
        return $array;
    }

    private function getDate($order)
    {
        $q = new Query(ThankTable::getEntity());
        $q->setSelect(array("DATE"));

        $q->setOrder(array('DATE' => $order));
        $q->setLimit(1);
        $result = $q->exec();

        return $result->fetch()["DATE"]->format("Y-m-d");
    }

    private function getUser()
    {
        $q = new Query(UserTable::getEntity());
        $q->setSelect(array("ID", "NAME"));
        $q->setOrder(array('NAME' => "ASC"));
        $result = $q->exec();
        return $result->fetchAll();
    }

    public function executeComponent()
    {
        $context = Context::getCurrent(); // Получаем контекст текущего хита
        $request = $context->getRequest(); // Получаем из контекста объект Request

        $this->checkModules(); // Проверяем установлен ли сам модуль

        $this->arResult['FORM_ACTION'] = $request->getRequestedPage(); //Передаем в шаблон адрес запрошенной страницы
        $this->arResult["values"] = $request->getPostList()->toArray(); //Передаем в шаблон параметры POST-запроса

        $timestart = microtime(true); // Определяем время старта запросов, для расчета времени запросов
        $this->arResult['MIN_DATE'] = $this->getDate('ASC'); // Определяем минимальную дату
        $this->arResult['MAX_DATE'] = $this->getDate('DESC'); // Определяем максимальную дату
        //Получаем из таблицы в БД все отделы с подотделами
        $department = $this->getDepartment();
        //и оставляем только головные отделы:
        $this->arResult["DEPARTMENT"] = $this->convertDepartment($department[0]);
        //Если в фильтре установлена дата и она отличается от мин/макс даты то добавляем дату в фильтр. Если дата не установлена или совпадает с мин/макс, то фильтовать по ней нет надобности
        if ($request->getPost("min_date") && $request->getPost("min_date") != $this->arResult['MIN_DATE']) {
            $filter[] = array('>DATE' => new Type\Date($request->getPost("min_date"), 'Y-m-d'));
        }
        if ($request->getPost("max_date") && $request->getPost("max_date") != $this->arResult['MAX_DATE']) {
            $filter[] = array('<DATE' => new Type\Date($request->getPost("max_date"), 'Y-m-d'));
        }
        
        // Определяем что было выбрано- головной отдел или подотдел, в зависимости от этого добавляем фильтрацию по нужному столбцу
        if ($request->getPost("department")) {
            if (array_key_exists($request->getPost("department"), $this->arResult["DEPARTMENT"])) {
                $filter[] = array("DEPARTMENT_PARENT" => $request->getPost("department"));
            } else {
                $filter[] = array("DEPARTMENT_ID" => $request->getPost("department"));
            }
        }

        // Если в фильтре выбрали пользователя:
        if ($request->getPost("users")) {
            $filter[] = array("USER_ID" => $request->getPost("users"));
        }
        // Сортировка списка подотделов по отделам, чтобы проще было использовать в шаблоне
        $this->arResult["DEPARTMENT_ALL"] = $this->getDepartmentTree($department, 0, 0);

        // Получаем список лайков согласно фильтрам:
        $user_like = $this->getLike($request->getPost("way_like"), $filter);

        $this->arResult["USER"] = $user_like["USER"]; // Передаем в шаблог
        $this->arResult["NAV"] = $user_like["NAV"]; // Для постраничной навигации
        $this->arResult["USERS"] = $this->getUser(); // Список всех пользователей

        $timeend = microtime(true); // Время завершения всех запросов
        $this->arResult["time"] = $timeend - $timestart; // Время генерации запросов
        $this->includeComponentTemplate(); // Инициализация и подключение шаблона
    }
} ?>