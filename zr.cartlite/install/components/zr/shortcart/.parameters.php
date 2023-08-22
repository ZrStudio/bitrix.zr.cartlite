<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if(!CModule::IncludeModule("iblock"))
	return;
if(!CModule::IncludeModule("zr.cartlite"))
	return;

$arTypeQuantity = [
    'PC' => GetMessage("ZR_CL_SC_TYPE_SHOW_QUANTITY_PC"),
    'PCQ' => GetMessage("ZR_CL_SC_TYPE_SHOW_QUANTITY_PCQ"),
];

$arComponentParameters = array(
	"PARAMETERS" => array(
		"BASKET_URL" => array(
            "PARENT" => "BASE",
            "NAME" => GetMessage("ZR_CL_SC_BASKET_URL"),
            "TYPE" => "STRING"
        ),
		"TYPE_SHOW_QUANTITY" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("ZR_CL_SC_TYPE_SHOW_QUANTITY"),
			"TYPE" => "LIST",
			"VALUES" => $arTypeQuantity,
		),
	),
);