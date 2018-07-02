<?php
/**
 * Created by PhpStorm.
 * User: lacy
 * Date: 3/22/2016
 * Time: 4:00 PM
 */

// *******************************************************************************************
// Functions start here


/**
 * @param $a
 * @param $b
 * @return int
 */
function compare_firstname($a, $b)
{
    return strnatcmp($a['fname'], $b['fname']);
}

// If first and last name is missing, but displayname isn't, split out displayname into first and last name
/**
 * @param $results
 * @param $debug
 * @return mixed
 */


function fixnames($results, $debug)
{
    global $db;
    $index = 0;
    foreach ($results as $e) {
        if ($e['fname'] == null && $e['displayname'] != null) {

            $e['fname'] = $e['displayname'];
            $results[$index]['fname'] = $e['fname'];
            
            $id = $e['id'];
            $fname = $e['fname'];
                       
        }
        $index++;
    }

    // $sql = "SELECT * FROM userman_users WHERE default_extension != 'none'";
//    $results = $db->getAll($sql, DB_FETCHMODE_ASSOC);

    if($debug) {echo"<pre>"; print_r($results); echo"</pre>";}
    return $results;
}

/**
 * @param $results
 * @param $debug
 * @return DOMDocument
 */
function createContactsXML ($results, $debug)
{
    $xml = new \DOMDocument('1.0');
    $xml->formatOutput = true;

    $phonebooks = $xml->createElement("phonebooks");
    $xml->appendChild($phonebooks);

    $contacts = $xml->createElement("contacts");
    $contacts->setAttribute("group_name", "PBX Directory");
    $contacts->setAttribute("editable", 0);
    $contacts->setAttribute("id", 0);
    $phonebooks->appendChild($contacts);

// This starts the contacts

    foreach ($results as $e) {
        
         // if(!empty($e['primary'])) { 
               
            $contact = $xml->createElement("contact");
            $contact->setAttribute("id", $e['username']);
            $contact->setAttribute("first_name", $e['fname']);
            $contact->setAttribute("last_name", $e['lname']);
            $contact->setAttribute("account_id", $e['username']);
            $contact->setAttribute("contact_type", "sip");
            $contact->setAttribute("organization", $e['company']);
            $contact->setAttribute("job_title", $e['title']);
            $contact->setAttribute("location", $e['location']);
            $contact->setAttribute("department", $e['department']);
            if(!empty($e['digium'])) { 
                $contact->setAttribute("subscribe_to", "auto_hint_" . $e['username']);
            } else {
                $contact->setAttribute("subscribe_to", $e['username']);
            }

            $contacts->appendChild($contact);

            if ($e['email'] != null) {
                $emails = $xml->createElement("emails");
                $contact->appendChild($emails);

                $email = $xml->createElement("email");
                $email->setAttribute("address", $e['email']);
                $email->setAttribute("label", "work");
                $email->setAttribute("primary", 1);
                $emails->appendChild($email);
            }

            $actions = $xml->createElement("actions");
            $contact->appendChild($actions);

            if ($e['username'] != null) {
                $action = $xml->createElement("action");
                $action->setAttribute("id", "primary");
                $action->setAttribute("dial", $e['username']);
                $action->setAttribute("dial_prefix", "");
                $action->setAttribute("label", "Extension");
                $action->setAttribute("name", "Office");
                $actions->appendChild($action);
            }

            if ($e['cell'] != null) {
                $action = $xml->createElement("action");
                $action->setAttribute("id", "cell");
                $action->setAttribute("dial", $e['cell']);
                $action->setAttribute("dial_prefix", "");
                $action->setAttribute("label", "Cell");
                $action->setAttribute("name", "Cell");
                $actions->appendChild($action);
            }

            if ($e['home'] != null) {
                $action = $xml->createElement("action");
                $action->setAttribute("id", "home");
                $action->setAttribute("dial", $e['home']);
                $action->setAttribute("dial_prefix", "");
                $action->setAttribute("label", "Home");
                $action->setAttribute("name", "Home");
                $actions->appendChild($action);
            }

            if(!empty($e['hasvoicemail'])) {
                $action = $xml->createElement("action");
                $action->setAttribute("id", "send_to_vm");
                $action->setAttribute("dial", $e['username']);
                $action->setAttribute("dial_prefix", "*86");
                $action->setAttribute("label", "Voicemail");
                $action->setAttribute("name", "Dial Voicemail");
                $actions->appendChild($action);
            
                $headers = $xml->createElement("headers");
                $action->appendChild($headers);

                $header = $xml->createElement("header");
                $header->setAttribute("key", "X-Digium-Call-Feature");
                $header->setAttribute("value", "feature_send_to_vm");
                $headers->appendChild($header);

                $header = $xml->createElement("header");
                $header->setAttribute("key", "Diversion");
                $header->setAttribute("value", "&lt;sip:%_ACCOUNT_USERNAME_%@%_ACCOUNT_SERVER_%:%_ACCOUNT_PORT_%&gt;;reason=&quot;send_to_vm&quot;");
                $headers->appendChild($header);
            }

            if ($e['username'] != null) {
                $action = $xml->createElement("action");
                $action->setAttribute("id", "pickupcall");
                $action->setAttribute("dial", $e['username']);
                $action->setAttribute("dial_prefix", "**");
                $action->setAttribute("label", "Pickup");
                $action->setAttribute("name", "Pickup");
                $actions->appendChild($action);
            }

            
            if ($e['username'] != null) {
                $action = $xml->createElement("action");
                $action->setAttribute("id", "anintercom");
                $action->setAttribute("dial", $e['username']);
                $action->setAttribute("dial_prefix", "*42");
                $action->setAttribute("label", "Intercom");
                $action->setAttribute("name", "Intercom");
                $actions->appendChild($action);

                $headers = $xml->createElement("headers");
                $action->appendChild($headers);

                $header = $xml->createElement("header");
                $header->setAttribute("key", "X-Digium-Call-Feature");
                $header->setAttribute("value", "feature_intercom");
                $headers->appendChild($header);
            }

            if ($e['username'] != null) {
                $action = $xml->createElement("action");
                $action->setAttribute("id", "mymonitor");
                $action->setAttribute("dial", $e['username']);
                $action->setAttribute("dial_prefix", "");
                $action->setAttribute("label", "Monitor");
                $action->setAttribute("name", "Monitor");
                $actions->appendChild($action);

                $headers = $xml->createElement("headers");
                $action->appendChild($headers);

                $header = $xml->createElement("header");
                $header->setAttribute("key", "X-Digium-Call-Feature");
                $header->setAttribute("value", "feature_monitor");
                $headers->appendChild($header);
            }

            $action = $xml->createElement("action");
            $action->setAttribute("id", "blindxfer");
            $action->setAttribute("dial", $e['username']);
            $action->setAttribute("dial_prefix", "");
            $action->setAttribute("label", "BlindXfer");
            $action->setAttribute("name", "Blind Transfer");
            $actions->appendChild($action);
         // }
    }

    // Create Night Mode Contact
    $contact = $xml->createElement("contact");
    $contact->setAttribute("id", "**25**");
    $contact->setAttribute("first_name", "Night Mode");
    $contact->setAttribute("last_name", "");
    $contact->setAttribute("account_id", "**25**");
    $contact->setAttribute("contact_type", "sip");
    $contact->setAttribute("organization", "3E");
    $contact->setAttribute("location", "La Porte");
    $contact->setAttribute("subscribe_to", "**25**");

    $contacts->appendChild($contact);

    $actions = $xml->createElement("actions");
    $contact->appendChild($actions);

    $action = $xml->createElement("action");
    $action->setAttribute("id", "primary");
    $action->setAttribute("dial", "**25**");
    $action->setAttribute("dial_prefix", "");
    $action->setAttribute("label", "Extension");
    $action->setAttribute("name", "Office");
    $actions->appendChild($action);
    // End Night Mode contact



    return $xml;
}

