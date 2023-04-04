<?php

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

if (!\Bitrix\Main\Loader::includeModule("reviewsbook"))
    return;

$APPLICATION->SetTitle(Loc::getMessage("REVIEWSBOOK_INSTALL_TITLE"));

\Bitrix\Main\Page\Asset::getInstance()->addJs("/local/modules/reviewsbook/js/main.js");

echo Loc::getMessage("REVIEWSBOOK_INSTALL_DESCRIPTION");
