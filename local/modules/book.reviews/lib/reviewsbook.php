<?php

use Bitrix\Iblock\ElementTable;
use Bitrix\Main\Loader;

class Reviewsbook
{
    const IBLOCK_BOOKS = "books";
    const IBLOCK_REVIEWS = "reviews";

    public static function getList($page = 1, $limit = 10)
    {
        Loader::includeModule("iblock");

        $bookFields = array(
            "ID",
            "IBLOCK_ID",
            "NAME",
            "PROPERTY_AUTHOR",
            "PROPERTY_YEAR",
            "PROPERTY_RATING"
        );

        $reviewsFields = array(
            "ID",
            "IBLOCK_ID",
            "NAME",
            "PROPERTY_DATE",
            "PROPERTY_TEXT",
            "PROPERTY_RATING",
            "PROPERTY_BOOK"
        );

        $books = ElementTable::getList(array(
            "select" => $bookFields,
            "filter" => array(
                "=IBLOCK_ID" => self::getIblockIdByCode(self::IBLOCK_BOOKS),
                ">PROPERTY_RATING" => 0
            ),
            "order" => array(
                "ID" => "ASC"
            ),
            "limit" => $limit,
            "offset" => ($page - 1) * $limit
        ))->fetchAll();

        $reviews = ElementTable::getList(array(
            "select" => $reviewsFields,
            "filter" => array(
                "=IBLOCK_ID" => self::getIblockIdByCode(self::IBLOCK_REVIEWS),
                ">PROPERTY_RATING" => 0
            ),
            "order" => array(
                "ID" => "ASC"
            ),
            "limit" => $limit,
            "offset" => ($page - 1) * $limit
        ))->fetchAll();

        $result = array();

        foreach ($reviews as $review) {
            $book = self::getBookById($review["PROPERTY_BOOK_VALUE"], $bookFields);
            if ($book) {
                $result[] = array(
                    "date" => $review["PROPERTY_DATE_VALUE"],
                    "text" => $review["PROPERTY_TEXT_VALUE"],
                    "rating" => $review["PROPERTY_RATING_VALUE"],
                    "book" => array(
                        "title" => $book["NAME"],
                        "author" => $book["PROPERTY_AUTHOR_VALUE"],
                        "year" => $book["PROPERTY_YEAR_VALUE"]
                    )
                );
            }
        } return $result;
    }

    private static function getIblockIdByCode($code)
    {
        $iblock = CIBlock::GetList(array(), array("CODE" => $code))->Fetch();

        if ($iblock) {
            return $iblock["ID"];
        }

        return false;
    }

    private static function getBookById($id, $fields)
    {
        $book = ElementTable::getList(array(
            "select" => $fields,
            "filter" => array(
                "=ID" => $id
            )
        ))->fetch();

        return $book;
    }
}