function createTextContacts($results, $debug)
{
    $myfile = fopen("contacts.txt", "w") or die("Unable to open file!");
    $index = 0;
    foreach ($results as $e) {
        $txt = $e['username'] . "," . $e['fname'] . " " . $e['lname'] . "\n";
        fwrite($myfile,$txt);
    }
    fclose($myfile);
}

/**
 * @param $results
 * @param $debug
 * @return DOMDocument
 */
function createSmartBLFXML($results, $debug, $d50)
{
    $xml = new \DOMDocument('1.0');
    $xml->formatOutput = true;

    $config = $xml->createElement("config");
    $xml->appendChild($config);

    $smart_blf = $xml->createElement("smart_blf");
    $config->appendChild($smart_blf);

    $blf_items = $xml->createElement("blf_items");
    $smart_blf->appendChild($blf_items);

    $index = 0;
    $paging = 1;

    if ($d50 != "1") {
        $blf_item = $xml->createElement("blf_item");
        $blf_item->setAttribute("location", "side");
        $blf_item->setAttribute("index", $index);
        $blf_item->setAttribute("paging", $paging);
        $blf_item->setAttribute("contact_id", "**25**");
        $blf_items->appendChild($blf_item);

        $behaviors = $xml->createElement("behaviors");
        $blf_item->appendChild($behaviors);

        $behavior = $xml->createElement("behavior");
        $behavior->setAttribute("phone_state", "idle");
        $behavior->setAttribute("target_status", "idle");
        $behavior->setAttribute("press_action", "regular_dial");
        $behavior->setAttribute("press_function", "dial");
        $behaviors->appendChild($behavior);

        $behavior = $xml->createElement("behavior");
        $behavior->setAttribute("phone_state", "idle");
        $behavior->setAttribute("target_status", "on_the_phone");
        $behavior->setAttribute("press_action", "regular_dial");
        $behavior->setAttribute("press_function", "dial");
        $behaviors->appendChild($behavior);

        $indicators = $xml->createElement("indicators");
        $blf_item->appendChild($indicators);

        $indicator = $xml->createElement("indicator");
        $indicator->setAttribute("target_status", "idle");
        $indicator->setAttribute("led_color", "green");
        $indicator->setAttribute("led_state", "on");
        $indicators->appendChild($indicator);

        $indicator = $xml->createElement("indicator");
        $indicator->setAttribute("target_status", "on_the_phone");
        $indicator->setAttribute("led_color", "red");
        $indicator->setAttribute("led_state", "on");
        $indicators->appendChild($indicator);


        $index++;

    }

    foreach ($results as $e) {

        if((!empty($e['primary']) && $e['fname'] != "Test") | !empty($d50)) {
            $blf_item = $xml->createElement("blf_item");
            $blf_item->setAttribute("location", "side");
            $blf_item->setAttribute("index", $index);
            $blf_item->setAttribute("paging", $paging);
            $blf_item->setAttribute("contact_id", $e['username']);
            $blf_items->appendChild($blf_item);

            $behaviors = $xml->createElement("behaviors");
            $blf_item->appendChild($behaviors);

            $behavior = $xml->createElement("behavior");
            $behavior->setAttribute("phone_state", "idle");
            $behavior->setAttribute("target_status", "idle");
            $behavior->setAttribute("press_action", "regular_dial");
            $behavior->setAttribute("press_function", "dial");
            $behaviors->appendChild($behavior);

            $behavior = $xml->createElement("behavior");
            $behavior->setAttribute("phone_state", "idle");
            $behavior->setAttribute("target_status", "idle");
            $behavior->setAttribute("long_press_action", "anintercom");
            $behavior->setAttribute("long_press_function", "dial");
            $behaviors->appendChild($behavior);

            $behavior = $xml->createElement("behavior");
            $behavior->setAttribute("phone_state", "idle");
            $behavior->setAttribute("target_status", "on_the_phone");
            $behavior->setAttribute("press_action", "call_vm");
            $behavior->setAttribute("press_function", "dial");
            $behaviors->appendChild($behavior);

            $behavior = $xml->createElement("behavior");
            $behavior->setAttribute("phone_state", "idle");
            $behavior->setAttribute("target_status", "on_the_phone");
            $behavior->setAttribute("long_press_action", "mymonitor");
            $behavior->setAttribute("long_press_function", "dial");
            $behaviors->appendChild($behavior);

            $behavior = $xml->createElement("behavior");
            $behavior->setAttribute("phone_state", "idle");
            $behavior->setAttribute("target_status", "ringing");
            $behavior->setAttribute("long_press_action", "pickupcall");
            $behavior->setAttribute("long_press_function", "dial");
            $behaviors->appendChild($behavior);

            $behavior = $xml->createElement("behavior");
            $behavior->setAttribute("phone_state", "hold/transfer");
            $behavior->setAttribute("press_action", "blindxfer");
            $behavior->setAttribute("press_function", "transfer");
            $behaviors->appendChild($behavior);

            $behavior = $xml->createElement("behavior");
            $behavior->setAttribute("phone_state", "incoming/transfer");
            $behavior->setAttribute("press_action", "blindxfer");
            $behavior->setAttribute("press_function", "transfer");
            $behaviors->appendChild($behavior);

            $behavior = $xml->createElement("behavior");
            $behavior->setAttribute("phone_state", "incoming");
            $behavior->setAttribute("press_action", "blindxfer");
            $behavior->setAttribute("press_function", "transfer");
            $behaviors->appendChild($behavior);

            $behavior = $xml->createElement("behavior");
            $behavior->setAttribute("phone_state", "connected");
            $behavior->setAttribute("press_action", "blindxfer");
            $behavior->setAttribute("press_fuction", "transfer");
            $behaviors->appendChild($behavior);

            $indicators = $xml->createElement("indicators");
            $blf_item->appendChild($indicators);

            $indicator = $xml->createElement("indicator");
            $indicator->setAttribute("target_status", "idle");
            $indicator->setAttribute("led_color", "green");
            $indicator->setAttribute("led_state", "on");
            $indicators->appendChild($indicator);

            $indicator = $xml->createElement("indicator");
            $indicator->setAttribute("target_status", "ringing");
            $indicator->setAttribute("led_color", "green");
            $indicator->setAttribute("led_state", "fast");
            $indicators->appendChild($indicator);

            $indicator = $xml->createElement("indicator");
            $indicator->setAttribute("target_status", "on_the_phone");
            $indicator->setAttribute("led_color", "red");
            $indicator->setAttribute("led_state", "on");
            $indicators->appendChild($indicator);

            $indicator = $xml->createElement("indicator");
            $indicator->setAttribute("target_status", "on_hold");
            $indicator->setAttribute("led_color", "red");
            $indicator->setAttribute("led_state", "slow");
            $indicators->appendChild($indicator);


            if ($index == 9) {
                $index = -1;
                $paging++;
            }

            $index++;
        }

    }
    if ($debug) {
        // echo "<pre>";
        // echo "<xmp>" . $xml->saveXML() . "</xmp>";
        // echo "</pre>";
    }
    return $xml;
}

