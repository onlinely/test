<?php

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class book_reviews extends \CModule
{
    public $MODULE_ID = "reviewsbook";
    public $MODULE_NAME = "Отзывы о книгах";
    public $MODULE_DESCRIPTION = "Модуль для отображения отзывов о книгах";
    public $MODULE_VERSION = "1.0.0";
    public $MODULE_VERSION_DATE = "2023-04-04";


    public function __construct()
    {
        if (file_exists(__DIR__ . "/lang/ru/install.php")) {
            $MESS = include __DIR__ . "/lang/ru/install.php";
            if (is_array($MESS)) {
                foreach ($MESS as $key => $value) {
                    $this->PARTNER_MESSAGES[$key] = $value;
                }
            }
        }
    }

    public function DoInstall()
    {
        ModuleManager::registerModule($this->MODULE_ID);

        $this->InstallDB();
        $this->InstallEvents();
        $this->InstallHandlers();

        return true;
    }

    public function DoUninstall()
    {
        $this->UnInstallHandlers();
        $this->UnInstallEvents();
        $this->UnInstallDB();

        ModuleManager::unRegisterModule($this->MODULE_ID);

        return true;
    }

    public function InstallDB()
    {
        Loader::includeModule("iblock");

        // Создание инфоблоков "Книги" и "Отзывы на книги"
        $iblockBook = new CIBlock;
        $iblockBookFields = array(
            "ACTIVE" => "Y",
            "NAME" => "Книги",
            "CODE" => "reviewsbook_books",
            "IBLOCK_TYPE_ID" => "content",
            "SITE_ID" => array("s1")
        );
        $bookId = $iblockBook->Add($iblockBookFields);

        $iblockReviews = new CIBlock;
        $iblockReviewsFields = array(
            "ACTIVE" => "Y",
            "NAME" => "Отзывы на книги",
            "CODE" => "reviewsbook_reviews",
            "IBLOCK_TYPE_ID" => "content",
            "SITE_ID" => array("s1")
        );
        $reviewsId = $iblockReviews->Add($iblockReviewsFields);

        if (!$bookId || !$reviewsId) {
            throw new Exception(GetMessage("REVIEWBOOK_IBLOCK_ERROR"));
        }

        // Добавление свойств "Средняя оценка"
        $ibp = new CIBlockProperty;
        $ibpFields = array(
            "NAME" => "Средняя оценка",
            "CODE" => "RATING",
            "PROPERTY_TYPE" => "N",
            "IBLOCK_ID" => $bookId,
            "ACTIVE" => "Y",
            "DEFAULT_VALUE" => 0
        );
        $ibp->Add($ibpFields);
        // Добавление свойства "Книга" к инфоблоку "Отзывы на книги"
        $ibp = new CIBlockProperty;
        $ibpFields = array(
            "NAME" => "Книга",
            "CODE" => "BOOK",
            "PROPERTY_TYPE" => "E",
            "IBLOCK_ID" => $reviewsId,
            "ACTIVE" => "Y",
            "LINK_IBLOCK_ID" => $bookId
        );
        $ibp->Add($ibpFields);

        // Добавление демо-данных в инфоблок "Книги"
        $bookElement1 = new CIBlockElement;
        $bookElement1Fields = array(
            "IBLOCK_ID" => $bookId,
            "NAME" => "Анна Каренина",
            "CODE" => "anna-karenina",
            "PROPERTY_VALUES" => array(
                "AUTHOR" => "Лев Толстой",
                "YEAR" => "1877",
                "RATING" => "0"
            ),
            "ACTIVE" => "Y"
        );
        $bookId1 = $bookElement1->Add($bookElement1Fields);

        $bookElement2 = new CIBlockElement;
        $bookElement2Fields = array(
            "IBLOCK_ID" => $bookId,
            "NAME" => "Мастер и Маргарита",
            "CODE" => "master-i-margarita",
            "PROPERTY_VALUES" => array(
                "AUTHOR" => "Михаил Булгаков",
                "YEAR" => "1966",
                "RATING" => "0"
            ),
            "ACTIVE" => "Y"
        );
        $bookId2 = $bookElement2->Add($bookElement2Fields);

        // Добавление демо-данных в инфоблок "Отзывы на книги"
        $reviewsElement1 = new CIBlockElement;
        $reviewsElement1Fields = array(
            "IBLOCK_ID" => $reviewsId,
            "NAME" => "Отзыв 1",
            "PROPERTY_VALUES" => array(
                "TEXT" => "Отличная книга!",
                "RATING" => "5",
                "BOOK" => $bookId1
            ),
            "ACTIVE" => "Y"
        );
        $reviewsElement1->Add($reviewsElement1Fields);

        $reviewsElement2 = new CIBlockElement;
        $reviewsElement2Fields = array(
            "IBLOCK_ID" => $reviewsId,
            "NAME" => "Отзыв 2",
            "PROPERTY_VALUES" => array(
                "TEXT" => "Супер!",
                "RATING" => "4",
                "BOOK" => $bookId1
            ),
            "ACTIVE" => "Y"
        );
        $reviewsElement2->Add($reviewsElement2Fields);

        $reviewsElement3 = new CIBlockElement;
        $reviewsElement3Fields = array(
            "IBLOCK_ID" => $reviewsId,
            "NAME" => "Отзыв 3",
            "PROPERTY_VALUES" => array(
                "TEXT" => "Не очень...",
                "RATING" => "2",
                "BOOK" => $bookId2
            ),
            "ACTIVE" => "Y"
        );
        $reviewsElement3->Add($reviewsElement3Fields);
    }
    public function UnInstallDB()
    {
        Loader::includeModule("iblock");

        // Удаление инфоблоков
        $bookIblockId = CIBlock::GetList(array(), array("CODE" => "reviewsbook_books"))->Fetch()["ID"];
        $reviewsIblockId = CIBlock::GetList(array(), array("CODE" => "reviewsbook_reviews"))->Fetch()["ID"];

        CIBlock::Delete($bookIblockId);
        CIBlock::Delete($reviewsIblockId);
    }

    public function InstallEvents()
    {
        return true;
    }

    public function UnInstallEvents()
    {
        return true;
    }

    public function InstallHandlers()
    {
        Loader::includeModule("iblock");

        // Обработчик добавления отзыва
        $eventManager = \Bitrix\Main\EventManager::getInstance();
        $eventManager->registerEventHandler(
            "iblock",
            "OnAfterIBlockElementAdd",
            $this->MODULE_ID,
            "Reviewsbook",
            "onAfterIBlockElementAddHandler"
        );

        // Обработчик изменения отзыва
        $eventManager->registerEventHandler(
            "iblock",
            "OnAfterIBlockElementUpdate",
            $this->MODULE_ID,
            "Reviewsbook",
            "onAfterIBlockElementUpdateHandler"
        );

        // Обработчик удаления отзыва
        $eventManager->registerEventHandler(
            "iblock",
            "OnBeforeIBlockElementDelete",
            $this->MODULE_ID,
            "Reviewsbook",
            "onBeforeIBlockElementDeleteHandler"
        );
    }

    public function UnInstallHandlers()
    {
        Loader::includeModule("iblock");

        // Удаление обработчиков
        $eventManager = \Bitrix\Main\EventManager::getInstance();
        $eventManager->unRegisterEventHandler(
            "iblock",
            "OnAfterIBlockElementAdd",
            $this->MODULE_ID,
            "Reviewsbook",
            "onAfterIBlockElementAddHandler"
        );
        $eventManager->unRegisterEventHandler(
            "iblock",
            "OnAfterIBlockElementUpdate",
            $this->MODULE_ID,
            "Reviewsbook",
            "onAfterIBlockElementUpdateHandler"
        );
        $eventManager->unRegisterEventHandler(
            "iblock",
            "OnBeforeIBlockElementDelete",
            $this->MODULE_ID,
            "Reviewsbook",
            "onBeforeIBlockElementDeleteHandler"
        );
    }
}