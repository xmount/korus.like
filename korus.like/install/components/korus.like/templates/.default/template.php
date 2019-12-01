<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}
?>
<div class="korus_content">
    <form id="filter_like" method="POST" action="<?= $arResult["FORM_ACTION"] ?>">
        <select name="way_like">
            <option value="user_for" <?= ($arResult["values"]["way_like"] == "user_for") ? "selected" : "" ?>>Кто сказал
                спасибо
            </option>
            <option value="user_to" <?= ($arResult["values"]["way_like"] == "user_to") ? "selected" : "" ?>>Кому сказали
                спасибо
            </option>
        </select>

        Min: <input type="text" name="min_date" size="10" placeholder="<?= $arResult["MIN_DATE"] ?>"
                    value="<?= $arResult["values"]["MIN_DATE"] ?>">
        <span style="margin-left: 10px;"></span>
        Max: <input type="text" name="max_date" size="10" placeholder="<?= $arResult["MAX_DATE"] ?>"
                    value="<?= $arResult["values"]["MAX_DATE"] ?>">

        <select name="department">
            <option value="">Все отделы</option>
            <?php foreach ($arResult["DEPARTMENT_ALL"] as $id => $item) { ?>
                <?php if (!$item["PARENT"]) { ?>
                    <option class="department"
                            value="<?= $id ?>" <?= ($arResult["values"]["department"] == $id) ? "selected" : "" ?>><?= strtoupper($item["NAME"]) ?></option>
                <? } else { ?>
                    <option value="<?= $id ?>" <?= ($arResult["values"]["department"] == $id) ? "selected" : "" ?>>
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?= $item["NAME"] ?></option>
                <? } ?>

            <? } ?>
        </select>

        <select name="users">
            <option value="">Все пользователи</option>
            <?php foreach ($arResult["USERS"] as $item) { ?>
                <option value="<?= $item["ID"] ?>" <?= ($arResult["values"]["users"] == $item["ID"]) ? "selected" : "" ?>><?= $item["NAME"] ?></option>
            <? } ?>
        </select>
        <button id="check_result">Check</button>
    </form>
    <table>
        <caption>Таблица благодарностей</caption>
        <?php foreach ($arResult['USER'] as $user) { ?>
            <tr>
                <td><?= $user["USER_NAME"] ?></td>
                <td><?= $user["LIKE_CNT"] ?></td>
                <td><?= $user["DEPARTMENT_NAME"] ?></td>
                <td><?= $arResult["DEPARTMENT"][$user["DEPARTMENT_PARENT"]] ?></td>
            </tr>

        <?
        }
        ?>
    </table>
    <?php
    $APPLICATION->IncludeComponent(
        "bitrix:main.pagenavigation",
        "",
        array(
            "NAV_OBJECT" => $arResult["NAV"],
            "SEF_MODE" => "N",
        ),
        false
    );
    ?>
    <br><span>Всего строк: <?= $arResult["NAV"]->getRecordCount() ?>; Запросы заняли <?= $arResult["time"] ?>
        сек.</span>
</div>