function createSmartBlfExt ($results, $debug, $blf, $smart_blf_ext_cfg)
{
    foreach ($blf as $e) {
        $a = explode(",",$e);

        $ext = $a[0];
        $count = count($a);
        // echo $ext . " has " . $count . "<br>";
        $i = 0;
        while ($i < $count-1) {
            $s[$i]=trim($a[$i+1]);
            $i++;
        }

        // echo "<pre>";
        // print_r($s);
        // print_r($e);
        // print_r($results);
        // echo "<br>";
        $newResults = null;
        $i = 0;
        // This needs to be modified to support Primary Location Only
        foreach ($s as $c) {
            $key = returnKey($results, "username", $c);
            

            // echo "Extension: " . $c . "<br>";
            // echo "Location: " . $key . "<br>";
            $nR[$i] = $results[$key];

            $i++;
//
        }
        // echo "<pre>";print_r($nR);echo "</pre>";
        $d50 = 1;
        $xml = createSmartBLFXML($nR, $debug, $d50);
        // $xml->save("smartblf-$ext.xml");
        $xml->save($smart_blf_ext_cfg . "smartblf-$ext.xml");
        echo "Copied smartblf-$ext.xml... <br>";

    }
}

function returnKey($products, $field, $value) {
    foreach ($products as $key => $product)
    {
        if ( $product[$field] == $value )
            return $key;
    }
    return false;
}

