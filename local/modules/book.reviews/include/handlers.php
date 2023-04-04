<?php

use Bitrix\Iblock\ElementTable;
use Bitrix\Main\EventManager;

$eventManager = EventManager::getInstance();

$eventManager->addEventHandler(
    "iblock",
    "OnAfterIBlockElementAdd",
    array("ReviewsbookEventHandler", "OnAfterIBlockElementAddHandler")
);

$eventManager->addEventHandler(
    "iblock",
    "OnAfterIBlockElementUpdate",
    array("ReviewsbookEventHandler", "OnAfterIBlockElementUpdateHandler")
);

$eventManager->addEventHandler(
    "iblock",
    "OnAfterIBlockElementDelete",
    array("ReviewsbookEventHandler", "OnAfterIBlockElementDeleteHandler")
);

class ReviewsbookEventHandler
{
    public static function OnAfterIBlockElementAddHandler(&$arFields)
    {
        if ($arFields["IBLOCK_CODE"] == Reviewsbook::IBLOCK_REVIEWS) {
            self::recalculateBookRating($arFields["PROPERTY_VALUES"]["BOOK"]);
        }
    }

    public static function OnAfterIBlockElementUpdateHandler(&$arFields)
    {
        if ($arFields["IBLOCK_CODE"] == Reviewsbook::IBLOCK_REVIEWS) {
            $oldBook = ElementTable::getRowById($arFields["ID"], array("select" => array("PROPERTY_BOOK")));
            if ($oldBook["PROPERTY_BOOK_VALUE"] != $arFields["PROPERTY_VALUES"]["BOOK"]) {
                self::recalculateBookRating($oldBook["PROPERTY_BOOK_VALUE"]);
                self::recalculateBookRating($arFields["PROPERTY_VALUES"]["BOOK"]);
            } else {
                self::recalculateBookRating($arFields["PROPERTY_VALUES"]["BOOK"]);
            }
        }
    }

    public static function OnAfterIBlockElementDeleteHandler($arFields)
    {
        if ($arFields["IBLOCK_CODE"] == Reviewsbook::IBLOCK_REVIEWS) {
            self::recalculateBookRating($arFields["PROPERTY_VALUES"]["BOOK"]);
        }
    }

    private static function recalculateBookRating($bookId)
    {
        $bookFields = array(
            "ID",
            "IBLOCK_ID",
            "PROPERTY_RATING"
        );

        $reviewsFields = array(
            "ID",
            "IBLOCK_ID",
            "PROPERTY_RATING",
            "PROPERTY_BOOK"
        );

        $book = Reviewsbook::getBookById($bookId, $bookFields);
        if ($book) {
            $reviews = ElementTable::getList(array(
                "select" => $reviewsFields,
                "filter" => array(
                    "=IBLOCK_ID" => Reviewsbook::getIblockIdByCode(Reviewsbook::IBLOCK_REVIEWS),
                    "=PROPERTY_BOOK" => $bookId,
                    ">PROPERTY_RATING" => 0
                )
            ))->fetchAll();

            $ratingSum = 0;
            $ratingCount = 0;

            foreach ($reviews as $review) {
                $ratingSum += $review["PROPERTY_RATING_VALUE"];
                $ratingCount++;
            }

            if ($ratingCount > 0) {
                $rating = $ratingSum / $ratingCount;
            } else {
                $rating = 0;
            }

            CIBlockElement::SetPropertyValuesEx(
                $bookId,
                Reviewsbook::getIblockIdByCode(Reviewsbook::IBLOCK_BOOKS),
                array(
                    "RATING" => $rating
                )
            );
        }
    }
}