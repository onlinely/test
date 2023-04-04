<?php
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

if (!check_bitrix_sessid()) {
    return;
}

echo CAdminMessage::ShowMessage([
    'TYPE' => 'OK',
    'MESSAGE' => Loc::getMessage('BOOK_REVIEWS_INSTALL_SUCCESS'),
]);

echo BeginNote();
echo Loc::getMessage('BOOK_REVIEWS_INSTALL_NOTE');
echo EndNote();