/**
 * @param $results
 */
function createResDigPhoneConf($results)
{
    $txt = "";
    foreach ($results as $e) {
        $txt = $txt . "[" . $e['mac'] . "](phone)\n";
        $txt = $txt . "line=" . $e['username'] . "\n";
        $txt = $txt . "mac=" . $e['mac'] . "\n";
        $txt = $txt . "alert=myfancyalert\n";
        $txt = $txt . "blf_contact_group=PBX Directory\n";
        if ( $e['model'] == "D50" ) {
            $txt = $txt . "blf_items=smartblf-" . $e['username'] . ".xml\n";
        } elseif ( $e['model'] == "D70" ) {
            $txt = $txt . "blf_items=smartblf.xml\n";
        }
        if ($e['company'] == "INC") {
            $txt = $txt . "d70_logo_file=INC_D70.png\n";
            $txt = $txt . "d50_logo_file=INC_D50.png\n";
            $txt = $txt . "d40_logo_file=INC_D50.png\n";
            $txt = $txt . "d45_logo_file=INC_D50.png\n";
            $txt = $txt . "d60_logo_file=INC_D60.png\n";
            $txt = $txt . "d62_logo_file=INC_D60.png\n";
            $txt = $txt . "d65_logo_file=INC_D60.png\n";
        }

        $txt = $txt . "full_name=" . $e['fname'] . " " . $e['lname'] . "\n";
        $txt = $txt . "\n";
       }
    $txtPhone = $txt;

    $txt = "";
    foreach ($results as $e) {
        $txt = $txt . "[" . $e['username'] . "](line)\n";
        $txt = $txt . "line_label=" . $e['username'] . " " . $e['fname'] . "\n";
        if ( $e['hasvoicemail'] == "y" )
            $txt = $txt . "mailbox=" . $e['username'] . "@default\n";
        $txt = $txt . "exten=" . $e['username'] . "\n";
        $txt = $txt . "\n";
    }

    $txtLine = $txt;

    //echo $txtPhone;
    //echo $txtLine;

    $myfile = fopen("res_digium_phones.conf","w") or die("Unable to open file!");
    fwrite($myfile, $txtPhone);
    fclose($myfile);

    $myfile = fopen("res_digium_lines.conf","w") or die("Unable to open file!");
    fwrite($myfile, $txtLine);
    fclose($myfile);

    return;

}

