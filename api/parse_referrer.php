<?

$str = file_get_contents('Data/referrer.json');
$json_data = json_decode($str, true);

$ref_data=[];

foreach ($json_data as $medium => $referers) {
    foreach ($referers as $source => $referer) {
        foreach ($referer['domains'] as $domain) {
            $parameters = isset($referer['parameters']) ? $referer['parameters'] : [];
            //($domain, $source, $medium, $parameters);
            $dom["source"] = $source;
            $dom["medium"] = $medium;
            $dom["params"] = $parameters;
            $ref_data[$domain] =  $dom;
        }
    }
}

function parse_referrer($url, $data)
{
    if ($url === null) {
    	$ret["type"] = "Direct";
    	$ret["keywords"] = "";
        return $ret;
    }

    $parts = parse_url($url);
    if (!isset($parts['scheme']) || !in_array(strtolower($parts['scheme']), ['http', 'https'])) {
        $ret["type"] = "Others";
    	$ret["keywords"] = "";
        return $ret;
    }

    $parts = array_merge(['query' => null, 'path' => '/'], $parts);


    $referer = lookup($data, $refererParts['host'], $refererParts['path']);
}

function lookup($data, $host, $path)
{
    $referer = lookupPath($data, $host, $path);

   
    if ($referer) {
        return $referer;
    }

    echo "lookupHost ".$host;
    return lookupHost($data, $host);
}


function lookupPath($data, $host, $path)
{
    $referer = lookupHost($data, $host, $path);

    if ($referer) {
        return $referer;
    }

    $path = substr($path, 0, strrpos($path, '/'));

    if (!$path) {
        return null;
    }

    return lookupPath($data, $host, $path);
}

function lookupHost($data, $host, $path = null)
{
    do {
        $referer =  isset($data[$host . $path]) ? $data[$host . $path] : null;
        $host = substr($host, strpos($host, '.') + 1);
    } while (!$referer && substr_count($host, '.') > 0);

    return $referer;
}


echo "parse";

print_r(parse_referrer("http://www.google.com/search?q=gateway+oracle+cards+denise+linn&hl=en&client=safari",$ref_data));



?>