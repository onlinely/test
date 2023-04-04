<?php
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

if (!check_bitrix_sessid()) {
    return;
}

echo CAdminMessage::ShowMessage([
    'TYPE' => 'OK',
    'MESSAGE' => Loc::getMessage('BOOK_REVIEWS_UNINSTALL_SUCCESS'),
]);