function createPjsip_PhonesConf($results)
{
    $txt = "";
    // echo "<pre>";
    foreach ($results as $e) {

        $password = random_str(32);
        $username = random_str(32);

        $txt = $txt . "[" . $e['username'] . "](endpoint-internal-D70)\n";
        $txt = $txt . "auth=" . $e['username'] . "\n";
        $txt = $txt . "aors=" . $e['username'] . "\n";
        $txt = $txt . "callerid=" . $e['fname'] . " " . $e['lname'] . "<" . $e['username'] . ">\n";
        $txt = $txt . "\n";
        $txt = $txt . "[" . $e['username'] . "](auth-userpass)\n";
        $txt = $txt . "password=" . $password . "\n";
        $txt = $txt . "username=" . $username . "\n";
        $txt = $txt . "\n";
        $txt = $txt . "[" . $e['username'] . "](aor-single-reg)\n";
        if ( $e['hasvoicemail'] == "y" )
            $txt = $txt . "mailboxes=" . $e['username'] . "@default\n";
        $txt = $txt . "\n";
       }

    $txtPhone = $txt;

    // echo $txtPhone;
    

    $myfile = fopen("pjsip_phones.conf","w") or die("Unable to open file!");
    fwrite($myfile, $txtPhone);
    fclose($myfile);
    
    // echo "</pre>";
    return;

}

// Not used anywhere
function fixSipDb($resultsSip, $results)
{

    // echo "<pre>";
    // print_r($resultsSip);
    // echo "<br>";
    echo "<pre>";
    echo "Starting fix Sip DB function \n";

    $index=0;

    foreach ($results as $e) {
        $callerid[$e['username']] = $e['fname'] . " " . $e['lname'];
        $fname[$e['username']] = $e['fname'];
    }

    // print_r($callerid);

    foreach ($resultsSip as $e) {

        if ($e['keyword'] == "callerid") {
            
            

            $idReplace = $callerid[$e['id']] . " <" . $e['id'] . ">";
            
            
            $l = $e['id'];
            $lookFor = "device <$l>";
            $lookFor2 = $fname[$e['id']] . " <" . $e['id'] . ">";
            $lookFor3 = substr($e['data'], strlen($lookFor2[$e['id']]));

            
            // echo "Caller ID: " . $e['data'] . "\n";
            // echo "Extension: " . $e['id'] . "\n";
            // echo "Real Caller ID: " . $callerid[$e['id']] . "\n";
            // echo "First name: " . $fname[$e['id']] . "\n";
            // echo "Replacement: $idReplace \n";
            // echo "Replacing with $idReplace \n";
            // echo "Looking for $lookFor \n";
            // echo "Also Looking for $lookFor2 \n";
            // echo "And also looking for $lookFor3 \n";
            // echo "Length " . strlen($lookFor2) . "\n";
            
            // For Follow Me Extensions
            if ($e['id'] == "247") { 
                // echo "== Gonna replace == " . $e['id'] . "\n";
                $idReplace = "Leonid Shikhman <238>";
                sql("UPDATE sip SET DATA='$idReplace' WHERE DATA='$lookFor3'");
                // database put AMPUSER/247 cidname Leonid Shikhman
                // database put AMPUSER/247 cidnum 238
                
                // $debug = true;
                $output = shell_exec('asterisk -rx "database put AMPUSER/247 cidname \"Leonid Shikhman\""');
//                if ($debug) { echo "<pre>$output</pre>"; }
                $output = shell_exec('asterisk -rx "database put AMPUSER/247 cidnum 238"');
//                if ($debug) { echo "<pre>$output</pre>"; }
                // $debug = false;
            }
            // End Follow Me Extensions

            if (($e['data'] == $lookFor && $callerid[$e['id']] != "")) {
                // echo "== Gonna replace == " . $e['id'] . "\n";
                 
                sql("UPDATE sip SET DATA='$idReplace' WHERE DATA='$lookFor'");


            }

            if ($lookFor3 == $lookFor2) {
                // echo "-- Gonna Replace -- " . $e['id'] . "\n";

                sql("UPDATE sip SET DATA='$idReplace' WHERE DATA='$lookFor3'");

            }
            // echo "\n";

            
        }
    }
    echo "Finished fix Sip DB function \n";
    echo "</pre>";
    return;            
}

