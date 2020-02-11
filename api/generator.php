<?

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



function SaveEvent($user_id, $session, $site_id, $event, $value, $full_date, $extraData = [])
{
	global $Marshaler;
	global $Dynamodb;

	global $ref_page;
	global $ref_data;

	$refpage = $ref_page[array_rand($ref_page)];

	$referrer = parse_referrer($refpage,$ref_data);

	//echo "Referrer";
	//print_r($referrer);

	$event_id = uniqid("",true);
	//$full_date = date("YmdHms");
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

	/*if ($event=="visit")
	{
		print_r($params);
	}*/

	try {
		print_r($item);
	  //  $result = $Dynamodb->putItem($params);

	    if ($result)
	    {
	    	$out["id"] = $event_id;
	    	$out["date"] = $full_date;

	    	return $out;
	    }
	    //return true;
	} catch (DynamoDbException $e) {
	    //$ret["error"] = $e->getMessage();
	    print_r($e);
	}
}

$tevents = rand(0,80);

$sites[]="1581375649.5616";
$sites[]="1581377272.4755";
$sites[]="1581377370.9319";
$sites[]="1581377488.9137";
$sites[]="1581377554.3685";

$ref_page[]="www.google.com";
$ref_page[]="www.yahoo.com";
$ref_page[]="www.faceboo.com";
$ref_page[]="www.twitter.com";
$ref_page[]="/";
$ref_page[]="/";
$ref_page[]="bexi.io";
$ref_page[]="aaaaa.com";
$ref_page[]="bing.com";
$ref_page[]="/";
$ref_page[]="getmodu.com";

$site_id = "";

echo "ejecutando ".$tevents;

for ($nevent = 0; $nevent<=$tevents; $nevent++)
{
	if ($site_id=="" || rand(0,1))
	{
		$site_id = $sites[array_rand($sites)];
		$session="";
		$user_id="";
	}

	if ($session=="" || rand(0,1))
	{
		$session = uniqid("",true);
		$newSession=true;
	}else{
		$newSession=false;
	}

	if ($user_id=="" || rand(0,1))
	{
		$user_id = uniqid("",true);
		$session = uniqid("",true);
		$newSession=true;
	}

	$ExtraField["name"] = "ref_type";
	$ExtraField["value"] = $referrer["medium"];
	$ExtraData[] = $ExtraField;

	$ExtraField["name"] = "ref_source";
	$ExtraField["value"] = $referrer["source"];
	$ExtraData[] = $ExtraField;
	
	//$fulldate = date("Ymd", strtotime("-".(rand(0,6))." days", strtotime(date("Y-m-d H:m:s")));
	$fulldate =	date("YmdHms", strtotime("-".(rand(0,6))." days", strtotime(date("Y-m-d H:m:s"))));

	if ($newSession)
	{
		SaveEvent($user_id, $session, $site_id, "new_visit", 1, $fulldate, $ExtraData);
		$newSession=false;
	}

	SaveEvent($user_id, $session, $site_id, "visit", 1, $fulldate, $ExtraData);
	SaveEvent($user_id, $session, $site_id, "time_page", rand(10,300), $fulldate, $ExtraData);
	SaveEvent($user_id, $session, $site_id, "scroll_percentage", rand(0,100), $fulldate, $ExtraData);
	SaveEvent($user_id, $session, $site_id, "click", rand(0,10), $fulldate, $ExtraData);
	
}

?>