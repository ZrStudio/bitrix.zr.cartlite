<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$templateGeneral = GetMessage("ZR_CL_MAIL_EVENT_TEMPLATE");

$dbEvent = CEventMessage::GetList('', '', Array("EVENT_NAME" => "ZR_CL_NEW_ORDER"));
if(!($dbEvent->Fetch()))
{
	$langs = CLanguage::GetList();
	while($lang = $langs->Fetch())
	{
		$lid = $lang["LID"];
		IncludeModuleLangFile(__FILE__, $lid);

		$et = new CEventType;
		$et->Add(array(
			"LID" => $lid,
			"EVENT_NAME" => "ZR_CL_NEW_ORDER",
			"NAME" => GetMessage("ZR_CL_NEW_ORDER_NAME"),
			"DESCRIPTION" => GetMessage("ZR_CL_NEW_ORDER_DESC"),
		));

		$arSites = array();
		$sites = CSite::GetList('', '', Array("LANGUAGE_ID"=>$lid));
		while ($site = $sites->Fetch())
			$arSites[] = $site["LID"];

		if(count($arSites) > 0)
		{
			$template = str_replace("#SITE_CHARSET#", $lang["CHARSET"], $templateGeneral);

			$arHTMLEvents = array("ZR_CL_NEW_ORDER");

			foreach($arHTMLEvents as $eventName)
			{
				$emess = new CEventMessage;

				$message = str_replace(
						array(
								"#TITLE#",
								"#SUB_TITLE#",
								"#TEXT#",
								"#FOOTER_BR#",
								"#FOOTER_SHOP#",
							),
						array(
								GetMessage($eventName."_HTML_TITLE"),
								GetMessage($eventName."_HTML_SUB_TITLE"),
								str_replace("\n", "<br />\n", GetMessage($eventName."_HTML_TEXT")),
								GetMessage("SMAIL_FOOTER_BR"),
								GetMessage("SMAIL_FOOTER_SHOP"),
							),
						$template);

				$emess->Add(array(
					"ACTIVE" => "Y",
					"EVENT_NAME" => $eventName,
					"LID" => $arSites,
					"EMAIL_FROM" => "#SALE_EMAIL#",
					"EMAIL_TO" => "#EMAIL#",
					"SUBJECT" => GetMessage($eventName."_SUBJECT"),
					"MESSAGE" => $message,
					"BODY_TYPE" => "html",
				));
			}
		}
	}
}
?>