<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$arComponentDescription = array(
    "NAME" => GetMessage("COMPONENT_NAME"),
    "DESCRIPTION" => GetMessage("COMPONENT_DESCRIPTION"),
    "PATH" => array(
        "ID" => "ZrStudio",
        "CHILD" => array(
            "ID" => "zrstudio",
            "NAME" => GetMessage("COMPONENT_CHILD_NAME")
        )
    ),
    "ICON" => "/images/icon.gif",
);
?>