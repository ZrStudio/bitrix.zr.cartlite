<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if(!CModule::IncludeModule("zr.cartlite"))
	return;

$arComponentParameters = array(
	"PARAMETERS" => array(
		"BACK_URL" => array(
            "PARENT" => "BASE",
            "NAME" => GetMessage("ZR_CL_BACK_URL"),
            "TYPE" => "STRING"
        ),
        "BACK_CATALOG_URL" => array(
            "PARENT" => "BASE",
            "NAME" => GetMessage("ZR_CL_BACK_CATALOG_URL"),
            "TYPE" => "STRING"
        ),
        "TYPE_SHOW_TOTAL_CONTAINER" => array(
            "PARENT" => "BASE",
            "NAME" => GetMessage("ZR_CL_TYPE_SHOW_TOTAL_CONTAINER"),
            "TYPE" => "LIST",
            "VALUES" => [
                "TOP" => GetMessage("ZR_CL_TYPE_SHOW_TOTAL_CONTAINER_TOP"),
                "BOTTOM" => GetMessage("ZR_CL_TYPE_SHOW_TOTAL_CONTAINER_BOTTOM"),
            ],
            "DEFAULT" => "TOP"
        )
	),
);