<?
require 'vendor/autoload.php';
require 'config.php';

date_default_timezone_set('UTC');

use Aws\DynamoDb\Exception\DynamoDbException;
use Aws\DynamoDb\Marshaler;

//echo "Tiempo 3 : ".(microtime(true) - $timeini)."<br>";


$credentials = new Aws\Credentials\Credentials(AWS_KEY, AWS_PASS);

$sdk = new Aws\Sdk([
    'region'   => AWS_REGION,
    'version'  => 'latest',
    'credentials' => $credentials
]);

$Dynamodb = $sdk->createDynamoDb();
$Marshaler = new Marshaler();


// FUNCTION TO DAVE EVENTS SON DYNAMODB
function SaveEvent($session, $page_id, $event, $value, $extraData = [])
{
	global $Marshaler;
	global $Dynamodb;

	if (empty($page_id) || empty($session))
	{
		return;
	}

	$event_id = uniqid("",true);
	$data='
    {
        "site_id": "' . $page_id . '",
        "session_id" : "' . $session . '",
        "e_id": "' . $event_id . '",
        "e_fulldate": "' . date("YmdHms") . '",
        "e_date": "' . date("Ymd") . '",
        "e_time": "' . date("Hms") . '",
        "e_type": "'. $event .'"';

    if ($value != "")
    {
    	$data .=', "e_value": "'. $value.'"';
    }
    if ($_SERVER["REMOTE_ADDR"]!="")
    {
    	$data .=', "cl_ip": "'. $_SERVER["REMOTE_ADDR"] .'"';
    }

    if ($_SERVER["HTTP_USER_AGENT"] != "")
    {
    	$data .=', "cl_browser_full": "'. $_SERVER["HTTP_USER_AGENT"] .'"';
    }


    //Obtenemos el brower y platform

    
    $browser = parse_user_agent();


    if ($browser["platform"] != "")
    {
    	$data .=', "cl_platform": "'. $browser["platform"] .'"';
    
    }

    if ($browser["browser"] != "")
    {
    	$data .=', "cl_browser": "'. $browser["browser"] .'"';
    
    }

    if ($browser["version"] != "")
    {
    	$data .=', "cl_browser_ver": "'. $browser["version"] .'"';
    
    }
    foreach ($extraData as $Field)
    {
    	if ($Field["value"] != "")
	    {
	    	$data .= ', "' . $Field["name"] . '": "'. $Field["value"] .'"';
	    }
    }

    $data .= "}";

    print_r($data);
	$item = $Marshaler->marshalJson($data);
	$params = [
	    'TableName' => "baw_events",
	    'Item' => $item
	];

	try {
	    $result = $Dynamodb->putItem($params);
	    //return true;
	} catch (DynamoDbException $e) {
	    //$ret["error"] = $e->getMessage();
	}

}



$event = $_REQUEST["event"];
$event_value = $_REQUEST["event_value"];
$page_id = $_REQUEST["page_id"];

//print_r($_REQUEST);

//print_r($_SERVER);

$NewSession = false;

if(isset($_COOKIE["baw_session_id"])){ 
	$session_id = $_COOKIE["baw_session_id"];
}else{
	$session_id = uniqid("",true);
	$timelife = time() + 365*24*60*60;
	$NewSession = true;
	setcookie("baw_session_id", $session_id, $timelife);
}

switch (trim($_REQUEST["event"])) {
	case '':
	case 'visit':
			$ExtraField["name"] = "path";
			if ($_REQUEST["path"] != "")
			{
				$ExtraField["value"] = $_REQUEST["path"];
			}else{
				$ExtraField["value"] = "/";
			}
			

			$ExtraData[] = $ExtraField;

			$ExtraField["name"] = "location";
			$ExtraField["value"] = $_REQUEST["location"];

			$ExtraData[] = $ExtraField;

			$ExtraField["name"] = "referer";
			$ExtraField["value"] = $_REQUEST["referer"];

			$ExtraData[] = $ExtraField;


			if ($NewSession)
			{
				SaveEvent($session_id, $page_id, "new_visit", $event_value, $ExtraData);
			}	

			
			SaveEvent($session_id, $page_id, "visit", $event_value, $ExtraData);
				/*

				$Data ='{
				"deliverable_id" : "'.$pid.'"
				,"project_id" : "'.$projectid.'"
				,"date_create" : "'.$pid.'"
				,"dev_status" : "0"
				,"winner_id" : "'.$winner.'" 
				,"html_code" : "'.$winner_code.'" 
				,"loser_id" : "'.$loser.'" 
				,"loser_code" : "'.$loser_code.'" 
				,"type" : "'.$type.'" 
				,"domain_status" : "0"
			';
			$Data = $Data . '}';
			
				$item = $Marshaler->marshalJson($paramsJson);

			$params = [
			    'TableName' => ($useprefix ? $db_prefix : '').$tableName,
			    'Item' => $item
			];

			$ret["error"]="";
			try {
			    $result = $Dynamodb->putItem($params);
			    //return true;
			} catch (DynamoDbException $e) {
			    $ret["error"] = $e->getMessage();
			}
				*/
		break;
	
	default:
		# code...
		break;
}

?>