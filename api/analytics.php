<?
session_start();

header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method,Set-Cookie, Cookie, Bearer");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Allow: GET, POST, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Credentials: true");


require 'vendor/autoload.php';
require 'config.php';
require "parse_referrer.php";

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



//print_r($ref_data);


// FUNCTION TO SAVE EVENTS ON DYNAMODB
function SaveEvent($user_id, $session, $site_id, $event, $value, $extraData = [])
{
	global $Marshaler;
	global $Dynamodb;

	if (empty($site_id) || empty($user_id) || empty($session))
	{
		return;
	}

	$event_id = uniqid("",true);
	$full_date = date("YmdHms");
	$dateonly = substr($full_date,0,8);
	$timeonly = substr($full_date,8);
	$data='
    {
        "site_id": "' . $site_id . '",
        "user_id" : "' . $user_id . '",
        "session_id" : "' . $session . '",
        "e_id": "' . $event_id . '",
        "e_fulldate": "' . $full_date . '",
        "e_date": "' . $dateonly . '",
        "e_time": "' . $timeonly . '",
        "e_type": "'. $event .'"';

    if (isset($value) && $value!=="")
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

	$item = $Marshaler->marshalJson($data);
	$params = [
	    'TableName' => "baw_events",
	    'Item' => $item
	];

	if ($event=="visit")
	{
		
	}

	try {
	    $result = $Dynamodb->putItem($params);

	    if ($result)
	    {
	    	$out["id"] = $event_id;
	    	$out["date"] = $full_date;

	    	return $out;
	    }
	    //return true;
	} catch (DynamoDbException $e) {
	    //$ret["error"] = $e->getMessage();
	}

}

// FUNCTION TO UPDATE EVENTS ON DYNAMODB
function updateEvent($event_id,$full_date,$value){
	global $Marshaler;
	global $Dynamodb;


	$JupdateData ='{
		":evalue" : "'.$value.'"
	}';
	$UpdateExpression="set e_value =:evalue";

	$Jkey = '
	    {
			"e_id": "'.$event_id.'",
			"e_fulldate" : "'.$full_date.'"
	    }
	';

	$key = $Marshaler->marshalJson($Jkey);

	$data = $Marshaler->marshalJson($JupdateData);


	$params = [
	    'TableName' => 'baw_events',
	    'Key' => $key,
	    'UpdateExpression' => $UpdateExpression,
	    'ExpressionAttributeValues'=> $data,
	    'ReturnValues' => 'UPDATED_NEW'
	];

	$ret["error"]="";
	try {
	    $result = $Dynamodb->updateItem($params);
	    $ret["result"]=$result['Attributes'];
	} catch (DynamoDbException $e) {
	    $ret["error"]= $e->getMessage();
	}

	return $ret;


}





$event = $_REQUEST["event"];
$event_value = $_REQUEST["event_value"];
$site_id = $_REQUEST["site_id"];


$NewSession = false;

if(isset($_REQUEST["baw_user_id"]) && $_REQUEST["baw_user_id"]!=="undefined"){
	$user_id = $_REQUEST["baw_user_id"];
}else{
	$user_id = uniqid("",true);
	$NewUser = true;
}


if (isset($_REQUEST["baw_session_id"]) && $_REQUEST["baw_session_id"]!=="undefined")
{
	$session_id = $_REQUEST["baw_session_id"];
}else{
	$session_id = uniqid("",true);
	$NewSession = true;
}
$events=[];
switch (trim($_REQUEST["event"])) {
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

			$referrer = parse_referrer($_REQUEST["referer"],$ref_data);

			//echo "Referrer";
			//print_r($referrer);

			$ExtraField["name"] = "ref_type";
			$ExtraField["value"] = $referrer["medium"];
			$ExtraData[] = $ExtraField;

			$ExtraField["name"] = "ref_source";
			$ExtraField["value"] = $referrer["source"];
			$ExtraData[] = $ExtraField;
			


			if ($NewUser)
			{
				SaveEvent($user_id, $session_id, $site_id, "new_visit", $event_value, $ExtraData);
			}
			if ($NewSession)
			{
				SaveEvent($user_id, $session_id, $site_id, "new_session", $event_value, $ExtraData);
				$e = SaveEvent($user_id, $session_id, $site_id, "time_page", 0, $ExtraData);

				if ($e)
				{
					$temporal["name"]="time_page";
					$temporal["id"]=$e["id"];
					$temporal["date"]=$e["date"];
					$events[]=$temporal;
				}
			}
			//print_r($ExtraData);
			SaveEvent($user_id, $session_id, $site_id, "visit", $event_value, $ExtraData);
		break;
	case 'collector':
			foreach ($_REQUEST["Data"] as $index => $item) {
				if ( isset($item["id"]) && isset($item["date"]) && $item["id"]!=="" && $item["date"]!=="")
				{
					updateEvent($item["id"],$item["date"],$item["value"]);
				}
				else{

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

					
					$e = SaveEvent($user_id, $session_id, $site_id, $index, $item["value"],$ExtraData);

					if ($e)
					{
						$temporal["name"]=$index;
						$temporal["id"]=$e["id"];
						$temporal["date"]=$e["date"];
						$events[]=$temporal;
					}
				}
			}
		break;
	default:
		# code...
		break;
}

if($NewUser){
	$res["baw_user_id"]=$user_id;
}
if($NewSession){
	$res["baw_session_id"]=$session_id;
}
$res["events"] = $events;
$res["error"]=0;
echo json_encode($res);

?>