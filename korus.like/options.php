<?php
defined('B_PROLOG_INCLUDED') || die;

/**
 * @var string $mid module id from GET
 */

use Bitrix\Main\Config\Configuration;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

global $APPLICATION, $USER;

if (!$USER->IsAdmin()) {
    return;
}

$module_id = 'korus.like';
Loader::includeModule($module_id);
