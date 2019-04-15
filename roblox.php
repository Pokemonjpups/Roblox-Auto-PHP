<?php

// Class Roblox. Generated on Mon Apr 15, 2019 @ 9:44 PM UTC
// Example Call: $roblox = new roblox("roblox_cookie_here");


class roblox {
    private $headers;

    public function __construct($cookie = 0)
    {
        if ($cookie !== 0) {
            $this->$headers = array("Cookie"=>".ROBLOSECURITY=$cookie");
        }else{
            $this->$headers = array();
        }
    }


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
// Groups Api v1

public function getv1groups($getgroupId)
    {
// Gets group information
        $data = $this->getRequestWithCookie("https://groups.roblox.com/v1/groups/$getgroupId", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function getv1groupsmembership($getgroupId)
    {
// Gets group membership information in the context of the authenticated user
        $data = $this->getRequestWithCookie("https://groups.roblox.com/v1/groups/$getgroupId/membership", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function getv1groupspayouts($getgroupId)
    {
// Gets a list of the group payout percentages
        $data = $this->getRequestWithCookie("https://groups.roblox.com/v1/groups/$getgroupId/payouts", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function postv1groupspayouts($getgroupId, $PayoutType, $recipientId, $recipientType, $amount)
    {
        // Pays out a user in Robux.

        $body = json_encode(
    array(
    ["PayoutType"]=>"$PayoutType",
    ["Recipients"]=>array(
    ["recipientId"]=>"$recipientId",
    ["recipientType"]=>"$recipientType",
    ["amount"]=>"$amount",),));
        $data = $this->postRequestWithCookie("https://groups.roblox.com/v1/groups/$getgroupId/payouts", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"post"]);
        return $data;
    }
    public function getv1groupsrelationships($getgroupId, $getgroupRelationshipType, $getmodelstartRowIndex, $getmodelmaxRows)
    {
// Gets a group's relationships
        $data = $this->getRequestWithCookie("https://groups.roblox.com/v1/groups/$getgroupId/relationships/$getgroupRelationshipType?model.startRowIndex=$modelstartRowIndex&model.maxRows=$modelmaxRows", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function getv1groupsrelationshipsrequests($getgroupId, $getgroupRelationshipType, $getmodelstartRowIndex, $getmodelmaxRows)
    {
// Gets a group's relationship requests
        $data = $this->getRequestWithCookie("https://groups.roblox.com/v1/groups/$getgroupId/relationships/$getgroupRelationshipType/requests?model.startRowIndex=$modelstartRowIndex&model.maxRows=$modelmaxRows", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function getv1groupsroles($getgroupId)
    {
// Gets a list of the rolesets in a group.
        $data = $this->getRequestWithCookie("https://groups.roblox.com/v1/groups/$getgroupId/roles", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function getv1groupsrolespermissions($getgroupId, $getroleSetId)
    {
// Gets the permissions for a group's roleset. The authorized user must either be the group owner or the roleset being requested, except for guest roles, which can be viewed by all (members and guests).
        $data = $this->getRequestWithCookie("https://groups.roblox.com/v1/groups/$getgroupId/roles/$getroleSetId/permissions", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function getv1groupsrolesusers($getgroupId, $getroleSetId, $getsortOrder, $getlimit, $getcursor)
    {
// Gets a list of users in a group for a specific roleset.
        $data = $this->getRequestWithCookie("https://groups.roblox.com/v1/groups/$getgroupId/roles/$getroleSetId/users?sortOrder=$sortOrder&limit=$limit&cursor=$cursor", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function getv1groupsrolesguestpermissions($getgroupId)
    {
// Gets the permissions for a group's guest roleset. These can be viewed by all (members and guests) users.
        $data = $this->getRequestWithCookie("https://groups.roblox.com/v1/groups/$getgroupId/roles/guest/permissions", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function getv1groupssociallinks($getgroupId)
    {
// Get social link data associated with a group
        $data = $this->getRequestWithCookie("https://groups.roblox.com/v1/groups/$getgroupId/social-links", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function postv1groupssociallinks($getgroupId, $type, $url, $title)
    {
        // Posts a social links

        $body = json_encode(
    array(
    ["type"]=>"$type",
    ["url"]=>"$url",
    ["title"]=>"$title",));
        $data = $this->postRequestWithCookie("https://groups.roblox.com/v1/groups/$getgroupId/social-links", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"post"]);
        return $data;
    }
    public function getv1groupswallposts($getgroupId, $getsortOrder, $getlimit, $getcursor)
    {
// Gets a list of group wall posts.
        $data = $this->getRequestWithCookie("https://groups.roblox.com/v1/groups/$getgroupId/wall/posts?sortOrder=$sortOrder&limit=$limit&cursor=$cursor", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function postv1groupswallposts($getgroupId, $body)
    {
        // Creates a post on a group wall

        $body = json_encode(
    array(
    ["body"]=>"$body",));
        $data = $this->postRequestWithCookie("https://groups.roblox.com/v1/groups/$getgroupId/wall/posts", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"post"]);
        return $data;
    }
    public function getv1groupsmetadata()
    {
// Gets Groups contextual information:  Max number of groups a user can be part of.   Current number of groups a user is a member of.   Whether to show/hide certain features based on device type.
        $data = $this->getRequestWithCookie("https://groups.roblox.com/v1/groups/metadata", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function getv1roles($getids)
    {
// Gets the Roles by their ids.
        $data = $this->getRequestWithCookie("https://groups.roblox.com/v1/roles?ids=$ids", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function getv1usergroupspending()
    {
// Gets groups that the authenticated user has requested to join
        $data = $this->getRequestWithCookie("https://groups.roblox.com/v1/user/groups/pending", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function getv1usersgroupmembershipstatus($getuserId)
    {
// Gets a user's membership status related to group
        $data = $this->getRequestWithCookie("https://groups.roblox.com/v1/users/$getuserId/group-membership-status", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function getv1usersgroupsprimaryrole($getuserId)
    {
// Gets a user's primary group.
        $data = $this->getRequestWithCookie("https://groups.roblox.com/v1/users/$getuserId/groups/primary/role", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function getv1usersgroupsroles($getuserId)
    {
// Gets a list of all group roles for groups the specified user is in.
        $data = $this->getRequestWithCookie("https://groups.roblox.com/v1/users/$getuserId/groups/roles", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function postv1groupsclaimownership($getgroupId)
    {
        // Claims ownership of the group as the authenticated user

        $body = json_encode(
    array());
        $data = $this->postRequestWithCookie("https://groups.roblox.com/v1/groups/$getgroupId/claim-ownership", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"post"]);
        return $data;
    }
    public function postv1groupspayoutsrecurring($getgroupId, $PayoutType, $recipientId, $recipientType, $amount)
    {
        // Updates recurring payouts.

        $body = json_encode(
    array(
    ["PayoutType"]=>"$PayoutType",
    ["Recipients"]=>array(
    ["recipientId"]=>"$recipientId",
    ["recipientType"]=>"$recipientType",
    ["amount"]=>"$amount",),));
        $data = $this->postRequestWithCookie("https://groups.roblox.com/v1/groups/$getgroupId/payouts/recurring", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"post"]);
        return $data;
    }
    public function postv1groupsrelationships($getgroupId, $getgroupRelationshipType, $getrelatedGroupId)
    {
        // Create a group relationship.

        $body = json_encode(
    array());
        $data = $this->postRequestWithCookie("https://groups.roblox.com/v1/groups/$getgroupId/relationships/$getgroupRelationshipType/$getrelatedGroupId", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"post"]);
        return $data;
    }
    public function deletev1groupsrelationships($getgroupId, $getgroupRelationshipType, $getrelatedGroupId)
    {
        // Deletes a group relationship.

        $body = json_encode(
    array());
        $data = $this->postRequestWithCookie("https://groups.roblox.com/v1/groups/$getgroupId/relationships/$getgroupRelationshipType/$getrelatedGroupId", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"delete"]);
        return $data;
    }
    public function postv1groupsrelationshipsrequests($getgroupId, $getgroupRelationshipType, $getrelatedGroupId)
    {
        // Accepts a group relationship request.

        $body = json_encode(
    array());
        $data = $this->postRequestWithCookie("https://groups.roblox.com/v1/groups/$getgroupId/relationships/$getgroupRelationshipType/requests/$getrelatedGroupId", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"post"]);
        return $data;
    }
    public function deletev1groupsrelationshipsrequests($getgroupId, $getgroupRelationshipType, $getrelatedGroupId)
    {
        // Declines a group relationship request.

        $body = json_encode(
    array());
        $data = $this->postRequestWithCookie("https://groups.roblox.com/v1/groups/$getgroupId/relationships/$getgroupRelationshipType/requests/$getrelatedGroupId", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"delete"]);
        return $data;
    }
    public function postv1groupsusers($getgroupId)
    {
        // Joins a group

        $body = json_encode(
    array());
        $data = $this->postRequestWithCookie("https://groups.roblox.com/v1/groups/$getgroupId/users", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"post"]);
        return $data;
    }
    public function postv1groupscreate($getrequestname, $getrequestdescription, $getrequestpublicGroup, $getrequestbuildersClubMembersOnly, $getrequestfiles)
    {
        // Creates a new group.

        $body = json_encode(
    array());
        $data = $this->postRequestWithCookie("https://groups.roblox.com/v1/groups/create", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"post"]);
        return $data;
    }
    public function postv1usergroupsprimary($groupId)
    {
        // Sets the authenticated user's primary group

        $body = json_encode(
    array(
    ["groupId"]=>"$groupId",));
        $data = $this->postRequestWithCookie("https://groups.roblox.com/v1/user/groups/primary", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"post"]);
        return $data;
    }
    public function deletev1usergroupsprimary()
    {
        // Removes the authenticated user's primary group

        $body = json_encode(
    array());
        $data = $this->postRequestWithCookie("https://groups.roblox.com/v1/user/groups/primary", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"delete"]);
        return $data;
    }
    public function deletev1groupssociallinks($getgroupId, $getsocialLinkId)
    {
        // Deletes a social link

        $body = json_encode(
    array());
        $data = $this->postRequestWithCookie("https://groups.roblox.com/v1/groups/$getgroupId/social-links/$getsocialLinkId", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"delete"]);
        return $data;
    }
    public function patchv1groupssociallinks($getgroupId, $getsocialLinkId, $type, $url, $title)
    {
        // Updates a social link

        $body = json_encode(
    array(
    ["type"]=>"$type",
    ["url"]=>"$url",
    ["title"]=>"$title",));
        $data = $this->postRequestWithCookie("https://groups.roblox.com/v1/groups/$getgroupId/social-links/$getsocialLinkId", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"patch"]);
        return $data;
    }
    public function patchv1groupsstatus($getgroupId, $message)
    {
        // Sets group status

        $body = json_encode(
    array());
        $data = $this->postRequestWithCookie("https://groups.roblox.com/v1/groups/$getgroupId/status", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"patch"]);
        return $data;
    }
    public function deletev1groupsusers($getgroupId, $getuserId)
    {
        // Removes a user from a group

        $body = json_encode(
    array());
        $data = $this->postRequestWithCookie("https://groups.roblox.com/v1/groups/$getgroupId/users/$getuserId", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"delete"]);
        return $data;
    }
    public function patchv1groupsusers($getgroupId, $getuserId, $roleId)
    {
        // Updates a users role in a group.

        $body = json_encode(
    array(
    ["roleId"]=>"$roleId",));
        $data = $this->postRequestWithCookie("https://groups.roblox.com/v1/groups/$getgroupId/users/$getuserId", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"patch"]);
        return $data;
    }
    public function deletev1groupsjoinrequestsusers($getgroupId, $getuserId)
    {
        // Declines/cancels a group join request.

        $body = json_encode(
    array());
        $data = $this->postRequestWithCookie("https://groups.roblox.com/v1/groups/$getgroupId/join-requests/users/$getuserId", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"delete"]);
        return $data;
    }
    public function deletev1groupswallposts($getgroupId, $getpostId)
    {
        // Deletes a group wall post.

        $body = json_encode(
    array());
        $data = $this->postRequestWithCookie("https://groups.roblox.com/v1/groups/$getgroupId/wall/posts/$getpostId", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"delete"]);
        return $data;
    }
    public function deletev1groupswallusersposts($getgroupId, $getuserId)
    {
        // Deletes all group wall posts made by a specific user.

        $body = json_encode(
    array());
        $data = $this->postRequestWithCookie("https://groups.roblox.com/v1/groups/$getgroupId/wall/users/$getuserId/posts", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"delete"]);
        return $data;
    }
    
// Groups Api v2

public function getv2groups($getgroupIds)
    {
// Multi-get groups information by Ids.
        $data = $this->getRequestWithCookie("https://groups.roblox.com/v2/groups?groupIds=$groupIds", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function getv2groupswallposts($getgroupId, $getsortOrder, $getlimit, $getcursor)
    {
// Gets a list of group wall posts.
        $data = $this->getRequestWithCookie("https://groups.roblox.com/v2/groups/$getgroupId/wall/posts?sortOrder=$sortOrder&limit=$limit&cursor=$cursor", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function postv2groupswallposts($getgroupId, $body)
    {
        // Creates a post on a group wall

        $body = json_encode(
    array(
    ["body"]=>"$body",));
        $data = $this->postRequestWithCookie("https://groups.roblox.com/v2/groups/$getgroupId/wall/posts", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"post"]);
        return $data;
    }
    
// Economy Api v1

public function getv1groupscurrency($getgroupId)
    {
// Gets currency for the specified group.
        $data = $this->getRequestWithCookie("https://economy.roblox.com/v1/groups/$getgroupId/currency", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function getv1userscurrency($getuserId)
    {
// Gets currency for the specified user.
        $data = $this->getRequestWithCookie("https://economy.roblox.com/v1/users/$getuserId/currency", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    

// Authentication Api v1

public function getv1accountpin()
    {
// Gets the account pin status. If the account pin is valid, this returns the time in seconds until when the account pin is unlocked.
        $data = $this->getRequestWithCookie("https://auth.roblox.com/v1/account/pin", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function postv1accountpin($pin)
    {
        // Reuqest to create the account pin.

        $body = json_encode(
    array());
        $data = $this->postRequestWithCookie("https://auth.roblox.com/v1/account/pin", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"post"]);
        return $data;
    }
    public function deletev1accountpin()
    {
        // Request for deletes the account pin from the account.

        $body = json_encode(
    array());
        $data = $this->postRequestWithCookie("https://auth.roblox.com/v1/account/pin", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"delete"]);
        return $data;
    }
    public function patchv1accountpin($pin)
    {
        // Request made to update the account pin on the account.

        $body = json_encode(
    array());
        $data = $this->postRequestWithCookie("https://auth.roblox.com/v1/account/pin", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"patch"]);
        return $data;
    }
    public function getv1authmetadata()
    {
// Gets Auth meta data
        $data = $this->getRequestWithCookie("https://auth.roblox.com/v1/auth/metadata", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function getv1credentialsverification($getrequestcredentialType, $getrequestcredentialValue, $getrequestpassword)
    {
// Checks if it is possible to send a verification message for the provided credentials.
        $data = $this->getRequestWithCookie("https://auth.roblox.com/v1/credentials/verification?request.credentialType=$requestcredentialType&request.credentialValue=$requestcredentialValue&request.password=$requestpassword", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function getv1passwordsvalidate($getrequestusername, $getrequestpassword)
    {
// Endpoint for checking if a password is valid.
        $data = $this->getRequestWithCookie("https://auth.roblox.com/v1/passwords/validate?request.username=$requestusername&request.password=$requestpassword", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function getv1recoverymetadata()
    {
// Get metadata for forgot endpoints
        $data = $this->getRequestWithCookie("https://auth.roblox.com/v1/recovery/metadata", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function getv1samlmetadata()
    {
// Gets the SAML2 metadata XML.
        $data = $this->getRequestWithCookie("https://auth.roblox.com/v1/saml/metadata", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function getv1socialconnectedproviders()
    {
// Get social network user information if the given social auth method is connected to current user.
        $data = $this->getRequestWithCookie("https://auth.roblox.com/v1/social/connected-providers", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function getv1twostepverificationmetadata()
    {
// Get metadata for two step verification
        $data = $this->getRequestWithCookie("https://auth.roblox.com/v1/twostepverification/metadata", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function getv1usernames($getusername)
    {
// Gets a list of existing usernames on Roblox based on the query parameters
        $data = $this->getRequestWithCookie("https://auth.roblox.com/v1/usernames?username=$username", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function getv1usernamesvalidate($getrequestusername, $getrequestbirthday, $getrequestcontext)
    {
// Checks if a username is valid.
        $data = $this->getRequestWithCookie("https://auth.roblox.com/v1/usernames/validate?request.username=$requestusername&request.birthday=$requestbirthday&request.context=$requestcontext", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function getv1validatorsemail($getrequestBodyemail)
    {
// Tries to check if an email is valid
        $data = $this->getRequestWithCookie("https://auth.roblox.com/v1/validators/email?requestBody.email=$requestBodyemail", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function getv1validatorsusername($getrequestBodyusername, $getrequestBodybirthday)
    {
// Tries to get a valid username if the current username is taken
        $data = $this->getRequestWithCookie("https://auth.roblox.com/v1/validators/username?requestBody.username=$requestBodyusername&requestBody.birthday=$requestBodybirthday", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function getv1xboxconnection()
    {
// Check if the current user has an Xbox connected.
        $data = $this->getRequestWithCookie("https://auth.roblox.com/v1/xbox/connection", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function postv1accountpinlock()
    {
        // Request to locks the account which has an account pin enabled.

        $body = json_encode(
    array());
        $data = $this->postRequestWithCookie("https://auth.roblox.com/v1/account/pin/lock", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"post"]);
        return $data;
    }
    public function postv1accountpinunlock($pin)
    {
        // Requests to unlock the account pin.

        $body = json_encode(
    array());
        $data = $this->postRequestWithCookie("https://auth.roblox.com/v1/account/pin/unlock", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"post"]);
        return $data;
    }
    public function postv1credentialsverificationsend($credentialType, $credentialValue, $password)
    {
        // Sends a verification message to the provided credentials.

        $body = json_encode(
    array(
    ["credentialType"]=>"$credentialType",
    ["credentialValue"]=>"$credentialValue",
    ["password"]=>"$password",));
        $data = $this->postRequestWithCookie("https://auth.roblox.com/v1/credentials/verification/send", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"post"]);
        return $data;
    }
    public function postv1login($ctype, $cvalue, $password)
    {
        // Authenticates a user.

        $body = json_encode(
    array(
    ["ctype"]=>"$ctype",
    ["cvalue"]=>"$cvalue",
    ["password"]=>"$password",));
        $data = $this->postRequestWithCookie("https://auth.roblox.com/v1/login", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"post"]);
        return $data;
    }
    public function postv1logout()
    {
        // Destroys the current authentication session.

        $body = json_encode(
    array());
        $data = $this->postRequestWithCookie("https://auth.roblox.com/v1/logout", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"post"]);
        return $data;
    }
    public function postv1samllogin()
    {
        // Authenticates a user for a service through SAML2.

        $body = json_encode(
    array());
        $data = $this->postRequestWithCookie("https://auth.roblox.com/v1/saml/login", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"post"]);
        return $data;
    }
    public function postv1signup($username, $password, $gender, $birthday, $isTosAgreementBoxChecked, $email, $locale)
    {
        // Endpoint for signing up a new user

        $body = json_encode(
    array(
    ["username"]=>"$username",
    ["password"]=>"$password",
    ["gender"]=>"$gender",
    ["birthday"]=>"$birthday",
    ["isTosAgreementBoxChecked"]=>"$isTosAgreementBoxChecked",
    ["email"]=>"$email",
    ["locale"]=>"$locale",));
        $data = $this->postRequestWithCookie("https://auth.roblox.com/v1/signup", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"post"]);
        return $data;
    }
    public function postv1socialdisconnect($getprovider)
    {
        // Remove the given social provider auth method from current Roblox user if it is connected.

        $body = json_encode(
    array());
        $data = $this->postRequestWithCookie("https://auth.roblox.com/v1/social/$getprovider/disconnect", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"post"]);
        return $data;
    }
    public function postv1twostepverificationresend($username, $ticket, $actionType)
    {
        // Resends a two step verification code.

        $body = json_encode(
    array(
    ["username"]=>"$username",
    ["ticket"]=>"$ticket",
    ["actionType"]=>"$actionType",));
        $data = $this->postRequestWithCookie("https://auth.roblox.com/v1/twostepverification/resend", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"post"]);
        return $data;
    }
    public function postv1twostepverificationverify($username, $ticket, $code, $rememberDevice, $actionType)
    {
        // Verifies a two step verification code.

        $body = json_encode(
    array(
    ["username"]=>"$username",
    ["ticket"]=>"$ticket",
    ["code"]=>"$code",
    ["rememberDevice"]=>"$rememberDevice",
    ["actionType"]=>"$actionType",));
        $data = $this->postRequestWithCookie("https://auth.roblox.com/v1/twostepverification/verify", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"post"]);
        return $data;
    }
    public function postv1userpasswordschange($currentPassword, $newPassword)
    {
        // Changes the password for the authenticated user.

        $body = json_encode(
    array(
    ["currentPassword"]=>"$currentPassword",
    ["newPassword"]=>"$newPassword",));
        $data = $this->postRequestWithCookie("https://auth.roblox.com/v1/user/passwords/change", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"post"]);
        return $data;
    }
    public function postv1usernamesrecover($targetType, $target)
    {
        // Sends an email of all accounts belonging to an email

        $body = json_encode(
    array(
    ["targetType"]=>"$targetType",
    ["target"]=>"$target",));
        $data = $this->postRequestWithCookie("https://auth.roblox.com/v1/usernames/recover", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"post"]);
        return $data;
    }
    public function postv1xboxdisconnect()
    {
        // Unlink the current ROBLOX account from the Xbox live account.

        $body = json_encode(
    array());
        $data = $this->postRequestWithCookie("https://auth.roblox.com/v1/xbox/disconnect", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"post"]);
        return $data;
    }
    
// Authentication Api v2

public function getv2authmetadata()
    {
// Gets Auth meta data
        $data = $this->getRequestWithCookie("https://auth.roblox.com/v2/auth/metadata", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function getv2credentialsverification($getrequestcredentialType, $getrequestcredentialValue, $getrequestpassword)
    {
// Checks if it is possible to send a verification message for the provided credentials.
        $data = $this->getRequestWithCookie("https://auth.roblox.com/v2/credentials/verification?request.credentialType=$requestcredentialType&request.credentialValue=$requestcredentialValue&request.password=$requestpassword", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function getv2passwordscurrentstatus()
    {
// Returns password status for current user.
        $data = $this->getRequestWithCookie("https://auth.roblox.com/v2/passwords/current-status", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function getv2passwordsvalidate($getrequestusername, $getrequestpassword)
    {
// Endpoint for checking if a password is valid.
        $data = $this->getRequestWithCookie("https://auth.roblox.com/v2/passwords/validate?request.username=$requestusername&request.password=$requestpassword", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function getv2recoverymetadata()
    {
// Get metadata for forgot endpoints
        $data = $this->getRequestWithCookie("https://auth.roblox.com/v2/recovery/metadata", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function getv2samlmetadata()
    {
// Gets the SAML2 metadata XML.
        $data = $this->getRequestWithCookie("https://auth.roblox.com/v2/saml/metadata", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function getv2twostepverificationmetadata()
    {
// Get metadata for two step verification
        $data = $this->getRequestWithCookie("https://auth.roblox.com/v2/twostepverification/metadata", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function getv2usernames($getusername)
    {
// Gets a list of existing usernames on Roblox based on the query parameters
        $data = $this->getRequestWithCookie("https://auth.roblox.com/v2/usernames?username=$username", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function getv2usernamesvalidate($getrequestusername, $getrequestbirthday, $getrequestcontext)
    {
// Checks if a username is valid.
        $data = $this->getRequestWithCookie("https://auth.roblox.com/v2/usernames/validate?request.username=$requestusername&request.birthday=$requestbirthday&request.context=$requestcontext", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function postv2credentialsverificationsend($credentialType, $credentialValue, $password)
    {
        // Sends a verification message to the provided credentials.

        $body = json_encode(
    array(
    ["credentialType"]=>"$credentialType",
    ["credentialValue"]=>"$credentialValue",
    ["password"]=>"$password",));
        $data = $this->postRequestWithCookie("https://auth.roblox.com/v2/credentials/verification/send", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"post"]);
        return $data;
    }
    public function postv2login($ctype, $cvalue, $password)
    {
        // Authenticates a user.

        $body = json_encode(
    array(
    ["ctype"]=>"$ctype",
    ["cvalue"]=>"$cvalue",
    ["password"]=>"$password",));
        $data = $this->postRequestWithCookie("https://auth.roblox.com/v2/login", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"post"]);
        return $data;
    }
    public function postv2logout()
    {
        // Destroys the current authentication session.

        $body = json_encode(
    array());
        $data = $this->postRequestWithCookie("https://auth.roblox.com/v2/logout", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"post"]);
        return $data;
    }
    public function postv2passwordsreset($ticket, $password)
    {
        // Resets a password for a user that belongs to the password reset ticket.

        $body = json_encode(
    array(
    ["ticket"]=>"$ticket",
    ["password"]=>"$password",));
        $data = $this->postRequestWithCookie("https://auth.roblox.com/v2/passwords/reset", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"post"]);
        return $data;
    }
    public function postv2passwordsresetsend($targetType, $target)
    {
        // Sends a password reset challenge to the specified target.

        $body = json_encode(
    array(
    ["targetType"]=>"$targetType",
    ["target"]=>"$target",));
        $data = $this->postRequestWithCookie("https://auth.roblox.com/v2/passwords/reset/send", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"post"]);
        return $data;
    }
    public function postv2passwordsresetverify($nonce, $code)
    {
        // Verifies a challenge solution.

        $body = json_encode(
    array(
    ["nonce"]=>"$nonce",
    ["code"]=>"$code",));
        $data = $this->postRequestWithCookie("https://auth.roblox.com/v2/passwords/reset/verify", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"post"]);
        return $data;
    }
    public function postv2samllogin()
    {
        // Authenticates a user for a service through SAML2.

        $body = json_encode(
    array());
        $data = $this->postRequestWithCookie("https://auth.roblox.com/v2/saml/login", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"post"]);
        return $data;
    }
    public function postv2signup($username, $password, $gender, $birthday, $isTosAgreementBoxChecked, $email, $locale)
    {
        // Endpoint for signing up a new user

        $body = json_encode(
    array(
    ["username"]=>"$username",
    ["password"]=>"$password",
    ["gender"]=>"$gender",
    ["birthday"]=>"$birthday",
    ["isTosAgreementBoxChecked"]=>"$isTosAgreementBoxChecked",
    ["email"]=>"$email",
    ["locale"]=>"$locale",));
        $data = $this->postRequestWithCookie("https://auth.roblox.com/v2/signup", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"post"]);
        return $data;
    }
    public function postv2twostepverificationresend($username, $ticket, $actionType)
    {
        // Resends a two step verification code.

        $body = json_encode(
    array(
    ["username"]=>"$username",
    ["ticket"]=>"$ticket",
    ["actionType"]=>"$actionType",));
        $data = $this->postRequestWithCookie("https://auth.roblox.com/v2/twostepverification/resend", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"post"]);
        return $data;
    }
    public function postv2twostepverificationverify($username, $ticket, $code, $rememberDevice, $actionType)
    {
        // Verifies a two step verification code.

        $body = json_encode(
    array(
    ["username"]=>"$username",
    ["ticket"]=>"$ticket",
    ["code"]=>"$code",
    ["rememberDevice"]=>"$rememberDevice",
    ["actionType"]=>"$actionType",));
        $data = $this->postRequestWithCookie("https://auth.roblox.com/v2/twostepverification/verify", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"post"]);
        return $data;
    }
    public function postv2userpasswordschange($currentPassword, $newPassword)
    {
        // Changes the password for the authenticated user.

        $body = json_encode(
    array(
    ["currentPassword"]=>"$currentPassword",
    ["newPassword"]=>"$newPassword",));
        $data = $this->postRequestWithCookie("https://auth.roblox.com/v2/user/passwords/change", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"post"]);
        return $data;
    }
    public function postv2usernamesrecover($targetType, $target)
    {
        // Sends an email of all accounts belonging to an email

        $body = json_encode(
    array(
    ["targetType"]=>"$targetType",
    ["target"]=>"$target",));
        $data = $this->postRequestWithCookie("https://auth.roblox.com/v2/usernames/recover", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"post"]);
        return $data;
    }
    
// AccountSettings Api v1

public function getv1accountsettingssettingsgroups()
    {
// Used by the site and mobile apps to determine titles and locations of  settings groups such as "Notifications" and "Billing"
        $data = $this->getRequestWithCookie("https://accountsettings.roblox.com/v1/account/settings/settings-groups", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function getv1appchatprivacy()
    {
// Get a user's app chat privacy setting
        $data = $this->getRequestWithCookie("https://accountsettings.roblox.com/v1/app-chat-privacy", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function postv1appchatprivacy($appChatPrivacy)
    {
        // Updates a user's app chat privacy setting

        $body = json_encode(
    array());
        $data = $this->postRequestWithCookie("https://accountsettings.roblox.com/v1/app-chat-privacy", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"post"]);
        return $data;
    }
    public function getv1email()
    {
// Gets the authenticated user's email address and verified status
        $data = $this->getRequestWithCookie("https://accountsettings.roblox.com/v1/email", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function postv1email($password, $emailAddress)
    {
        // Updates the authenticated user's email address

        $body = json_encode(
    array());
        $data = $this->postRequestWithCookie("https://accountsettings.roblox.com/v1/email", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"post"]);
        return $data;
    }
    public function patchv1email($password, $emailAddress)
    {
        // Updates the authenticated user's email address

        $body = json_encode(
    array());
        $data = $this->postRequestWithCookie("https://accountsettings.roblox.com/v1/email", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"patch"]);
        return $data;
    }
    public function getv1gamechatprivacy()
    {
// Get a user's game chat privacy setting
        $data = $this->getRequestWithCookie("https://accountsettings.roblox.com/v1/game-chat-privacy", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function postv1gamechatprivacy($gameChatPrivacy)
    {
        // Updates a user's game chat privacy setting

        $body = json_encode(
    array());
        $data = $this->postRequestWithCookie("https://accountsettings.roblox.com/v1/game-chat-privacy", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"post"]);
        return $data;
    }
    public function getv1inventoryprivacy()
    {
// Get a user's inventory privacy setting
        $data = $this->getRequestWithCookie("https://accountsettings.roblox.com/v1/inventory-privacy", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function postv1inventoryprivacy($inventoryPrivacy)
    {
        // Updates a user's inventory privacy setting

        $body = json_encode(
    array());
        $data = $this->postRequestWithCookie("https://accountsettings.roblox.com/v1/inventory-privacy", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"post"]);
        return $data;
    }
    public function getv1privacy()
    {
// Gets a user's privacy settings.
        $data = $this->getRequestWithCookie("https://accountsettings.roblox.com/v1/privacy", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function patchv1privacy($phoneDiscovery)
    {
        // Updates a user's privacy settings.

        $body = json_encode(
    array());
        $data = $this->postRequestWithCookie("https://accountsettings.roblox.com/v1/privacy", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"patch"]);
        return $data;
    }
    public function getv1privacyinfo()
    {
// Gets a user's privacy settings info.
        $data = $this->getRequestWithCookie("https://accountsettings.roblox.com/v1/privacy/info", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function getv1privatemessageprivacy()
    {
// Get a user's private message privacy setting
        $data = $this->getRequestWithCookie("https://accountsettings.roblox.com/v1/private-message-privacy", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function postv1privatemessageprivacy($privateMessagePrivacy)
    {
        // Updates a user's private message privacy setting

        $body = json_encode(
    array());
        $data = $this->postRequestWithCookie("https://accountsettings.roblox.com/v1/private-message-privacy", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"post"]);
        return $data;
    }
    public function getv1themes($getconsumerType, $getconsumerId)
    {
// returns the theme type for a specific consumer.
        $data = $this->getRequestWithCookie("https://accountsettings.roblox.com/v1/themes/$getconsumerType/$getconsumerId", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function patchv1themes($themeType, $getconsumerType, $getconsumerId)
    {
        // Modify the theme type for consumer.

        $body = json_encode(
    array(
    ["themeType"]=>"$themeType",));
        $data = $this->postRequestWithCookie("https://accountsettings.roblox.com/v1/themes/$getconsumerType/$getconsumerId", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"patch"]);
        return $data;
    }
    public function getv1themestypes()
    {
// returns all the enabled theme types.
        $data = $this->getRequestWithCookie("https://accountsettings.roblox.com/v1/themes/types", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function getv1tradeprivacy()
    {
// Get a user's trade privacy setting
        $data = $this->getRequestWithCookie("https://accountsettings.roblox.com/v1/trade-privacy", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function postv1tradeprivacy($tradePrivacy)
    {
        // Updates a user's trade privacy setting

        $body = json_encode(
    array());
        $data = $this->postRequestWithCookie("https://accountsettings.roblox.com/v1/trade-privacy", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"post"]);
        return $data;
    }
    public function getv1tradevalue()
    {
// Get a user's trade quality filter setting
        $data = $this->getRequestWithCookie("https://accountsettings.roblox.com/v1/trade-value", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function postv1tradevalue($tradeValue)
    {
        // Updates a user's trade quality filter setting

        $body = json_encode(
    array());
        $data = $this->postRequestWithCookie("https://accountsettings.roblox.com/v1/trade-value", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"post"]);
        return $data;
    }
    public function getv1twostepverification()
    {
// Retrieves if a user has two-step verification enabled or not.
        $data = $this->getRequestWithCookie("https://accountsettings.roblox.com/v1/twostepverification", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function patchv1twostepverification($enabled, $password)
    {
        // Sets the user's two-step verification setting.

        $body = json_encode(
    array());
        $data = $this->postRequestWithCookie("https://accountsettings.roblox.com/v1/twostepverification", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"patch"]);
        return $data;
    }
    public function getv1userscreenscontactupsell()
    {
// Determines if the contact (e.g. email or phone) upsell screen should be shown to the current user and gets data related to it
        $data = $this->getRequestWithCookie("https://accountsettings.roblox.com/v1/user/screens/contact-upsell", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function getv1xboxisusernamevalid($getAuthorization, $getSignature, $getrequestusername)
    {
// Determines whether the username requested is valid.
        $data = $this->getRequestWithCookie("https://accountsettings.roblox.com/v1/xbox/is-username-valid?request.username=$requestusername", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function postv1emailverify($freeItem)
    {
        // Send verify email to the authenticated user's email address

        $body = json_encode(
    array(
    ["freeItem"]=>"$freeItem",));
        $data = $this->postRequestWithCookie("https://accountsettings.roblox.com/v1/email/verify", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"post"]);
        return $data;
    }
    public function postv1promotionchannels($facebook, $twitter, $youtube, $twitch, $socialNetworksVisibilityPrivacy)
    {
        // Updates a user's promotion channels and their visibility settings on their profile

        $body = json_encode(
    array(
    ["facebook"]=>"$facebook",
    ["twitter"]=>"$twitter",
    ["youtube"]=>"$youtube",
    ["twitch"]=>"$twitch",
    ["socialNetworksVisibilityPrivacy"]=>"$socialNetworksVisibilityPrivacy",));
        $data = $this->postRequestWithCookie("https://accountsettings.roblox.com/v1/promotion-channels", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"post"]);
        return $data;
    }
    public function postv1userscreenscontactupsellsuppress($getsuppress)
    {
        // Suppresses the ContactUpsell screen for the authenticated user

        $body = json_encode(
    array());
        $data = $this->postRequestWithCookie("https://accountsettings.roblox.com/v1/user/screens/contact-upsell/suppress?suppress=$suppress", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"post"]);
        return $data;
    }
    
// AccountSettings Api v2

public function getv2twostepverification()
    {
// Retrieves the Two-Step Verification Setting
        $data = $this->getRequestWithCookie("https://accountsettings.roblox.com/v2/twostepverification", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function postv2twostepverification($enabled, $password)
    {
        // Sets the Two-Step Verification Setting.            Account password is required to disable 2SV.

        $body = json_encode(
    array());
        $data = $this->postRequestWithCookie("https://accountsettings.roblox.com/v2/twostepverification", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"post"]);
        return $data;
    }
    public function patchv2twostepverification($enabled, $password)
    {
        // Sets the Two-Step Verification Setting.            Account password is required to disable 2SV.

        $body = json_encode(
    array());
        $data = $this->postRequestWithCookie("https://accountsettings.roblox.com/v2/twostepverification", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"patch"]);
        return $data;
    }
    
// Presence Api v1

public function postv1presenceregisterapppresence($getlocation)
    {
        // Register User Presence for IOS, Android and Xbox app

        $body = json_encode(
    array());
        $data = $this->postRequestWithCookie("https://presence.roblox.com/v1/presence/register-app-presence?location=$location", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"post"]);
        return $data;
    }
    public function postv1presenceusers()
    {
        // Get Presence for a list of usersTODO - use locale specific Presence resources to get Presence locationType

        $body = json_encode(
    array());
        $data = $this->postRequestWithCookie("https://presence.roblox.com/v1/presence/users", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"post"]);
        return $data;
    }
    
// Notifications Api v2

public function getv2notificationsgetrolloutsettings($getfeatureNames)
    {
// Gets the notification settings related to rollout
        $data = $this->getRequestWithCookie("https://notifications.roblox.com/v2/notifications/get-rollout-settings?featureNames=$featureNames", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function getv2notificationsgetsettings()
    {
// Gets settings related to notifications for the signed in user
        $data = $this->getRequestWithCookie("https://notifications.roblox.com/v2/notifications/get-settings", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function getv2notificationssettingsrealtime()
    {
// Gets the notification settings related to realtime
        $data = $this->getRequestWithCookie("https://notifications.roblox.com/v2/notifications/settings/realtime", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function getv2pushnotificationschromemanifest()
    {
// Get Chrome Manifest to link GCM project to Chrome Browser
        $data = $this->getRequestWithCookie("https://notifications.roblox.com/v2/push-notifications/chrome-manifest", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function getv2pushnotificationsgetcurrentdevicedestination()
    {
// Gets the current device destination
        $data = $this->getRequestWithCookie("https://notifications.roblox.com/v2/push-notifications/get-current-device-destination", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function getv2pushnotificationsgetdestinations()
    {
// Gets valid destinations associated with the signed user
        $data = $this->getRequestWithCookie("https://notifications.roblox.com/v2/push-notifications/get-destinations", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function getv2pushnotificationsmetadata($getnotificationToken, $getnotificationId)
    {
// Gets the corresponding metadata for the specified notification
        $data = $this->getRequestWithCookie("https://notifications.roblox.com/v2/push-notifications/metadata?notificationToken=$notificationToken&notificationId=$notificationId", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function getv2pushnotificationsnotificationids($getnotificationToken, $getlimit, $getcursor)
    {
// Gets the notificationIds for the specified notification token
        $data = $this->getRequestWithCookie("https://notifications.roblox.com/v2/push-notifications/notification-ids?notificationToken=$notificationToken&limit=$limit&cursor=$cursor", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function getv2realtimemetadata()
    {
// Get Realtime Client side required information
        $data = $this->getRequestWithCookie("https://notifications.roblox.com/v2/realtime/metadata", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function getv2streamnotificationsgetlatestgameupdates($getuniverseIds, $getsinceDateTime)
    {
        $data = $this->getRequestWithCookie("https://notifications.roblox.com/v2/stream-notifications/get-latest-game-updates?universeIds=$universeIds&sinceDateTime=$sinceDateTime", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function getv2streamnotificationsgetpromptsettings()
    {
// Gets the Notification stream prompt settings
        $data = $this->getRequestWithCookie("https://notifications.roblox.com/v2/stream-notifications/get-prompt-settings", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function getv2streamnotificationsgetrecent($getstartIndex, $getmaxRows)
    {
// Gets the recent entries from the notification stream
        $data = $this->getRequestWithCookie("https://notifications.roblox.com/v2/stream-notifications/get-recent?startIndex=$startIndex&maxRows=$maxRows", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function getv2streamnotificationsunreadcount()
    {
// Gets the count of unread Notification stream entries
        $data = $this->getRequestWithCookie("https://notifications.roblox.com/v2/stream-notifications/unread-count", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function postv2notificationsnotificationsourcetypesallow($sourceType)
    {
        // Allows the specified notification source types

        $body = json_encode(
    array());
        $data = $this->postRequestWithCookie("https://notifications.roblox.com/v2/notifications/notification-source-types/allow", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"post"]);
        return $data;
    }
    public function postv2notificationsnotificationsourcetypesoptout($sourceType)
    {
        // Opts out from the specified notification source types

        $body = json_encode(
    array());
        $data = $this->postRequestWithCookie("https://notifications.roblox.com/v2/notifications/notification-source-types/opt-out", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"post"]);
        return $data;
    }
    public function postv2notificationsreceiverdestinationtypesallow($destinationType)
    {
        // Allows the specified notification receiver destination types

        $body = json_encode(
    array());
        $data = $this->postRequestWithCookie("https://notifications.roblox.com/v2/notifications/receiver-destination-types/allow", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"post"]);
        return $data;
    }
    public function postv2notificationsreceiverdestinationtypesoptout($destinationType)
    {
        // Opts out from the specified notification receiver destination types

        $body = json_encode(
    array());
        $data = $this->postRequestWithCookie("https://notifications.roblox.com/v2/notifications/receiver-destination-types/opt-out", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"post"]);
        return $data;
    }
    public function postv2notificationsupdatedestinationsetting($notificationSourceType, $destinationId, $isEnabled)
    {
        // Updates the notification destination setting

        $body = json_encode(
    array());
        $data = $this->postRequestWithCookie("https://notifications.roblox.com/v2/notifications/update-destination-setting", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"post"]);
        return $data;
    }
    public function postv2notificationsupdatenotificationsettings($notificationSourceType, $receiverDestinationType, $isEnabled)
    {
        // Updated the notification band settings

        $body = json_encode(
    array());
        $data = $this->postRequestWithCookie("https://notifications.roblox.com/v2/notifications/update-notification-settings", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"post"]);
        return $data;
    }
    public function postv2pushnotificationsderegisteralldevices()
    {
        // De-register all devices to disable push notifications

        $body = json_encode(
    array());
        $data = $this->postRequestWithCookie("https://notifications.roblox.com/v2/push-notifications/deregister-all-devices", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"post"]);
        return $data;
    }
    public function postv2pushnotificationsderegistercurrentdevice()
    {
        // De-register current device to diable push notifications

        $body = json_encode(
    array());
        $data = $this->postRequestWithCookie("https://notifications.roblox.com/v2/push-notifications/deregister-current-device", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"post"]);
        return $data;
    }
    public function postv2pushnotificationsmarkasread($platformType, $notificationId)
    {
        // Marks the specified notification as read.

        $body = json_encode(
    array());
        $data = $this->postRequestWithCookie("https://notifications.roblox.com/v2/push-notifications/mark-as-read", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"post"]);
        return $data;
    }
    public function postv2pushnotificationsmarkcategoryasread($notificationType, $category, $latestNotificationId)
    {
        // Mark all notifications in the specified stacking category up until the specified date read

        $body = json_encode(
    array());
        $data = $this->postRequestWithCookie("https://notifications.roblox.com/v2/push-notifications/mark-category-as-read", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"post"]);
        return $data;
    }
    public function postv2pushnotificationsmarkinteraction($platformType, $notificationToken, $notificationId, $interactionType)
    {
        // Marks Interaction status for push notification

        $body = json_encode(
    array());
        $data = $this->postRequestWithCookie("https://notifications.roblox.com/v2/push-notifications/mark-interaction", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"post"]);
        return $data;
    }
    public function postv2pushnotificationsregisterandroidamazon($notificationToken, $authorizeForUser, $oldNotificationToken, $deviceName)
    {
        // Register Amazon Android for push notifications

        $body = json_encode(
    array());
        $data = $this->postRequestWithCookie("https://notifications.roblox.com/v2/push-notifications/register-android-amazon", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"post"]);
        return $data;
    }
    public function postv2pushnotificationsregisterandroidnative($notificationToken, $authorizeForUser, $oldNotificationToken, $deviceName)
    {
        // Register Android Native for push notifications

        $body = json_encode(
    array());
        $data = $this->postRequestWithCookie("https://notifications.roblox.com/v2/push-notifications/register-android-native", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"post"]);
        return $data;
    }
    public function postv2pushnotificationsregisterchrome($notificationToken, $initiatedByUser)
    {
        // Registers Chrome for push notifications

        $body = json_encode(
    array());
        $data = $this->postRequestWithCookie("https://notifications.roblox.com/v2/push-notifications/register-chrome", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"post"]);
        return $data;
    }
    public function postv2pushnotificationsregisterfirefox($notificationToken, $notificationEndpoint, $initiatedByUser)
    {
        // Registers Firefox for push notifications

        $body = json_encode(
    array());
        $data = $this->postRequestWithCookie("https://notifications.roblox.com/v2/push-notifications/register-firefox", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"post"]);
        return $data;
    }
    public function postv2pushnotificationsregisteriosnative($notificationToken, $destinationIdentifier, $authorizeForUser, $oldNotificationToken, $deviceName)
    {
        // Registers IOS device for push notifications

        $body = json_encode(
    array());
        $data = $this->postRequestWithCookie("https://notifications.roblox.com/v2/push-notifications/register-ios-native", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"post"]);
        return $data;
    }
    public function postv2streamnotificationsclearunread()
    {
        // Clears the unread Notification stream count

        $body = json_encode(
    array());
        $data = $this->postRequestWithCookie("https://notifications.roblox.com/v2/stream-notifications/clear-unread", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"post"]);
        return $data;
    }
    public function postv2streamnotificationsgameupdatenotificationinteracted($universeId, $createdOnKey, $interactionType, $currentUserId)
    {
        // Sends metrics for when a Game Update Notification as Interacted. This differs from an the MarkStreamEntryInteracted function because it comes from an interaction              on the Game Update Notifications section not the aggregated Notification Stream view

        $body = json_encode(
    array());
        $data = $this->postRequestWithCookie("https://notifications.roblox.com/v2/stream-notifications/game-update-notification-interacted", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"post"]);
        return $data;
    }
    public function postv2streamnotificationsgameupdatenotificationread($universeId, $createdOn, $currentUserId)
    {
        // Sends metrics when a Game Update Notification is Read from the Game Update Notifications Section of the Notification Stream

        $body = json_encode(
    array());
        $data = $this->postRequestWithCookie("https://notifications.roblox.com/v2/stream-notifications/game-update-notification-read", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"post"]);
        return $data;
    }
    public function postv2streamnotificationsmarkinteracted($eventId)
    {
        // Marks a Notification Stream Entry as Interacted

        $body = json_encode(
    array());
        $data = $this->postRequestWithCookie("https://notifications.roblox.com/v2/stream-notifications/mark-interacted", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"post"]);
        return $data;
    }
    public function postv2streamnotificationssuppressprompt()
    {
        // Supresses the notification stream prompt

        $body = json_encode(
    array());
        $data = $this->postRequestWithCookie("https://notifications.roblox.com/v2/stream-notifications/suppress-prompt", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"post"]);
        return $data;
    }
    
// Games Api v1

public function getv1games($getuniverseIds)
    {
// Gets a list of games' detail
        $data = $this->getRequestWithCookie("https://games.roblox.com/v1/games?universeIds=$universeIds", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function getv1gamesservers($getplaceId, $getserverType, $getsortOrder, $getlimit, $getcursor)
    {
// Get the game server list
        $data = $this->getRequestWithCookie("https://games.roblox.com/v1/games/$getplaceId/servers/$getserverType?sortOrder=$sortOrder&limit=$limit&cursor=$cursor", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function getv1gamesfavorites($getuniverseId)
    {
// Returns if a game was marked as favorite for the authenticated user
        $data = $this->getRequestWithCookie("https://games.roblox.com/v1/games/$getuniverseId/favorites", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function postv1gamesfavorites($getuniverseId, $isFavorited)
    {
        // Favors (or unfavors) a game for the authenticated user

        $body = json_encode(
    array(
    ["isFavorited"]=>"$isFavorited",));
        $data = $this->postRequestWithCookie("https://games.roblox.com/v1/games/$getuniverseId/favorites", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"post"]);
        return $data;
    }
    public function getv1gamesfavoritescount($getuniverseId)
    {
// Get the favorites count of the a specific game
        $data = $this->getRequestWithCookie("https://games.roblox.com/v1/games/$getuniverseId/favorites/count", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function getv1gamesgamepasses($getuniverseId, $getsortOrder, $getlimit, $getcursor)
    {
// Get the game's game passes
        $data = $this->getRequestWithCookie("https://games.roblox.com/v1/games/$getuniverseId/game-passes?sortOrder=$sortOrder&limit=$limit&cursor=$cursor", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function getv1gamesmedia($getuniverseId)
    {
// Get the game media data
        $data = $this->getRequestWithCookie("https://games.roblox.com/v1/games/$getuniverseId/media", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function getv1gamesvotes($getuniverseId)
    {
// Get the game vote status
        $data = $this->getRequestWithCookie("https://games.roblox.com/v1/games/$getuniverseId/votes", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function getv1gamesvotesuser($getuniverseId)
    {
// Get the user's vote status for a game
        $data = $this->getRequestWithCookie("https://games.roblox.com/v1/games/$getuniverseId/votes/user", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function getv1gamesgamesproductinfo($getuniverseIds)
    {
// Gets a list of games' product info, used to purchase a game
        $data = $this->getRequestWithCookie("https://games.roblox.com/v1/games/games-product-info?universeIds=$universeIds", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function getv1gamesgamethumbnail($getimageToken, $getheight, $getwidth)
    {
// Get a single game thumbnail
        $data = $this->getRequestWithCookie("https://games.roblox.com/v1/games/game-thumbnail?imageToken=$imageToken&height=$height&width=$width", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function getv1gamesgamethumbnails($getimageTokens, $getheight, $getwidth)
    {
// Gets a list of game thumbnails
        $data = $this->getRequestWithCookie("https://games.roblox.com/v1/games/game-thumbnails?imageTokens=$imageTokens&height=$height&width=$width", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function getv1gameslist($getmodelsortToken, $getmodelgameFilter, $getmodeltimeFilter, $getmodelgenreFilter, $getmodelexclusiveStartId, $getmodelsortOrder, $getmodelgameSetTargetId, $getmodelkeyword, $getmodelstartRows, $getmodelmaxRows, $getmodelisKeywordSuggestionEnabled, $getmodelcontextCountryRegionId, $getmodelcontextUniverseId)
    {
// Gets a list of games
        $data = $this->getRequestWithCookie("https://games.roblox.com/v1/games/list?model.sortToken=$modelsortToken&model.gameFilter=$modelgameFilter&model.timeFilter=$modeltimeFilter&model.genreFilter=$modelgenreFilter&model.exclusiveStartId=$modelexclusiveStartId&model.sortOrder=$modelsortOrder&model.gameSetTargetId=$modelgameSetTargetId&model.keyword=$modelkeyword&model.startRows=$modelstartRows&model.maxRows=$modelmaxRows&model.isKeywordSuggestionEnabled=$modelisKeywordSuggestionEnabled&model.contextCountryRegionId=$modelcontextCountryRegionId&model.contextUniverseId=$modelcontextUniverseId", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function getv1gamesmultigetplacedetails($getplaceIds)
    {
// Get place details
        $data = $this->getRequestWithCookie("https://games.roblox.com/v1/games/multiget-place-details?placeIds=$placeIds", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function getv1gamesmultigetplayabilitystatus($getuniverseIds)
    {
// Gets a list of universe playability statuses for the authenticated user
        $data = $this->getRequestWithCookie("https://games.roblox.com/v1/games/multiget-playability-status?universeIds=$universeIds", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function getv1gamesrecommendationsalgorithm($getalgorithmName, $getmodelpaginationKey, $getmodelmaxRows)
    {
// Get games recommendations
        $data = $this->getRequestWithCookie("https://games.roblox.com/v1/games/recommendations/algorithm/$getalgorithmName?model.paginationKey=$modelpaginationKey&model.maxRows=$modelmaxRows", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function getv1gamesrecommendationsgame($getuniverseId, $getmodelpaginationKey, $getmodelmaxRows)
    {
// Get games recommendations based on a given universe
        $data = $this->getRequestWithCookie("https://games.roblox.com/v1/games/recommendations/game/$getuniverseId?model.paginationKey=$modelpaginationKey&model.maxRows=$modelmaxRows", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function getv1gamessorts($getmodelgameSortsContext)
    {
// Gets an ordered list of all sorts
        $data = $this->getRequestWithCookie("https://games.roblox.com/v1/games/sorts?model.gameSortsContext=$modelgameSortsContext", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function getv1gamesvotes2($getuniverseIds)
    {
// Gets a list of universe vote status
        $data = $this->getRequestWithCookie("https://games.roblox.com/v1/games/votes?universeIds=$universeIds", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function getv1vipservers($getid)
    {
// Get necessary data to generate webpage
        $data = $this->getRequestWithCookie("https://games.roblox.com/v1/vip-servers/$getid", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function patchv1vipservers($getid, $name, $newJoinCode, $active)
    {
        // Updates vip server

        $body = json_encode(
    array(
    ["name"]=>"$name",
    ["newJoinCode"]=>"$newJoinCode",
    ["active"]=>"$active",));
        $data = $this->postRequestWithCookie("https://games.roblox.com/v1/vip-servers/$getid", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"patch"]);
        return $data;
    }
    public function postv1gamesvipservers($getuniverseId, $name, $expectedPrice)
    {
        // Create VIP server for a game

        $body = json_encode(
    array());
        $data = $this->postRequestWithCookie("https://games.roblox.com/v1/games/vip-servers/$getuniverseId", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"post"]);
        return $data;
    }
    public function patchv1gamesuservotes($getuniverseId, $vote)
    {
        // Set the user's vote for a game

        $body = json_encode(
    array());
        $data = $this->postRequestWithCookie("https://games.roblox.com/v1/games/$getuniverseId/user-votes", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"patch"]);
        return $data;
    }
    public function patchv1vipserverspermissions($getid, $clanAllowed, $enemyClanId, $friendsAllowed)
    {
        // Update friend/clan access and allowed friends/clan list

        $body = json_encode(
    array(
    ["clanAllowed"]=>"$clanAllowed",
    ["enemyClanId"]=>"$enemyClanId",
    ["friendsAllowed"]=>"$friendsAllowed",));
        $data = $this->postRequestWithCookie("https://games.roblox.com/v1/vip-servers/$getid/permissions", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"patch"]);
        return $data;
    }
    public function patchv1vipserverssubscription($getid, $active, $price)
    {
        // Updates subscription status of a vip server

        $body = json_encode(
    array(
    ["active"]=>"$active",
    ["price"]=>"$price",));
        $data = $this->postRequestWithCookie("https://games.roblox.com/v1/vip-servers/$getid/subscription", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"patch"]);
        return $data;
    }
    
// Games Api v2

public function getv2groupsgames($getgroupId, $getaccessFilter, $getsortOrder, $getlimit, $getcursor)
    {
// Gets games created by the specified group.
        $data = $this->getRequestWithCookie("https://games.roblox.com/v2/groups/$getgroupId/games?accessFilter=$accessFilter&sortOrder=$sortOrder&limit=$limit&cursor=$cursor", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function getv2usersgames($getuserId, $getaccessFilter, $getsortOrder, $getlimit, $getcursor)
    {
// Gets games created by the specified user.
        $data = $this->getRequestWithCookie("https://games.roblox.com/v2/users/$getuserId/games?accessFilter=$accessFilter&sortOrder=$sortOrder&limit=$limit&cursor=$cursor", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    

// Followings Api v1

public function getv1usersuniverses($getuserId)
    {
// Gets all the followings between a user with {userId} and universes
        $data = $this->getRequestWithCookie("https://followings.roblox.com/v1/users/$getuserId/universes", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function getv1usersuniversesstatus($getuserId, $getuniverseId)
    {
// Gets the status of a following relationship between a user and a universe.
        $data = $this->getRequestWithCookie("https://followings.roblox.com/v1/users/$getuserId/universes/$getuniverseId/status", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function postv1usersuniverses($getuserId, $getuniverseId)
    {
        // Creates the following between a user with {userId} and universe with {universeId}

        $body = json_encode(
    array());
        $data = $this->postRequestWithCookie("https://followings.roblox.com/v1/users/$getuserId/universes/$getuniverseId", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"post"]);
        return $data;
    }
    public function deletev1usersuniverses($getuserId, $getuniverseId)
    {
        // Deletes the following between a user with {userId} and universe with {universeId}

        $body = json_encode(
    array());
        $data = $this->postRequestWithCookie("https://followings.roblox.com/v1/users/$getuserId/universes/$getuniverseId", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"delete"]);
        return $data;
    }
    
// Followings Api v2

public function getv2usersuniverses($getuserId)
    {
// Gets all universes followed by a user.
        $data = $this->getRequestWithCookie("https://followings.roblox.com/v2/users/$getuserId/universes", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    

// Catalog Api v1

public function getv1assetsbundles($getassetId, $getsortOrder, $getlimit, $getcursor)
    {
// Lists the bundles a particular asset belongs to. Use the Id of the last bundle in the response to get the next page.
        $data = $this->getRequestWithCookie("https://catalog.roblox.com/v1/assets/$getassetId/bundles?sortOrder=$sortOrder&limit=$limit&cursor=$cursor", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function getv1assettocategory()
    {
// Lists a mapping for assets to category IDs to convert from inventory ID to catalog ID. Creates a mapping to link 'Get More' button in inventory page to the relevant catalog page.
        $data = $this->getRequestWithCookie("https://catalog.roblox.com/v1/asset-to-category", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function getv1assettosubcategory()
    {
// Lists a mapping for assets to subcategory IDs to convert from inventory ID to catalog ID. Creates a mapping to link 'Get More' button in inventory page to the relevant catalog page.
        $data = $this->getRequestWithCookie("https://catalog.roblox.com/v1/asset-to-subcategory", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function getv1bundlesdetails($getbundleId)
    {
// Returns details about the given bundleId.
        $data = $this->getRequestWithCookie("https://catalog.roblox.com/v1/bundles/$getbundleId/details", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function getv1bundlesdetails2($getbundleIds)
    {
// Returns details about the given bundleIds.
        $data = $this->getRequestWithCookie("https://catalog.roblox.com/v1/bundles/details?bundleIds=$bundleIds", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function getv1categories()
    {
// Lists Category Names and their Ids
        $data = $this->getRequestWithCookie("https://catalog.roblox.com/v1/categories", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function getv1exclusiveitemsbundles($getappStoreType)
    {
// Lists the exclusive catalog items for a particular app store.
        $data = $this->getRequestWithCookie("https://catalog.roblox.com/v1/exclusive-items/$getappStoreType/bundles", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function getv1favoritesassetscount($getassetId)
    {
// Gets the favorite count for the given asset Id.
        $data = $this->getRequestWithCookie("https://catalog.roblox.com/v1/favorites/assets/$getassetId/count", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function getv1favoritesbundlescount($getbundleId)
    {
// Gets the favorite count for the given bundle Id.
        $data = $this->getRequestWithCookie("https://catalog.roblox.com/v1/favorites/bundles/$getbundleId/count", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function getv1favoritesusersassetsfavorite($getuserId, $getassetId)
    {
// Gets the favorite model for the asset and user.
        $data = $this->getRequestWithCookie("https://catalog.roblox.com/v1/favorites/users/$getuserId/assets/$getassetId/favorite", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function postv1favoritesusersassetsfavorite($getuserId, $getassetId)
    {
        // Create a favorite for an asset by the authenticated user.

        $body = json_encode(
    array());
        $data = $this->postRequestWithCookie("https://catalog.roblox.com/v1/favorites/users/$getuserId/assets/$getassetId/favorite", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"post"]);
        return $data;
    }
    public function deletev1favoritesusersassetsfavorite($getuserId, $getassetId)
    {
        // Delete a favorite for an asset by the authenticated user.

        $body = json_encode(
    array());
        $data = $this->postRequestWithCookie("https://catalog.roblox.com/v1/favorites/users/$getuserId/assets/$getassetId/favorite", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"delete"]);
        return $data;
    }
    public function getv1favoritesusersbundlesfavorite($getuserId, $getbundleId)
    {
// Gets the favorite model for the bundle and user.
        $data = $this->getRequestWithCookie("https://catalog.roblox.com/v1/favorites/users/$getuserId/bundles/$getbundleId/favorite", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function postv1favoritesusersbundlesfavorite($getuserId, $getbundleId)
    {
        // Create a favorite for the bundle by the authenticated user.

        $body = json_encode(
    array());
        $data = $this->postRequestWithCookie("https://catalog.roblox.com/v1/favorites/users/$getuserId/bundles/$getbundleId/favorite", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"post"]);
        return $data;
    }
    public function deletev1favoritesusersbundlesfavorite($getuserId, $getbundleId)
    {
        // Delete favorite for the bundle by the authenticated user.

        $body = json_encode(
    array());
        $data = $this->postRequestWithCookie("https://catalog.roblox.com/v1/favorites/users/$getuserId/bundles/$getbundleId/favorite", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"delete"]);
        return $data;
    }
    public function getv1subcategories()
    {
// Lists Subcategory Names and their Ids
        $data = $this->getRequestWithCookie("https://catalog.roblox.com/v1/subcategories", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function getv1usersbundles($getuserId, $getsortOrder, $getlimit, $getcursor)
    {
// Lists the bundles owned by a given user.
        $data = $this->getRequestWithCookie("https://catalog.roblox.com/v1/users/$getuserId/bundles?sortOrder=$sortOrder&limit=$limit&cursor=$cursor", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function postv1bundlesunpack($getbundleId)
    {
        // Unpacks a bundle and grants all of the associated items.It may take a few seconds for all items to be granted after the request finishes.

        $body = json_encode(
    array());
        $data = $this->postRequestWithCookie("https://catalog.roblox.com/v1/bundles/$getbundleId/unpack", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"post"]);
        return $data;
    }
    
// Develop Api v1

public function getv1gametemplates()
    {
// Gets a page of templates that can be used to start off making games.
        $data = $this->getRequestWithCookie("https://develop.roblox.com/v1/gametemplates", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function getv1gameUpdateNotifications($getuniverseId)
    {
// Retrieves historical records of game update messages.
        $data = $this->getRequestWithCookie("https://develop.roblox.com/v1/gameUpdateNotifications/$getuniverseId", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function postv1gameUpdateNotifications($getuniverseId)
    {
        // Publishes a new Game Update Notification for a {Roblox.Platform.Universes.IUniverse}Universe

        $body = json_encode(
    array());
        $data = $this->postRequestWithCookie("https://develop.roblox.com/v1/gameUpdateNotifications/$getuniverseId", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"post"]);
        return $data;
    }
    public function getv1groupsuniverses($getgroupId, $getsortOrder, $getlimit, $getcursor)
    {
// Gets a list of universes for the given group.
        $data = $this->getRequestWithCookie("https://develop.roblox.com/v1/groups/$getgroupId/universes?sortOrder=$sortOrder&limit=$limit&cursor=$cursor", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function getv1placescompatibilities($getplaceId)
    {
// Gets compatibility of place with different platforms {placeId}
        $data = $this->getRequestWithCookie("https://develop.roblox.com/v1/places/$getplaceId/compatibilities", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function getv1placesstats($getplaceId, $gettype, $getgranularity, $getdivisionType, $getstartTime, $getendTime)
    {
// Get statistics data for a place.
        $data = $this->getRequestWithCookie("https://develop.roblox.com/v1/places/$getplaceId/stats/$gettype?granularity=$granularity&divisionType=$divisionType&startTime=$startTime&endTime=$endTime", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function getv1placesstatslegacyflot($getplaceId, $gettype, $gettimeFrame, $getdivisionType, $getstartTime, $getendTime)
    {
// Get statistics data for a place in a certain format.  DO NOT USE THIS ENDPOINT. It may be removed at any time. Use GetStatistics instead.
        $data = $this->getRequestWithCookie("https://develop.roblox.com/v1/places/$getplaceId/stats/$gettype/legacy/flot?timeFrame=$timeFrame&divisionType=$divisionType&startTime=$startTime&endTime=$endTime", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function getv1plugins($getpluginIds)
    {
// Gets plugin details by ids.
        $data = $this->getRequestWithCookie("https://develop.roblox.com/v1/plugins?pluginIds=$pluginIds", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function getv1searchuniverses($getq, $getsort, $getsortOrder, $getlimit, $getcursor)
    {
// Allows searching for universes.
        $data = $this->getRequestWithCookie("https://develop.roblox.com/v1/search/universes?q=$q&sort=$sort&sortOrder=$sortOrder&limit=$limit&cursor=$cursor", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function getv1universes($getuniverseId)
    {
// Gets a {Roblox.Api.Develop.Models.UniverseModel}.
        $data = $this->getRequestWithCookie("https://develop.roblox.com/v1/universes/$getuniverseId", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function getv1universesconfiguration($getuniverseId)
    {
// Get settings for an owned universe.
        $data = $this->getRequestWithCookie("https://develop.roblox.com/v1/universes/$getuniverseId/configuration", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function patchv1universesconfiguration($getuniverseId, $name, $universeAvatarType, $universeScaleType, $universeAnimationType, $universeCollisionType, $universeBodyType, $universeJointPositioningType, $isArchived, $isFriendsOnly, $genre, $isForSale, $price)
    {
        // Update universe settings for an owned universe.

        $body = json_encode(
    array());
        $data = $this->postRequestWithCookie("https://develop.roblox.com/v1/universes/$getuniverseId/configuration", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"patch"]);
        return $data;
    }
    public function getv1universesconfigurationvipservers($getuniverseId)
    {
// Get settings for an owned universe's VIP servers.
        $data = $this->getRequestWithCookie("https://develop.roblox.com/v1/universes/$getuniverseId/configuration/vip-servers", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function getv1universespermissions($getuniverseId)
    {
// Returns list of granted and declined permissions related to the universe with the id {universeId} for authenticated user
        $data = $this->getRequestWithCookie("https://develop.roblox.com/v1/universes/$getuniverseId/permissions", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function getv1universesplaces($getuniverseId, $getsortOrder, $getlimit, $getcursor)
    {
// Gets a list of places for a universe.
        $data = $this->getRequestWithCookie("https://develop.roblox.com/v1/universes/$getuniverseId/places?sortOrder=$sortOrder&limit=$limit&cursor=$cursor", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function getv1universesstatisticreports($getuniverseId)
    {
// Lists all months and years for which universe statistics are available.
        $data = $this->getRequestWithCookie("https://develop.roblox.com/v1/universes/$getuniverseId/statistic-reports", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function getv1universesstatisticreports2($getuniverseId, $getyearDashMonth)
    {
// Retrieves the status of a spreadsheet with universe statistics for a given month.
        $data = $this->getRequestWithCookie("https://develop.roblox.com/v1/universes/$getuniverseId/statistic-reports/$getyearDashMonth", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function getv1universesstatisticreportsdownload($getuniverseId, $getyearDashMonth)
    {
// Retrieves a spreadsheet with universe statistics for a given month.
        $data = $this->getRequestWithCookie("https://develop.roblox.com/v1/universes/$getuniverseId/statistic-reports/$getyearDashMonth/download", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function getv1universesteamcreate($getuniverseId)
    {
// Gets TeamCreate settings for an {Roblox.Platform.Universes.IUniverse}.
        $data = $this->getRequestWithCookie("https://develop.roblox.com/v1/universes/$getuniverseId/teamcreate", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function patchv1universesteamcreate($getuniverseId, $isEnabled)
    {
        // Edit team create settings for a universe.

        $body = json_encode(
    array(
    ["isEnabled"]=>"$isEnabled",));
        $data = $this->postRequestWithCookie("https://develop.roblox.com/v1/universes/$getuniverseId/teamcreate", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"patch"]);
        return $data;
    }
    public function getv1universesteamcreatememberships($getuniverseId, $getsortOrder, $getlimit, $getcursor)
    {
// List of users allowed to TeamCreate a universe.
        $data = $this->getRequestWithCookie("https://develop.roblox.com/v1/universes/$getuniverseId/teamcreate/memberships?sortOrder=$sortOrder&limit=$limit&cursor=$cursor", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function postv1universesteamcreatememberships($getuniverseId, $userId)
    {
        // Adds a user to a TeamCreate permissions list.

        $body = json_encode(
    array(
    ["userId"]=>"$userId",));
        $data = $this->postRequestWithCookie("https://develop.roblox.com/v1/universes/$getuniverseId/teamcreate/memberships", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"post"]);
        return $data;
    }
    public function deletev1universesteamcreatememberships($getuniverseId, $userId)
    {
        // Removes a user from a TeamCreate permissions list.

        $body = json_encode(
    array(
    ["userId"]=>"$userId",));
        $data = $this->postRequestWithCookie("https://develop.roblox.com/v1/universes/$getuniverseId/teamcreate/memberships", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"delete"]);
        return $data;
    }
    public function getv1universesmultiget($getids)
    {
// Gets a {System.Collections.Generic.List`1}.
        $data = $this->getRequestWithCookie("https://develop.roblox.com/v1/universes/multiget?ids=$ids", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function getv1universesmultigetpermissions($getids)
    {
// Returns an array of granted and declined permissions related to the universes with the ids in {ids} for the authenticated user.
        $data = $this->getRequestWithCookie("https://develop.roblox.com/v1/universes/multiget/permissions?ids=$ids", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function getv1usergroupscanmanage()
    {
// Gets a list of Groups that a user can manage.
        $data = $this->getRequestWithCookie("https://develop.roblox.com/v1/user/groups/canmanage", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function getv1usernotificationsstatisticreports()
    {
// Gets a list of DeveloperMetricsAvailable notifications for the authenticated user.
        $data = $this->getRequestWithCookie("https://develop.roblox.com/v1/user/notifications/statistic-reports", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function getv1userstudiodata($getclientKey)
    {
// Retrieves a JSON object from persistant storage.
        $data = $this->getRequestWithCookie("https://develop.roblox.com/v1/user/studiodata?clientKey=$clientKey", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function postv1userstudiodata($getclientKey)
    {
        // Saves a JSON object to persistent storage.

        $body = json_encode(
    array());
        $data = $this->postRequestWithCookie("https://develop.roblox.com/v1/user/studiodata?clientKey=$clientKey", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"post"]);
        return $data;
    }
    public function getv1userteamcreatememberships($getsortOrder, $getlimit, $getcursor)
    {
// List of universes the authenticated user has permission to TeamCreate.
        $data = $this->getRequestWithCookie("https://develop.roblox.com/v1/user/teamcreate/memberships?sortOrder=$sortOrder&limit=$limit&cursor=$cursor", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function getv1useruniverses($getsortOrder, $getlimit, $getcursor)
    {
// Gets a list of universes for the authenticated user.
        $data = $this->getRequestWithCookie("https://develop.roblox.com/v1/user/universes?sortOrder=$sortOrder&limit=$limit&cursor=$cursor", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function postv1gameUpdateNotificationsfilter()
    {
        // Filters game update text.

        $body = json_encode(
    array());
        $data = $this->postRequestWithCookie("https://develop.roblox.com/v1/gameUpdateNotifications/filter", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"post"]);
        return $data;
    }
    public function postv1places($getplaceId, $name, $description)
    {
        // Updates the place configuration for the place with the id {placeId}

        $body = json_encode(
    array());
        $data = $this->postRequestWithCookie("https://develop.roblox.com/v1/places/$getplaceId", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"post"]);
        return $data;
    }
    public function patchv1places($getplaceId, $name, $description)
    {
        // Updates the place configuration for the place with the id {placeId}

        $body = json_encode(
    array());
        $data = $this->postRequestWithCookie("https://develop.roblox.com/v1/places/$getplaceId", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"patch"]);
        return $data;
    }
    public function postv1universesactivate($getuniverseId)
    {
        // Activates a universes.

        $body = json_encode(
    array());
        $data = $this->postRequestWithCookie("https://develop.roblox.com/v1/universes/$getuniverseId/activate", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"post"]);
        return $data;
    }
    public function postv1universesaliases($getuniverseId, $name, $type, $targetId)
    {
        // Creates an alias.

        $body = json_encode(
    array(
    ["name"]=>"$name",
    ["type"]=>"$type",
    ["targetId"]=>"$targetId",));
        $data = $this->postRequestWithCookie("https://develop.roblox.com/v1/universes/$getuniverseId/aliases", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"post"]);
        return $data;
    }
    public function postv1universesdeactivate($getuniverseId)
    {
        // Deactivates a universe.

        $body = json_encode(
    array());
        $data = $this->postRequestWithCookie("https://develop.roblox.com/v1/universes/$getuniverseId/deactivate", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"post"]);
        return $data;
    }
    public function postv1universesdeveloperproductsupdate($getuniverseId, $getproductId, $Name, $Description, $IconImageAssetId, $PriceInRobux)
    {
        // Updates a Developer Product.

        $body = json_encode(
    array());
        $data = $this->postRequestWithCookie("https://develop.roblox.com/v1/universes/$getuniverseId/developerproducts/$getproductId/update", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"post"]);
        return $data;
    }
    public function postv1universesstatisticreportsgenerate($getuniverseId, $getyearDashMonth)
    {
        // 

        $body = json_encode(
    array());
        $data = $this->postRequestWithCookie("https://develop.roblox.com/v1/universes/$getuniverseId/statistic-reports/$getyearDashMonth/generate", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"post"]);
        return $data;
    }
    public function patchv1plugins($getpluginId, $name, $description, $commentsEnabled)
    {
        // Updates a plugin.

        $body = json_encode(
    array(
    ["name"]=>"$name",
    ["description"]=>"$description",
    ["commentsEnabled"]=>"$commentsEnabled",));
        $data = $this->postRequestWithCookie("https://develop.roblox.com/v1/plugins/$getpluginId", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"patch"]);
        return $data;
    }
    public function deletev1universesaliases($getuniverseId, $getname)
    {
        // Deletes an alias.

        $body = json_encode(
    array());
        $data = $this->postRequestWithCookie("https://develop.roblox.com/v1/universes/$getuniverseId/aliases/$getname", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"delete"]);
        return $data;
    }
    public function patchv1universesaliases($getuniverseId, $getname, $name, $type, $targetId)
    {
        // Updates an alias.

        $body = json_encode(
    array(
    ["name"]=>"$name",
    ["type"]=>"$type",
    ["targetId"]=>"$targetId",));
        $data = $this->postRequestWithCookie("https://develop.roblox.com/v1/universes/$getuniverseId/aliases/$getname", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"patch"]);
        return $data;
    }
    
// Develop Api v2

public function getv2universesconfiguration($getuniverseId)
    {
// Get settings for an owned universe.   V2 Contains data for avatar scale and asset override.
        $data = $this->getRequestWithCookie("https://develop.roblox.com/v2/universes/$getuniverseId/configuration", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function patchv2universesconfiguration($getuniverseId, $name, $description, $universeAvatarType, $universeAnimationType, $universeCollisionType, $universeJointPositioningType, $isArchived, $isFriendsOnly, $genre, $isForSale, $price, $assetID, $assetTypeID, $isPlayerChoice, $universeAvatarMinScales, $universeAvatarMaxScales, $studioAccessToApisAllowed)
    {
        // Update universe settings for an owned universe.V2 Contains data for avatar scale and asset override.

        $body = json_encode(
    array());
        $data = $this->postRequestWithCookie("https://develop.roblox.com/v2/universes/$getuniverseId/configuration", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"patch"]);
        return $data;
    }
    
// Contacts Api v1

public function postv1usergettags()
    {
        // Gets the tags for multiple users

        $body = json_encode(
    array());
        $data = $this->postRequestWithCookie("https://contacts.roblox.com/v1/user/get-tags", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"post"]);
        return $data;
    }
    public function postv1usersetpendingtag($targetUserId, $userTag)
    {
        // Sets the pending tag for a user

        $body = json_encode(
    array());
        $data = $this->postRequestWithCookie("https://contacts.roblox.com/v1/user/set-pending-tag", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"post"]);
        return $data;
    }
    public function postv1usertag($targetUserId, $userTag)
    {
        // Sets the tag for a user

        $body = json_encode(
    array());
        $data = $this->postRequestWithCookie("https://contacts.roblox.com/v1/user/tag", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"post"]);
        return $data;
    }
    
// Avatar Api v1

public function getv1avatar()
    {
// Returns details about the authenticated user's avatar
        $data = $this->getRequestWithCookie("https://avatar.roblox.com/v1/avatar", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function getv1avatarrules()
    {
// Returns the business rules related to avatars
        $data = $this->getRequestWithCookie("https://avatar.roblox.com/v1/avatar-rules", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function getv1outfitsdetails($getuserOutfitId)
    {
// Gets details about the contents of an outfit.
        $data = $this->getRequestWithCookie("https://avatar.roblox.com/v1/outfits/$getuserOutfitId/details", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function getv1recentitemslist($getrecentItemListType)
    {
// Returns a list of recent items  Recent items can be Assets or Outfits
        $data = $this->getRequestWithCookie("https://avatar.roblox.com/v1/recent-items/$getrecentItemListType/list", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function getv1usersavatar($getuserId)
    {
// Returns details about a specified user's avatar
        $data = $this->getRequestWithCookie("https://avatar.roblox.com/v1/users/$getuserId/avatar", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function getv1userscurrentlywearing($getuserId)
    {
// Gets a list of asset ids that the user is currently wearing
        $data = $this->getRequestWithCookie("https://avatar.roblox.com/v1/users/$getuserId/currently-wearing", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function getv1usersoutfits($getuserId, $getpage, $getitemsPerPage, $getisEditable)
    {
// Gets a list of outfits for the specified user.
        $data = $this->getRequestWithCookie("https://avatar.roblox.com/v1/users/$getuserId/outfits?page=$page&itemsPerPage=$itemsPerPage&isEditable=$isEditable", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function postv1avatarassetsremove($getassetId)
    {
        // Removes the asset from the authenticated user's avatar.

        $body = json_encode(
    array());
        $data = $this->postRequestWithCookie("https://avatar.roblox.com/v1/avatar/assets/$getassetId/remove", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"post"]);
        return $data;
    }
    public function postv1avatarassetswear($getassetId)
    {
        // Puts the asset on the authenticated user's avatar.

        $body = json_encode(
    array());
        $data = $this->postRequestWithCookie("https://avatar.roblox.com/v1/avatar/assets/$getassetId/wear", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"post"]);
        return $data;
    }
    public function postv1avatarredrawthumbnail()
    {
        // Requests the authenticated user's thumbnail be redrawn

        $body = json_encode(
    array());
        $data = $this->postRequestWithCookie("https://avatar.roblox.com/v1/avatar/redraw-thumbnail", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"post"]);
        return $data;
    }
    public function postv1avatarsetbodycolors($headColorId, $torsoColorId, $rightArmColorId, $leftArmColorId, $rightLegColorId, $leftLegColorId)
    {
        // Sets the authenticated user's body colors

        $body = json_encode(
    array());
        $data = $this->postRequestWithCookie("https://avatar.roblox.com/v1/avatar/set-body-colors", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"post"]);
        return $data;
    }
    public function postv1avatarsetplayeravatartype($playerAvatarType)
    {
        // Sets the authenticated user's player avatar type (e.g. R6 or R15).

        $body = json_encode(
    array());
        $data = $this->postRequestWithCookie("https://avatar.roblox.com/v1/avatar/set-player-avatar-type", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"post"]);
        return $data;
    }
    public function postv1avatarsetscales($height, $width, $head, $depth, $proportion, $bodyType)
    {
        // Sets the authenticated user's scales

        $body = json_encode(
    array());
        $data = $this->postRequestWithCookie("https://avatar.roblox.com/v1/avatar/set-scales", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"post"]);
        return $data;
    }
    public function postv1avatarsetwearingassets()
    {
        // Sets the avatar's current assets to the list

        $body = json_encode(
    array());
        $data = $this->postRequestWithCookie("https://avatar.roblox.com/v1/avatar/set-wearing-assets", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"post"]);
        return $data;
    }
    public function postv1outfitsdelete($getuserOutfitId)
    {
        // Deletes the outfit

        $body = json_encode(
    array());
        $data = $this->postRequestWithCookie("https://avatar.roblox.com/v1/outfits/$getuserOutfitId/delete", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"post"]);
        return $data;
    }
    public function postv1outfitsupdate($getuserOutfitId, $name, $bodyColors, $scale, $playerAvatarType)
    {
        // Updates the contents of the outfit.

        $body = json_encode(
    array());
        $data = $this->postRequestWithCookie("https://avatar.roblox.com/v1/outfits/$getuserOutfitId/update", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"post"]);
        return $data;
    }
    public function postv1outfitswear($getuserOutfitId)
    {
        // Wears the outfit

        $body = json_encode(
    array());
        $data = $this->postRequestWithCookie("https://avatar.roblox.com/v1/outfits/$getuserOutfitId/wear", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"post"]);
        return $data;
    }
    public function postv1outfitscreate($name, $bodyColors, $scale, $playerAvatarType)
    {
        // Creates a new outfit.

        $body = json_encode(
    array());
        $data = $this->postRequestWithCookie("https://avatar.roblox.com/v1/outfits/create", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"post"]);
        return $data;
    }
    public function patchv1outfits($getuserOutfitId, $name, $bodyColors, $scale, $playerAvatarType)
    {
        // Updates the contents of an outfit.

        $body = json_encode(
    array());
        $data = $this->postRequestWithCookie("https://avatar.roblox.com/v1/outfits/$getuserOutfitId", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"patch"]);
        return $data;
    }
    
// Publish Api v1

public function postv1badgesicon($getbadgeId, $getrequestfiles)
    {
        // Overwrites a badge icon with a new one.

        $body = json_encode(
    array());
        $data = $this->postRequestWithCookie("https://publish.roblox.com/v1/badges/$getbadgeId/icon", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"post"]);
        return $data;
    }
    public function postv1gamepassesicon($getgamePassId, $getrequestfiles)
    {
        // Overwrites a game pass icon with a new one.

        $body = json_encode(
    array());
        $data = $this->postRequestWithCookie("https://publish.roblox.com/v1/game-passes/$getgamePassId/icon", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"post"]);
        return $data;
    }
    public function postv1gamesthumbnailimage($getgameId, $getrequestfiles)
    {
        // Uploads a game thumbnail.

        $body = json_encode(
    array());
        $data = $this->postRequestWithCookie("https://publish.roblox.com/v1/games/$getgameId/thumbnail/image", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"post"]);
        return $data;
    }
    public function postv1pluginsicon($getpluginId, $getrequestfiles)
    {
        // Overwrites a plugin icon with a new one.

        $body = json_encode(
    array());
        $data = $this->postRequestWithCookie("https://publish.roblox.com/v1/plugins/$getpluginId/icon", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"post"]);
        return $data;
    }
    
// Midas Api v1

public function getv1platformsandroidpurchasescomplete($getmidasCallbackRequestopenid, $getmidasCallbackRequestappid, $getmidasCallbackRequestts, $getmidasCallbackRequestpayitem, $getmidasCallbackRequesttoken, $getmidasCallbackRequestbillno, $getmidasCallbackRequestversion, $getmidasCallbackRequestzoneid, $getmidasCallbackRequestprovidetype, $getmidasCallbackRequestamt, $getmidasCallbackRequestappmeta, $getmidasCallbackRequestcftid, $getmidasCallbackRequestchannel_id, $getmidasCallbackRequestpaychannelsubid, $getmidasCallbackRequestclientver, $getmidasCallbackRequestpayamt_coins, $getmidasCallbackRequestpubacct_payamt_coins, $getmidasCallbackRequestproduct_id, $getmidasCallbackRequestbazinga, $getmidasCallbackRequestsig)
    {
// CompletePurchase
        $data = $this->getRequestWithCookie("https://midas.roblox.com/v1/platforms/android/purchases/complete?midasCallbackRequest.openid=$midasCallbackRequestopenid&midasCallbackRequest.appid=$midasCallbackRequestappid&midasCallbackRequest.ts=$midasCallbackRequestts&midasCallbackRequest.payitem=$midasCallbackRequestpayitem&midasCallbackRequest.token=$midasCallbackRequesttoken&midasCallbackRequest.billno=$midasCallbackRequestbillno&midasCallbackRequest.version=$midasCallbackRequestversion&midasCallbackRequest.zoneid=$midasCallbackRequestzoneid&midasCallbackRequest.providetype=$midasCallbackRequestprovidetype&midasCallbackRequest.amt=$midasCallbackRequestamt&midasCallbackRequest.appmeta=$midasCallbackRequestappmeta&midasCallbackRequest.cftid=$midasCallbackRequestcftid&midasCallbackRequest.channel_id=$midasCallbackRequestchannel_id&midasCallbackRequest.paychannelsubid=$midasCallbackRequestpaychannelsubid&midasCallbackRequest.clientver=$midasCallbackRequestclientver&midasCallbackRequest.payamt_coins=$midasCallbackRequestpayamt_coins&midasCallbackRequest.pubacct_payamt_coins=$midasCallbackRequestpubacct_payamt_coins&midasCallbackRequest.product_id=$midasCallbackRequestproduct_id&midasCallbackRequest.bazinga=$midasCallbackRequestbazinga&midasCallbackRequest.sig=$midasCallbackRequestsig", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function getv1platformsdesktoppurchasescomplete($getmidasCallbackRequestopenid, $getmidasCallbackRequestappid, $getmidasCallbackRequestts, $getmidasCallbackRequestpayitem, $getmidasCallbackRequesttoken, $getmidasCallbackRequestbillno, $getmidasCallbackRequestversion, $getmidasCallbackRequestzoneid, $getmidasCallbackRequestprovidetype, $getmidasCallbackRequestamt, $getmidasCallbackRequestappmeta, $getmidasCallbackRequestcftid, $getmidasCallbackRequestchannel_id, $getmidasCallbackRequestpaychannelsubid, $getmidasCallbackRequestclientver, $getmidasCallbackRequestpayamt_coins, $getmidasCallbackRequestpubacct_payamt_coins, $getmidasCallbackRequestproduct_id, $getmidasCallbackRequestbazinga, $getmidasCallbackRequestsig)
    {
// Callback endpoint for Midas after it receives payment request and informs Roblox for product delivery.
        $data = $this->getRequestWithCookie("https://midas.roblox.com/v1/platforms/desktop/purchases/complete?midasCallbackRequest.openid=$midasCallbackRequestopenid&midasCallbackRequest.appid=$midasCallbackRequestappid&midasCallbackRequest.ts=$midasCallbackRequestts&midasCallbackRequest.payitem=$midasCallbackRequestpayitem&midasCallbackRequest.token=$midasCallbackRequesttoken&midasCallbackRequest.billno=$midasCallbackRequestbillno&midasCallbackRequest.version=$midasCallbackRequestversion&midasCallbackRequest.zoneid=$midasCallbackRequestzoneid&midasCallbackRequest.providetype=$midasCallbackRequestprovidetype&midasCallbackRequest.amt=$midasCallbackRequestamt&midasCallbackRequest.appmeta=$midasCallbackRequestappmeta&midasCallbackRequest.cftid=$midasCallbackRequestcftid&midasCallbackRequest.channel_id=$midasCallbackRequestchannel_id&midasCallbackRequest.paychannelsubid=$midasCallbackRequestpaychannelsubid&midasCallbackRequest.clientver=$midasCallbackRequestclientver&midasCallbackRequest.payamt_coins=$midasCallbackRequestpayamt_coins&midasCallbackRequest.pubacct_payamt_coins=$midasCallbackRequestpubacct_payamt_coins&midasCallbackRequest.product_id=$midasCallbackRequestproduct_id&midasCallbackRequest.bazinga=$midasCallbackRequestbazinga&midasCallbackRequest.sig=$midasCallbackRequestsig", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function getv1platformsiospurchasescomplete($getmidasCallbackRequestopenid, $getmidasCallbackRequestappid, $getmidasCallbackRequestts, $getmidasCallbackRequestpayitem, $getmidasCallbackRequesttoken, $getmidasCallbackRequestbillno, $getmidasCallbackRequestversion, $getmidasCallbackRequestzoneid, $getmidasCallbackRequestprovidetype, $getmidasCallbackRequestamt, $getmidasCallbackRequestappmeta, $getmidasCallbackRequestcftid, $getmidasCallbackRequestchannel_id, $getmidasCallbackRequestpaychannelsubid, $getmidasCallbackRequestclientver, $getmidasCallbackRequestpayamt_coins, $getmidasCallbackRequestpubacct_payamt_coins, $getmidasCallbackRequestproduct_id, $getmidasCallbackRequestbazinga, $getmidasCallbackRequestsig)
    {
// CompletePurchase
        $data = $this->getRequestWithCookie("https://midas.roblox.com/v1/platforms/ios/purchases/complete?midasCallbackRequest.openid=$midasCallbackRequestopenid&midasCallbackRequest.appid=$midasCallbackRequestappid&midasCallbackRequest.ts=$midasCallbackRequestts&midasCallbackRequest.payitem=$midasCallbackRequestpayitem&midasCallbackRequest.token=$midasCallbackRequesttoken&midasCallbackRequest.billno=$midasCallbackRequestbillno&midasCallbackRequest.version=$midasCallbackRequestversion&midasCallbackRequest.zoneid=$midasCallbackRequestzoneid&midasCallbackRequest.providetype=$midasCallbackRequestprovidetype&midasCallbackRequest.amt=$midasCallbackRequestamt&midasCallbackRequest.appmeta=$midasCallbackRequestappmeta&midasCallbackRequest.cftid=$midasCallbackRequestcftid&midasCallbackRequest.channel_id=$midasCallbackRequestchannel_id&midasCallbackRequest.paychannelsubid=$midasCallbackRequestpaychannelsubid&midasCallbackRequest.clientver=$midasCallbackRequestclientver&midasCallbackRequest.payamt_coins=$midasCallbackRequestpayamt_coins&midasCallbackRequest.pubacct_payamt_coins=$midasCallbackRequestpubacct_payamt_coins&midasCallbackRequest.product_id=$midasCallbackRequestproduct_id&midasCallbackRequest.bazinga=$midasCallbackRequestbazinga&midasCallbackRequest.sig=$midasCallbackRequestsig", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function postv1platformsandroidpurchases($mainProductCode, $midasPlatformId, $midasPlatformKey)
    {
        // Validates and gets token from Midas

        $body = json_encode(
    array());
        $data = $this->postRequestWithCookie("https://midas.roblox.com/v1/platforms/android/purchases", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"post"]);
        return $data;
    }
    public function postv1platformsdesktoppurchases($mainProductId)
    {
        // FetchPurchaseToken

        $body = json_encode(
    array());
        $data = $this->postRequestWithCookie("https://midas.roblox.com/v1/platforms/desktop/purchases", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"post"]);
        return $data;
    }
    public function postv1platformsiospurchases($mainProductCode, $midasPlatformId, $midasPlatformKey)
    {
        // Validates and gets token from Midas

        $body = json_encode(
    array());
        $data = $this->postRequestWithCookie("https://midas.roblox.com/v1/platforms/ios/purchases", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"post"]);
        return $data;
    }
    
// Locale Api v1

public function getv1countryregions()
    {
// Get list of country regions sorted by Name
        $data = $this->getRequestWithCookie("https://locale.roblox.com/v1/country-regions", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function getv1countryregionsusercountryregion()
    {
// Gets user's country region.
        $data = $this->getRequestWithCookie("https://locale.roblox.com/v1/country-regions/user-country-region", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function patchv1countryregionsusercountryregion($countryId)
    {
        // Sets user's countrt region.

        $body = json_encode(
    array());
        $data = $this->postRequestWithCookie("https://locale.roblox.com/v1/country-regions/user-country-region", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"patch"]);
        return $data;
    }
    public function getv1languages()
    {
// Gets the list of languages available on Roblox.
        $data = $this->getRequestWithCookie("https://locale.roblox.com/v1/languages", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function getv1languagesusergeneratedcontent()
    {
// Gets the list of languages available on Roblox for user generated content.
        $data = $this->getRequestWithCookie("https://locale.roblox.com/v1/languages/user-generated-content", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function getv1localessupportedlocales()
    {
// Get list of supported locales sorted by the Native Name property.
        $data = $this->getRequestWithCookie("https://locale.roblox.com/v1/locales/supported-locales", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function getv1localesuserlocale()
    {
// Gets user locale. If user is absent returns, locale from http request object.
        $data = $this->getRequestWithCookie("https://locale.roblox.com/v1/locales/user-locale", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function getv1localesuserlocalizationlocussupportedlocales()
    {
// Gets each of a user's localization locus supported locales. A localization locus supported locale is a page (or group of pages) that  have been defined by the International team which need independent locale support.  If the user is null we will attempt to return the locales appropriate for the user's device language.
        $data = $this->getRequestWithCookie("https://locale.roblox.com/v1/locales/user-localization-locus-supported-locales", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function postv1localessetusersupportedlocale($supportedLocaleCode)
    {
        // Sets user's supported locale.Null supported locale will clear out user's supported locale (set users' supported locale to null)

        $body = json_encode(
    array());
        $data = $this->postRequestWithCookie("https://locale.roblox.com/v1/locales/set-user-supported-locale", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"post"]);
        return $data;
    }
    
// Friends Api v1

public function getv1friendsverifiednearbycode($getcode)
    {
// Gets data from a nearby code
        $data = $this->getRequestWithCookie("https://friends.roblox.com/v1/friends/verified/nearby/code/$getcode", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function getv1friendsverifiednearbyhealth()
    {
// Checks if session is still alive
        $data = $this->getRequestWithCookie("https://friends.roblox.com/v1/friends/verified/nearby/health", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function getv1myfriendsrequests($getsortOrder, $getlimit, $getcursor)
    {
// Get all users that friend requests with targetUserId using exclusive start paging
        $data = $this->getRequestWithCookie("https://friends.roblox.com/v1/my/friends/requests?sortOrder=$sortOrder&limit=$limit&cursor=$cursor", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function getv1recommendedusers()
    {
// Return a list of Recommendations for the Authenticated User.  V1 API to just return list of existing friends for the Authenticated user.
        $data = $this->getRequestWithCookie("https://friends.roblox.com/v1/recommended-users", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function getv1userfriendrequestscount()
    {
// Return the number of pending friend requests.
        $data = $this->getRequestWithCookie("https://friends.roblox.com/v1/user/friend-requests/count", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function getv1usersfollowers($gettargetUserId, $getsortOrder, $getlimit, $getcursor)
    {
// Get all users that follow user with targetUserId in page response format
        $data = $this->getRequestWithCookie("https://friends.roblox.com/v1/users/$gettargetUserId/followers?sortOrder=$sortOrder&limit=$limit&cursor=$cursor", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function getv1usersfollowings($gettargetUserId, $getsortOrder, $getlimit, $getcursor)
    {
// Get all users that user with targetUserId is following in page response format
        $data = $this->getRequestWithCookie("https://friends.roblox.com/v1/users/$gettargetUserId/followings?sortOrder=$sortOrder&limit=$limit&cursor=$cursor", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function getv1usersfriends($getuserId)
    {
// Get list of all friends for the specified user.
        $data = $this->getRequestWithCookie("https://friends.roblox.com/v1/users/$getuserId/friends", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function getv1usersfriendsstatuses($getuserId, $getuserIds)
    {
// Gets a list of friend statuses of specified users against the specified user.
        $data = $this->getRequestWithCookie("https://friends.roblox.com/v1/users/$getuserId/friends/statuses?userIds=$userIds", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function postv1contactsmatch($country, $sourceId)
    {
        // Match the authenticated user's contacts with roblox users by phone number.Gets the user contact information as a list of {Roblox.Friends.Api.Contact} and process them to create a list of {Roblox.Friends.Api.UserContact}. Note that one contactcan result into multiple usercontacts depending on the amount of phone numbersassociated with the contact.

        $body = json_encode(
    array());
        $data = $this->postRequestWithCookie("https://friends.roblox.com/v1/contacts/match", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"post"]);
        return $data;
    }
    public function postv1contactsupdate($country, $sourceId)
    {
        // Updates the authenticated user's contacts.

        $body = json_encode(
    array());
        $data = $this->postRequestWithCookie("https://friends.roblox.com/v1/contacts/update", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"post"]);
        return $data;
    }
    public function postv1friendsverifiednearbyredeem($getcode)
    {
        // Redeems nearby code

        $body = json_encode(
    array());
        $data = $this->postRequestWithCookie("https://friends.roblox.com/v1/friends/verified/nearby/$getcode/redeem", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"post"]);
        return $data;
    }
    public function postv1friendsverifiednearbysession()
    {
        // Get or Create a session for authenticated user

        $body = json_encode(
    array());
        $data = $this->postRequestWithCookie("https://friends.roblox.com/v1/friends/verified/nearby/session", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"post"]);
        return $data;
    }
    public function deletev1friendsverifiednearbysession()
    {
        // Removes session for authenticated user

        $body = json_encode(
    array());
        $data = $this->postRequestWithCookie("https://friends.roblox.com/v1/friends/verified/nearby/session", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"delete"]);
        return $data;
    }
    public function postv1friendsverifiedqrredeem($getcode)
    {
        // Redeems QR code

        $body = json_encode(
    array());
        $data = $this->postRequestWithCookie("https://friends.roblox.com/v1/friends/verified/qr/$getcode/redeem", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"post"]);
        return $data;
    }
    public function postv1friendsverifiedqrsession()
    {
        // Get or Create a session for authenticated user

        $body = json_encode(
    array());
        $data = $this->postRequestWithCookie("https://friends.roblox.com/v1/friends/verified/qr/session", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"post"]);
        return $data;
    }
    public function deletev1friendsverifiedqrsession()
    {
        // Removes session for authenticated user

        $body = json_encode(
    array());
        $data = $this->postRequestWithCookie("https://friends.roblox.com/v1/friends/verified/qr/session", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"delete"]);
        return $data;
    }
    public function postv1userfriendrequestsdeclineall()
    {
        // Decline all pending friend requests for the authenticated user.

        $body = json_encode(
    array());
        $data = $this->postRequestWithCookie("https://friends.roblox.com/v1/user/friend-requests/decline-all", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"post"]);
        return $data;
    }
    public function postv1usersacceptfriendrequest($getrequesterUserId)
    {
        // Accept a friend request.

        $body = json_encode(
    array());
        $data = $this->postRequestWithCookie("https://friends.roblox.com/v1/users/$getrequesterUserId/accept-friend-request", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"post"]);
        return $data;
    }
    public function postv1usersdeclinefriendrequest($getrequesterUserId)
    {
        // Decline a friend request.

        $body = json_encode(
    array());
        $data = $this->postRequestWithCookie("https://friends.roblox.com/v1/users/$getrequesterUserId/decline-friend-request", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"post"]);
        return $data;
    }
    public function postv1usersfollow($gettargetUserId)
    {
        // Creates the following between a user and user with {targetUserId}

        $body = json_encode(
    array());
        $data = $this->postRequestWithCookie("https://friends.roblox.com/v1/users/$gettargetUserId/follow", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"post"]);
        return $data;
    }
    public function postv1usersrequestfriendship($gettargetUserId)
    {
        // Send a friend request to target user

        $body = json_encode(
    array());
        $data = $this->postRequestWithCookie("https://friends.roblox.com/v1/users/$gettargetUserId/request-friendship", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"post"]);
        return $data;
    }
    public function postv1usersunfollow($gettargetUserId)
    {
        // Deletes the following between a user and user with {targetUserId}

        $body = json_encode(
    array());
        $data = $this->postRequestWithCookie("https://friends.roblox.com/v1/users/$gettargetUserId/unfollow", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"post"]);
        return $data;
    }
    public function postv1usersunfriend($gettargetUserId)
    {
        // Unfriend a user

        $body = json_encode(
    array());
        $data = $this->postRequestWithCookie("https://friends.roblox.com/v1/users/$gettargetUserId/unfriend", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"post"]);
        return $data;
    }
    
// Badges Api v1

public function getv1badges($getbadgeId)
    {
// Gets badge information by the badge Id.
        $data = $this->getRequestWithCookie("https://badges.roblox.com/v1/badges/$getbadgeId", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function patchv1badges($getbadgeId, $name, $description, $enabled)
    {
        // Updates badge configuration.

        $body = json_encode(
    array(
    ["name"]=>"$name",
    ["description"]=>"$description",
    ["enabled"]=>"$enabled",));
        $data = $this->postRequestWithCookie("https://badges.roblox.com/v1/badges/$getbadgeId", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"patch"]);
        return $data;
    }
    public function getv1universesbadges($getuniverseId, $getsortOrder, $getlimit, $getcursor)
    {
// Gets badges by their awarding game.
        $data = $this->getRequestWithCookie("https://badges.roblox.com/v1/universes/$getuniverseId/badges?sortOrder=$sortOrder&limit=$limit&cursor=$cursor", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function getv1usersbadges($getuserId, $getsortOrder, $getlimit, $getcursor)
    {
// Gets a list of badges a user has been awarded.
        $data = $this->getRequestWithCookie("https://badges.roblox.com/v1/users/$getuserId/badges?sortOrder=$sortOrder&limit=$limit&cursor=$cursor", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function getv1usersbadgesawardeddates($getuserId, $getbadgeIds)
    {
// Gets timestamps for when badges were awarded to a user.
        $data = $this->getRequestWithCookie("https://badges.roblox.com/v1/users/$getuserId/badges/awarded-dates?badgeIds=$badgeIds", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function deletev1userbadges($getbadgeId)
    {
        // Removes a badge from the authenticated user.

        $body = json_encode(
    array());
        $data = $this->postRequestWithCookie("https://badges.roblox.com/v1/user/badges/$getbadgeId", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"delete"]);
        return $data;
    }
    
// Billing Api v1

public function getv1userpayments($getsortOrder, $getlimit, $getcursor)
    {
// Retrive the payment history for Authenticated user
        $data = $this->getRequestWithCookie("https://billing.roblox.com/v1/user/payments?sortOrder=$sortOrder&limit=$limit&cursor=$cursor", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    
public function postv1amazonpurchase($receiptId, $amazonUserId, $isRetry)
    {
        // Perform a purchase and grant desired product.

        $body = json_encode(
    array());
        $data = $this->postRequestWithCookie("https://billing.roblox.com/v1/amazon/purchase", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"post"]);
        return $data;
    }
    public function postv1amazonvalidate($productId, $currency)
    {
        // Validate a ProductId before making a purchase.

        $body = json_encode(
    array());
        $data = $this->postRequestWithCookie("https://billing.roblox.com/v1/amazon/validate", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"post"]);
        return $data;
    }
    public function postv1applepurchase($productId, $receipt, $isRetry)
    {
        // Perform a purchase and grant desired product.

        $body = json_encode(
    array());
        $data = $this->postRequestWithCookie("https://billing.roblox.com/v1/apple/purchase", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"post"]);
        return $data;
    }
    public function postv1applevalidate($productId, $currency)
    {
        // Validate a ProductId before making a purchase.

        $body = json_encode(
    array());
        $data = $this->postRequestWithCookie("https://billing.roblox.com/v1/apple/validate", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"post"]);
        return $data;
    }
    public function postv1gamecardredeem($pinCode)
    {
        // Redeem gamecards for assets and credits

        $body = json_encode(
    array());
        $data = $this->postRequestWithCookie("https://billing.roblox.com/v1/gamecard/redeem", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"post"]);
        return $data;
    }
    public function postv1gamecardreverse($PinCode, $UserId)
    {
        // Reverse a game card that was already redeemed

        $body = json_encode(
    array(
    ["PinCode"]=>"$PinCode",
    ["UserId"]=>"$UserId",));
        $data = $this->postRequestWithCookie("https://billing.roblox.com/v1/gamecard/reverse", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"post"]);
        return $data;
    }
    public function postv1googlepurchase($packageName, $productId, $token, $isRetry, $orderId)
    {
        // Perform a purchase and grant desired product.

        $body = json_encode(
    array());
        $data = $this->postRequestWithCookie("https://billing.roblox.com/v1/google/purchase", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"post"]);
        return $data;
    }
    public function postv1googlevalidate($productId, $currency)
    {
        // Validate a ProductId before making a purchase.

        $body = json_encode(
    array());
        $data = $this->postRequestWithCookie("https://billing.roblox.com/v1/google/validate", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"post"]);
        return $data;
    }
    public function postv1notificationsxsolla($notification_type, $purchase, $user, $transaction, $payment_details, $refund_details, $custom_parameters)
    {
        // Webhook for Xsolla

        $body = json_encode(
    array());
        $data = $this->postRequestWithCookie("https://billing.roblox.com/v1/notifications/xsolla", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"post"]);
        return $data;
    }
    public function postv1paymentsxsollaiframetoken($mainProductId, $upsellProductId, $paymentProviderType, $verifiedEmailOrPhone)
    {
        // Get the Xsolla iframe token

        $body = json_encode(
    array());
        $data = $this->postRequestWithCookie("https://billing.roblox.com/v1/payments/xsolla/iframe-token", $body, ["ReturnStatusCode"=>true,"CustomRequest"=>"post"]);
        return $data;
    }
    
// Captcha Api v1

public function getv1captchametadata()
    {
// Gets metadata for completing captchas.
        $data = $this->getRequestWithCookie("https://captcha.roblox.com/v1/captcha/metadata", [], ["ReturnStatusCode"=>true]);
        return $data;
    }
    

}
