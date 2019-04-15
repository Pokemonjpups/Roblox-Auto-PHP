<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$subdomains = array(
    "groups"=>array(
        "v1","v2"
    ),
    "economy"=>array(
        "v1",
    ),
    "auth"=>array(
        "v1","v2"
    ),
    "accountsettings"=>array(
        "v1","v2",
    ),
    "presence"=>array(
        "v1"
    ),
    "notifications"=>array(
        "v2",
    ),
    "games"=>array(
        "v1","v2",
    ),
    "followings"=>array(
        "v1","v2",
    ),
    "catalog"=>array(
        "v1",
    ),
    "develop"=>array(
        "v1","v2",
    ),
    "contacts"=>array(
        "v1",
    ),
    "avatar"=>array(
        "v1",
    ),
    "publish"=>array(
        "v1",
    ),
    "midas"=>array(
        "v1",
    ),
    "locale"=>array(
        "v1",
    ),
    "friends"=>array(
        "v1",
    ),
    "badges"=>array(
        "v1",
    ),
    "billing"=>array(
        "v1",
    ),
    "captcha"=>array(
        "v1"
    )
);
$alldocs = array();
foreach ($subdomains as $key=>$value) {
    foreach ($value as $v) {
        $alldocs[] = json_decode(file_get_contents("https://" . $key . ".roblox.com/docs/json/" . $v . "?_=1555342414706"), true);
    }
}

