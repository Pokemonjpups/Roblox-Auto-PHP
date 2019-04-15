<?php

    // -------------------------
    // -- GLOBAL VARIABLES    --
    // -- Description: Used in a variety of functions below.
    // -------------------------

    // -------------------------
    // -- PROTECTED FUNCTIONS --
    // -- Description: Functions used by Other Functions in this file
    // -------------------------

    // file_get_contents replacement
    private function file_get_contents_curl($url, $extraOptions = 0)
    {
    	$ch = curl_init();
    	
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);
    	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        $data = curl_exec($ch);
        if ($extraOptions !== 0) {
            if ($extraOptions["ReturnStatusCode"] === true) {
                $data = array("data"=>$data,"statuscode"=> curl_getinfo($ch, CURLINFO_HTTP_CODE));
            }
        }
    	
    	curl_close($ch);
    	
    	return $data;
    }

    // Generic GET Request with Headers
    private function GetRequest($url, $headers, $extraOptions = 0)
    {
        $ch = curl_init();
    	
    	if ($extraOptions !== 0) {
            if ($extraOptions["Header"] === true) {
                curl_setopt($ch, CURLOPT_HEADER, 1);
            }else{
                curl_setopt($ch, CURLOPT_HEADER, 0);
            }
        }
        $headerarray = $this->headers;
        foreach ($headers as $key=>$value) {
            $headerarray[] = $key . ": " . $value;
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headerarray);
    	
        $data = curl_exec($ch);
        if ($extraOptions !== 0) {
            if ($extraOptions["ReturnStatusCode"] === true) {
                $data = array("data"=>$data,"statuscode"=> curl_getinfo($ch, CURLINFO_HTTP_CODE));
            }
        }
    	curl_close($ch);
    	
    	return $data;
    }

    // Generic POST Request with Headers
    private function postRequest($url, $data, $headers, $extraOptions = 0)
    {
        $ch = curl_init();
            
        if ($extraOptions !== 0) {
            if ($extraOptions["Header"] === true) {
                curl_setopt($ch, CURLOPT_HEADER, 1);
            }else{
                curl_setopt($ch, CURLOPT_HEADER, 0);
            }
        }
        $headerarray = $this->headers;
        foreach ($headers as $key=>$value) {
            $headerarray[] = $key . ": " . $value;
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        if ($extraOptions !== 0) {
            if ($extraOptions["CustomRequest"] !== null) {
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $extraOptions["CustomRequest"]);
            }else{
                curl_setopt($ch, CURLOPT_POST, 1);
            }
        }else{
            curl_setopt($ch, CURLOPT_POST, 1);
        }
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headerarray);
            
        $data = curl_exec($ch);

        if ($extraOptions !== 0) {
            if ($extraOptions["ReturnStatusCode"] === true) {
                $data = array("data"=>$data,"statuscode"=> curl_getinfo($ch, CURLINFO_HTTP_CODE));
            }
        }

        curl_close($ch);
            
        return $data;
    }

    // Retrieves ASP.NET Verification Tokens (For Legacy Pages)
    // @Return: array("header"=>string,"form"=>string)
    private function retrieveVerificationTokens($headers, $url = "https://www.roblox.com/places/create")
    {
        $headers["Referer"] = $url;
        $data = $this->getRequestWithCookie($url, $headers, array("Header"=>true));
        preg_match("/<input name=\"__RequestVerificationToken\" type=\"hidden\" value=\".+\"/", $data, $matches);
        preg_match('/__RequestVerificationToken=?.+;/', $data, $headertoken);
        $r = array("header"=>$headertoken[0],"form"=>substr($matches[0], 62, -1));
        return $r;
    }

    // Retrieve X-CSRF-Token
    // @Return: array("success"=>bool,"token"=>string)
    private function retrieveXSRF($isAuthenticated = false, $headers = 0, $body = "{}")
    {
        if ($headers === 0) {
            $headers = $this->headers;
            $data = $this->postRequest("https://auth.roblox.com/v2/login", $body, $headers, array("Header"=>true));
        }else{
            if ($isAuthenticated === true) {
                $data = $this->postRequest("https://auth.roblox.com/v2/logout", "{}", $headers, array("Header"=>true));
            }else{
                $data = $this->postRequest("https://auth.roblox.com/v2/login", $body, $headers, array("Header"=>true));
            }
        }
        preg_match('/X-CSRF-TOKEN: ............/', $data, $matches);
        if (substr($matches[0], 14) !== null and substr($matches[0], 14) !== "") {
            return array("success"=>true,"token"=>substr($matches[0], 14));
        }else{
            return array("success"=>false);
        }
    }

    // Post Request w/ Cookie
    // @Return: string
    private function postRequestWithCookie($url, $body = "{}", $headers = 0, $extraOptions = 0)
    {

        if ($extraOptions["IgnoreXSRF"] === true) {
            $data = $this->postRequest($url, $body, $headers, $extraOptions);
            return $data;
        }else{
            $xsrf = $this->retrieveXSRF(true, $headers);
            if ($xsrf["success"]) {
                $headers = $this->headers;
                $headers["X-CSRF-Token"] =  $xsrf["token"];
                $data = $this->postRequest($url, $body, $headers, $extraOptions);
                return $data;
            }else{
                // TODO: Add error handling...
            }
        }
    }

    // Post Request w/o Cookie
    // @Return: string
    private function postRequestWithoutCookie($url, $body = "{}", $headers = 0, $extraOptions = 0)
    {
        $xsrf = $this->retrieveXSRF(false, $headers, $body);
        if ($xsrf["success"]) {
            $headers = $this->headers;
            $headers["X-CSRF-Token"] =  $xsrf["token"];
            $data = $this->postRequest($url, $body, $headers, $extraOptions);
            return $data;
        }else{
            // TODO: Add error handling...
        }
    }

    // Get Request w/ Cookie
    // @Return: string
    private function getRequestWithCookie($url, $headers = 0, $extraOptions = 0)
    {
        $data = $this->getRequest($url, $headers, $extraOptions);
        return $data;
    }