function readContacts($contacts)
{
    $c=count($contacts);
    $i=0;
    $d=0;
    echo "Total Number of contacts $c<br><br>";
    foreach ($contacts as $e) {
       // print_r($e);
        if($i == 0){ $i++; continue;}
        $d = $i -1;
        $a = explode(",",$e);
        $a[16]=trim($a[16]);
        $results[$d]['fname']=$a[0];
        $results[$d]['lname']=$a[1];
        $results[$d]['username']=$a[2];
        $results[$d]['mac']=$a[3];
        $results[$d]['email']=$a[4];
        $results[$d]['building']=$a[5];
        $results[$d]['location']=$a[6];
        $results[$d]['cell']=$a[7];
        $results[$d]['home']=$a[8];
        $results[$d]['department']=$a[9];
        $results[$d]['company']=$a[10];
        $results[$d]['title']=$a[11];
        $results[$d]['dial']=$a[12];
        $results[$d]['primary']=$a[13];
        $results[$d]['hasvoicemail']=$a[14];
        $results[$d]['digium']=$a[15];
        $results[$d]['model']=$a[16];

        // image





        $i++;
    }
return $results;
}

function createButtons ($results) {
    $txt = <<<EOF
[PARK/default]
label=Parking
type=park
extension=700
context=parkedcalls
\n
[SIP/3eanalog]
type=trunk
label=External
\n
EOF;

    $txt = $txt . "[Custom:Close]\n";
    $txt = $txt . "type=extension\n";
    $txt = $txt . "extension=**25**\n";
    $txt = $txt . "context=from-internal\n";
    $txt = $txt . "label=Night Mode\n";
    $txt = $txt . "channel=Custom:Close\n";
    $txt = $txt . "\n";

    foreach ($results as $e) {
        if(empty($e['username'])) {continue;}
        $txt = $txt . "[PJSIP/" . $e['username'] . "]\n";
        $txt = $txt . "type=extension\n";
        $txt = $txt . "extension=" . $e['username'] . "\n";
        $txt = $txt . "context=from-internal\n";
        $txt = $txt . "label=" . $e['fname'] . " " . $e['lname'] . "\n";
        if(!empty($e['hasvoicemail'])) {
            $txt = $txt . "mailbox=" . $e['username'] . "@default\n";
            $txt = $txt . "extenvoicemail=*86" . $e['username'] . "@default\n";
        }
        if(!empty($e['cell'])) {
            $txt = $txt . "external=" . $e['cell'] . "@from-internal\n";
        }
        $txt = $txt . "\n";
       }

    // $txtPhone = $txt;
    // echo "<pre>". $txt ."</pre>";
    return $txt;
}

/**
 * Generate a random string, using a cryptographically secure 
 * pseudorandom number generator (random_int)
 * 
 * For PHP 7, random_int is a PHP core function
 * For PHP 5.x, depends on https://github.com/paragonie/random_compat
 * 
 * @param int $length      How many characters do we want?
 * @param string $keyspace A string of all possible characters
 *                         to select from
 * @return string
 */