$ret = "class roblox {
    private \$headers;

    public function __construct(\$cookie = 0)
    {
        if (\$cookie !== 0) {
            \$this->\$headers = array(\"Cookie\"=>\".ROBLOSECURITY=\$cookie\");
        }else{
            \$this->\$headers = array();
        }
    }
";
$ret = $ret . substr(file_get_contents("curl.php"), 6);

$query = "";
$ret_key = 0;
$input_paramarr = array();
$func_names = array();

function literalArray($array)
{
    //return displayArrayRecursively($array);
    
    $d = "";
    $d = $d . "array(";
    if (gettype($array) === "array") {
        foreach ($array as $key=>$value) {
            if (gettype($value) === "array") {
                $d = $d . "
    [\"$key\"]=>array(";
                foreach ($value as $nkey=>$nval) {
                    $d = $d . "
    [\"$nkey\"]=>\"$nval\",";
                }
                $d = $d . "),";
            }elseif (gettype($value) === "string") {
                $d = $d . "
    [\"$key\"]=>\"$value\",";
            }else {
                $d = $d . "
    [$key]=>\"$value\",";
            }
        }
    }
    $d = $d . ")";
    return $d;
    
}

foreach ($alldocs as &$d) {
    $ret = $ret . "
// " . $d["info"]["title"] . "\n\n";
    foreach ($d["paths"] as $key=>$value) {
        $base = $d["schemes"][0] . "://" . $d["host"];
        $query = "";
        $url = $base . $key;
        foreach ($value as $method=>$data) {
            if ($method === "get") {
                $name = str_replace('/','',$key);
                $funcname = preg_replace("/\{[^}]+\}/","",$name);
                $funcname = str_replace('-', '', $funcname);
                if (in_array($method . $funcname, $func_names) === true) {
                    $funcname = $funcname . "2";
                }
                array_push($func_names, $method . $funcname);
                $ret = $ret .  'public function ' . $method . $funcname . '(';
                if (count($data["parameters"]) > 0) {
                    foreach ($data["parameters"] as &$param) {
                        $param["varname"] = str_replace('.','',$param["name"]);
                        $param["varname"] = str_replace('-','',$param["varname"]);
                        $var = "\$get" . $param["varname"];
                        $ret = $ret . $var . ", ";
                        if ($param["in"] === "path") {
                            $url = str_replace('{' . $param["name"] . '}',$var, $url);
                        }
                    }
                    $ret = substr($ret, 0, -2);
                }else{
                
                }
                $ret = $ret . ')
    {';
                if (isset($data["summary"])) {
                    $ret = $ret . '
// ' . preg_replace( "/\r|\n/", " ", $data["summary"]);
                }
                $ret = $ret . '
        $data = $this->getRequestWithCookie("' . $url;

    if (count($data["parameters"]) > 0) {
        $success = false;
        foreach ($data["parameters"] as &$param) {
            if ($param["in"] === "query") {
                $param["varname"] = str_replace('.','',$param["name"]);
                $varname = "$" . $param["varname"];
                $query = $query . $param["name"] . "=" . $varname . "&";
                $success = true;
            }
        }
        if ($success === true) {
            $query = substr($query, 0, -1);
            $query = "?" . $query;
            $ret = $ret . $query;
            $query = "";
        }
    }else{

    }


    $ret = $ret . '", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    ';
                $ret = $ret . "\n";
            }else{

                $name = str_replace('/','',$key);
                $funcname = preg_replace("/\{[^}]+\}/","",$name);
                $funcname = str_replace('-', '', $funcname);
                if (in_array($method . $funcname, $func_names) === true) {
                    $funcname = $funcname . "2";
                }
                array_push($func_names, $method . $funcname);
                $ret = $ret .  'public function ' . $method . $funcname . '(';
                $input_param = array();
                if (count($data["parameters"]) > 0) {
                    $success = false;
                    foreach ($data["parameters"] as &$param) {
                        $param["varname"] = str_replace('.','',$param["name"]);
                        $param["varname"] = str_replace('-','',$param["varname"]);
                        if (!isset($param["schema"])) {
                            $success = true;
                            $var = "\$get" . $param["varname"];
                            $ret = $ret . $var . ", ";
                            if ($param["in"] === "path") {
                                $url = str_replace('{' . $param["name"] . '}',$var, $url);
                            }

                            //$input_param[$param["varname"]] = $var;
                        }else{
                            $input_param[$param["varname"]] = array();

                            if (isset($param["schema"]["\$ref"])) {
                                $format = substr($param["schema"]["\$ref"], 14);
                                $format = $d["definitions"][$format];
                                foreach ($format["properties"] as $input=>$ival) {
                                    $ival["varname"] = str_replace('.','',$input);
                                    if (!isset($ival["items"])) {
                                        $success = true;
                                        if (isset($ival["enum"])) {
                                            $var = "$" . $ival["varname"];
                                            //$var = "$" . $ival["varname"] . " = \"" . $ival["enum"][0] . "\"";
                                        }else{
                                            $var = "$" . $ival["varname"];
                                        }
                                        $ret = $ret . $var . ", ";

                                        $input_param[$param["varname"]][$ival["varname"]] = $var;
                                    }else{
                                        if (isset($ival["items"]["\$ref"])) {
                                            $format = substr($ival["items"]["\$ref"], 14);
                                            if (isset($d["definitions"][$format])) {
                                                $format = $d["definitions"][$format];
                                                if (isset($format["properties"])) {
                                                    foreach ($format["properties"] as $input_in=>$ival_in) {
                                                        if (!isset($ival_in["items"])) {
                                                            $success = true;
                                                            $ival_in["varname"] = str_replace('.','',$input_in);
                                                            if (isset($ival_in["enum"])) {
                                                                //$var = "$" . $ival_in["varname"] . " = \"" . $ival_in["enum"][0] . "\"";
                                                                $var = "$" . $ival_in["varname"];
                                                            }else{
                                                                $var = "$" . $ival_in["varname"];
                                                            }
                                                            $ret = $ret . $var . ", ";

                                                            $input_param[$param["varname"]][$ival["varname"]][$ival_in["varname"]] = $var;
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        //var_dump( $input_param);
                    }
                    if ($success === true) {
                        $ret = substr($ret, 0, -2);
                    }            
                }
                $ret = $ret . ')
    {
        // ' . preg_replace( "/\r|\n/", "", $data["summary"]) . '

        $body = json_encode(
    ';

    if (isset($input_param["request"])) {
    $ret = $ret . literalArray($input_param["request"]);
    }else{
        $ret = $ret . "array()";
    }

    $ret = $ret . ');
        $data = $this->postRequestWithCookie("' . $url;

    if (count($data["parameters"]) > 0) {
        $success = false;
        foreach ($data["parameters"] as &$param) {
            if ($param["in"] === "query") {
                $param["varname"] = str_replace('.','',$param["name"]);
                $varname = "$" . $param["varname"];
                $query = $query . $param["name"] . "=" . $varname . "&";
                $success = true;
            }
        }
        if ($success === true) {
            $query = substr($query, 0, -1);
            $query = "?" . $query;
            $ret = $ret . $query;
        }
    }

    $ret = $ret . '", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"' . $method . '"]);
        return $data;
    }
    ';
    $ret_key = $ret_key + 1;
            }
        }
    }
}
$ret = $ret . "
}";
$ret = "// Class Roblox. Generated on " . date("D M j\, o \@ g\:i A e") . "
// Example Call: \$roblox = new roblox(\"roblox_cookie_here\");
\n\n" . $ret;
echo $ret;

$f = fopen("roblox_" . time() . ".txt", "w") or die("Unable to open file!");
fwrite($f, $ret);
echo "Success!";
