<?php
date_default_timezone_set('UTC');
include(".env");

$member_groups = getData(
	'membergroup?format=full&filter=$children.member.%60Medlemsnummer%60%20=%20',
	'&',
	$_GET['n']
);

$calender_IDS = getCalId($member_groups);

$event_data = getData(
	'calendarevent?format=full&filter=$parents.$id%20IN%20(',
	')&',
	implode(',', $calender_IDS)
);
switch ($_GET['type']) {
	case "json":
		header('Content-Type: application/json; charset=utf-8');
		echo json_encode($event_data);
		break;
	default:
		header('Content-Type: text/calendar; charset=utf-8');
		header('Content-Disposition: attachment; filename=spejder.ics');
		echo "BEGIN:VCALENDAR\xA";
		echo "PRODID:Calendar\xA";
		echo "VERSION:2.0\xA";

		foreach ($event_data as $event) {
			echo "BEGIN:VEVENT\xA";
			echo "UID:" . $event->id . "@spejder.de\xA";
			echo "CLASS:PUBLIC\xA";
			echo "DESCRIPTION:" . $event->properties->description . "\xA";
			echo "DTSTAMP;VALUE=DATE-TIME:" . date_format(date_create($event->lastmodified), "c") .  "\xA";
			echo "DTSTART;VALUE=DATE-TIME:" . date_format(date_create($event->properties->begin), "c") .  "\xA";
			echo "DTEND;VALUE=DATE-TIME:" . date_format(date_create($event->properties->begin), "c") .  "\xA";
			echo "LOCATION:" . $event->properties->place . "\xA";
			echo "SUMMARY;LANGUAGE=dk-da:" . $event->properties->title . "\xA";
			echo "TRANSP:TRANSPARENT\xA";
			echo "END:VEVENT\xA";
		}
		echo "END:VCALENDAR";
}


function getCalId($groups)
{
	$calender_IDS = [];
	foreach ($groups as $group) {
		$calender = $group->links->calendar;
		if (isset($calender)) $calender_IDS = array_merge($calender_IDS, $calender);
	}
	return $calender_IDS;
}

function getData($filter, $filter_close, $value)
{
	global $API_KEY, $API_URL;
	$ret = json_decode(CallAPI($API_URL . $filter . $value . $filter_close . "apikey=" . $API_KEY));

	//if (isset($ret["objects"])) return $ret["objects"];
	return $ret;
}

function CallAPI($url)
{
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($curl, CURLOPT_URL, $url);

	$result = curl_exec($curl);

	curl_close($curl);

	return $result;
}