function random_str($length, $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ')
{
    $pieces = [];
    $max = mb_strlen($keyspace, '8bit') - 1;
    for ($i = 0; $i < $length; ++$i) {
        $pieces []= $keyspace[random_int(0, $max)];
    }
    return implode('', $pieces);
}

function createVoicemail ($results)
{
    $txt = "";
    foreach ($results as $e) {
        if (empty($e['username'])) {
            continue;
        }

        if (strtoupper($e['hasvoicemail'])=="Y") {
            $txt = $txt . $e['username'] . " => " . $e['username'] . "," . $e['fname'] . " " . $e['lname'] . "," . $e['email'] . "\n";
        }


    }

    $base = file_get_contents("voicemail-base.conf");
    $txt = $base . $txt;
//    echo "<pre>" . $txt . "</pre>";
    return $txt;
}

function newUsermanCreateContactsXML ($userman, $extensions)
{
    $xml = new \DOMDocument('1.0');
    $xml->formatOutput = true;

    $phonebooks = $xml->createElement("phonebooks");
    $xml->appendChild($phonebooks);

    $contacts = $xml->createElement("contacts");
    $contacts->setAttribute("group_name", "PBX Directory");
    $contacts->setAttribute("editable", 0);
    $contacts->setAttribute("id", 0);
    $phonebooks->appendChild($contacts);

// This starts the contacts
    foreach ($extensions as $e) {
        foreach ($userman as $u) {
            if ($e["0"] == $u["default_extension"]) {
                $contact = $xml->createElement("contact");
                $contact->setAttribute("id", $u['default_extension']);
                $contact->setAttribute("first_name", $u['fname']);
                $contact->setAttribute("last_name", $u['lname']);
                $contact->setAttribute("account_id", $u['default_extension']);
                $contact->setAttribute("contact_type", "sip");
                $contact->setAttribute("organization", $u['company']);
                $contact->setAttribute("job_title", $u['title']);
                $contact->setAttribute("department", $u['department']);
                //TODO Add hooks to userman and bulk handler for additional field for location and digium.
//        if(!empty($e['digium'])) {
//            $contact->setAttribute("subscribe_to", "auto_hint_" . $e['username']);
//        } else {
//            $contact->setAttribute("subscribe_to", $e['username']);
//        }
                $contact->setAttribute("subscribe_to", "auto_hint_" . $u['username']);

                $contacts->appendChild($contact);

                if ($u['email'] != null) {
                    $emails = $xml->createElement("emails");
                    $contact->appendChild($emails);

                    $email = $xml->createElement("email");
                    $email->setAttribute("address", $u['email']);
                    $email->setAttribute("label", "work");
                    $email->setAttribute("primary", 1);
                    $emails->appendChild($email);
                }

                $actions = $xml->createElement("actions");
                $contact->appendChild($actions);

                if ($u['default_extension'] != null) {
                    $action = $xml->createElement("action");
                    $action->setAttribute("id", "primary");
                    $action->setAttribute("dial", $u['default_extension']);
                    $action->setAttribute("dial_prefix", "");
                    $action->setAttribute("label", "Extension");
                    $action->setAttribute("name", "Office");
                    $actions->appendChild($action);
                }

                if ($u['cell'] != null) {
                    $action = $xml->createElement("action");
                    $action->setAttribute("id", "cell");
                    $action->setAttribute("dial", $u['cell']);
                    $action->setAttribute("dial_prefix", "");
                    $action->setAttribute("label", "Cell");
                    $action->setAttribute("name", "Cell");
                    $actions->appendChild($action);
                }

                if ($u['home'] != null) {
                    $action = $xml->createElement("action");
                    $action->setAttribute("id", "home");
                    $action->setAttribute("dial", $u['home']);
                    $action->setAttribute("dial_prefix", "");
                    $action->setAttribute("label", "Home");
                    $action->setAttribute("name", "Home");
                    $actions->appendChild($action);
                }

                if(($e['2'] !== "novm")) {
                    $action = $xml->createElement("action");
                    $action->setAttribute("id", "send_to_vm");
                    $action->setAttribute("dial", $u['default_extension']);
                    $action->setAttribute("dial_prefix", "*86");
                    $action->setAttribute("label", "Voicemail");
                    $action->setAttribute("name", "Dial Voicemail");
                    $actions->appendChild($action);

                    $headers = $xml->createElement("headers");
                    $action->appendChild($headers);

                    $header = $xml->createElement("header");
                    $header->setAttribute("key", "X-Digium-Call-Feature");
                    $header->setAttribute("value", "feature_send_to_vm");
                    $headers->appendChild($header);

                    $header = $xml->createElement("header");
                    $header->setAttribute("key", "Diversion");
                    $header->setAttribute("value", "&lt;sip:%_ACCOUNT_USERNAME_%@%_ACCOUNT_SERVER_%:%_ACCOUNT_PORT_%&gt;;reason=&quot;send_to_vm&quot;");
                    $headers->appendChild($header);
                }

                if ($u['default_extension'] != null) {
                    $action = $xml->createElement("action");
                    $action->setAttribute("id", "pickupcall");
                    $action->setAttribute("dial", $u['default_extension']);
                    $action->setAttribute("dial_prefix", "**");
                    $action->setAttribute("label", "Pickup");
                    $action->setAttribute("name", "Pickup");
                    $actions->appendChild($action);
                }


                if ($u['default_extension'] != null) {
                    $action = $xml->createElement("action");
                    $action->setAttribute("id", "anintercom");
                    $action->setAttribute("dial", $u['default_extension']);
                    $action->setAttribute("dial_prefix", "*42");
                    $action->setAttribute("label", "Intercom");
                    $action->setAttribute("name", "Intercom");
                    $actions->appendChild($action);

                    $headers = $xml->createElement("headers");
                    $action->appendChild($headers);

                    $header = $xml->createElement("header");
                    $header->setAttribute("key", "X-Digium-Call-Feature");
                    $header->setAttribute("value", "feature_intercom");
                    $headers->appendChild($header);
                }

                if ($u['default_extension'] != null) {
                    $action = $xml->createElement("action");
                    $action->setAttribute("id", "mymonitor");
                    $action->setAttribute("dial", $u['default_extension']);
                    $action->setAttribute("dial_prefix", "");
                    $action->setAttribute("label", "Monitor");
                    $action->setAttribute("name", "Monitor");
                    $actions->appendChild($action);

                    $headers = $xml->createElement("headers");
                    $action->appendChild($headers);

                    $header = $xml->createElement("header");
                    $header->setAttribute("key", "X-Digium-Call-Feature");
                    $header->setAttribute("value", "feature_monitor");
                    $headers->appendChild($header);
                }

                $action = $xml->createElement("action");
                $action->setAttribute("id", "blindxfer");
                $action->setAttribute("dial", $u['default_extension']);
                $action->setAttribute("dial_prefix", "");
                $action->setAttribute("label", "BlindXfer");
                $action->setAttribute("name", "Blind Transfer");
                $actions->appendChild($action);

                if ($u['dn'] !== $e[1]) {
                    echo "name not equal -> " . $u['dn'] . "<br>";
                    $e[3] = null;
                    $e[1] = $u['dn'];
                    $file = fopen("ext-update.csv", "w");
                    $c = array(
                        array("extension","name"),
                        array($e[0],$e[1])
                    );
                    foreach ($c as $r) {
                        fputcsv($file,$r);
                    }

                    fclose($file);

                    $exec = "fwconsole bi --type='extensions' ext-update.csv";
                    $exec = shell_exec($exec);
                    echo "Updated extensions... " . $exec . "<br>";

                }


                $e[3] = "found";


            }


        }

        if ($e[3] !== "found") {

            // Standard contact
            $contact = $xml->createElement("contact");
            $contact->setAttribute("id", $e["0"]);
            $contact->setAttribute("first_name", $e["1"]);
            $contact->setAttribute("last_name", "");
            $contact->setAttribute("account_id", $e["0"]);
            $contact->setAttribute("contact_type", "sip");
            $contact->setAttribute("subscribe_to", $e["0"]);

            $contacts->appendChild($contact);

            $actions = $xml->createElement("actions");
            $contact->appendChild($actions);

            $action = $xml->createElement("action");
            $action->setAttribute("id", "primary");
            $action->setAttribute("dial", $e["0"]);
            $action->setAttribute("dial_prefix", "");
            $action->setAttribute("label", "Extension");
            $action->setAttribute("name", "Office");
            $actions->appendChild($action);

        }

        echo $e[1] . " > " . $e[3] . "<br>";

    }

    // Create Night Mode Contact
    $contact = $xml->createElement("contact");
    $contact->setAttribute("id", "**25**");
    $contact->setAttribute("first_name", "Night Mode");
    $contact->setAttribute("last_name", "");
    $contact->setAttribute("account_id", "**25**");
    $contact->setAttribute("contact_type", "sip");
    $contact->setAttribute("organization", "3E");
    $contact->setAttribute("location", "La Porte");
    $contact->setAttribute("subscribe_to", "**25**");

    $contacts->appendChild($contact);

    $actions = $xml->createElement("actions");
    $contact->appendChild($actions);

    $action = $xml->createElement("action");
    $action->setAttribute("id", "primary");
    $action->setAttribute("dial", "**25**");
    $action->setAttribute("dial_prefix", "");
    $action->setAttribute("label", "Extension");
    $action->setAttribute("name", "Office");
    $actions->appendChild($action);
    // End Night Mode contact

    return $xml;
}
