<?php

/**
 * @param $userman
 * @param $extensions
 *
 * @return \DOMDocument
 */
function newUsermanCreateContactsXML ($adUser)
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
    foreach ($adUser as $u) {
        if($u['Change Name']=="n") {
            $u['fname'] = substr_replace ("%","",$u['extFName']);
            $u['lname'] = substr_replace ("%","",$u['extLName']);

        }

        $contact = $xml->createElement("contact");
        $contact->setAttribute("id", $u['Extension']);
        $contact->setAttribute("first_name", $u['fname']);
        $contact->setAttribute("last_name", $u['lname']);
        $contact->setAttribute("account_id", $u['Extension']);
        $contact->setAttribute("contact_type", "sip");
        $contact->setAttribute("organization", $u['company']);
        $contact->setAttribute("job_title", $u['title']);
        $contact->setAttribute("department", $u['department']);
        if($u['digium'] == "y") {
            $contact->setAttribute ("subscribe_to", "auto_hint_" . $u['Extension']);
        } else {
            $contact->setAttribute ("subscribe_to", $u['Extension']);
        }
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
        if ($u['Extension'] != null) {
            $action = $xml->createElement("action");
            $action->setAttribute("id", "primary");
            $action->setAttribute("dial", $u['Extension']);
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
        if(($u['hasvoicemail'] == "y")) {
                $action = $xml->createElement("action");
                $action->setAttribute("id", "send_to_vm");
                $action->setAttribute("dial", $u['Extension']);
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
        if ($u['Extension'] != null) {
            $action = $xml->createElement("action");
            $action->setAttribute("id", "pickupcall");
            $action->setAttribute("dial", $u['Extension']);
            $action->setAttribute("dial_prefix", "**");
            $action->setAttribute("label", "Pickup");
            $action->setAttribute("name", "Pickup");
            $actions->appendChild($action);

            $action = $xml->createElement("action");
            $action->setAttribute("id", "anintercom");
            $action->setAttribute("dial", $u['Extension']);
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

            $action = $xml->createElement("action");
            $action->setAttribute("id", "mymonitor");
            $action->setAttribute("dial", $u['Extension']);
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
        $action->setAttribute("dial", $u['Extension']);
        $action->setAttribute("dial_prefix", "");
        $action->setAttribute("label", "BlindXfer");
        $action->setAttribute("name", "Blind Transfer");
        $actions->appendChild($action);
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


function correctFreePBXExtensions ($adUsers, $extensions) {
    //TODO Needs to compare to FreePBX Extensions and write out corrected CSV
    $st="";
    $f = "ext-update.csv";
    $file = fopen($f, "w");
    $c = array("extension","name");
    fputcsv($file,$c);

//    print_r ($extensions);
    foreach ($extensions as $e) {
        if($adUsers[$e[0]]['changename'] != "n") {
            if($adUsers[$e[0]]['displayname'] != $e[1]) {
                if($adUsers[$e[0]]['displayname'] !="") {
                    echo "Mismatch AD: " . $adUsers[$e[0]]['displayname'] . " & Ext: " . $e[1] . " for extension ". $e[0] . "<br>";
                    $c = array($e[0],$adUsers[$e[0]]['displayname']);
                    fputcsv($file,$c);
                }
            }
        }
    }
    $st = fclose($file);

    $exec = "fwconsole bi --type='extensions' ext-update.csv";
    $exec = shell_exec($exec);
    echo $exec;

    $fileName = basename('ext-update.csv');
    $filePath = $fileName;
    if(!empty($fileName) && file_exists($filePath)){
        // Define headers
        header("Cache-Control: public");
        header("Content-Description: File Transfer");
        header("Content-Disposition: attachment; filename=$fileName");
        header("Content-Type: application/zip");
        header("Content-Transfer-Encoding: binary");
        ob_clean();
        flush();
        // Read the file
        readfile($filePath);
        exit;
    }else{
        echo 'The file does not exist.';
    }

    return($st);




}

// Not Used
function matchUserExten($users, $extensions, $contacts) {
    $newValues = array();
    foreach ($extensions as $e) {
        foreach ($users as $key => $value) {

//            print_r($value);
            if ($e["0"] == $value["default_extension"]) {
                $u['vm'] = $e[2];
                /** @var TYPE_NAME $newValues */
                $newValues[$key] = $value;
                $newValues[$key] += ["vm" => $e[2]];
//                array_push($newValues[$key], ["vm" => $e[2]]);
//                $users[$u['vm']] = $e[2];
            }
        }

    }
//    print_r($newValues);
    $excel = readContacts ($contacts);

    $newValues1 = array();

        foreach ($newValues as $key => $value) {

//            print_r($value);
            if ($value['default_extension'] !== "none") {

                $k = returnKey($excel, "username", $value["default_extension"]);

                if (intval($k) > 0) {
    //                print "k is " . $k . ": " . $excel[$k]["username"] . "<br>";
    //                print_r($excel[$k]);

                    $e = $excel[$k];
                    /** @var TYPE_NAME $newValues */
                    $newValues1[$key] = $value;

                    $newValues1[$key] += ["mac" => trim($e["mac"], '"')];
                    $newValues1[$key] += ["model" => trim($e["model"], '"')];
                    $newValues1[$key] += ["digium" => trim($e["digium"], '"')];
                    $newValues1[$key] += ["hasvoicemail" => trim($e["hasvoicemail"], '"')];
                    $newValues1[$key] += ["location" => trim($e["location"], '"')];
                    $newValues1[$key] += ["building" => trim($e["building"], '"')];
                    //                array_push($newValues[$key], ["vm" => $e[2]]);
                    //                $users[$u['vm']] = $e[2];
                } else {
                    $newValues1[$key] = $value;
                }
            }
        }




//    print_r($newValues1);

    return($newValues1);
}

function compare_firstname($a, $b)
{
    return strnatcmp($a['fname'], $b['fname']);
}

/**
 * @param $results
 * @param $debug
 * @param $d50
 *
 * @return \DOMDocument
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

        if((!empty($e['Extension']) && $e['fname'] != "Test") | !empty($d50)) {
            $blf_item = $xml->createElement("blf_item");
            $blf_item->setAttribute("location", "side");
            $blf_item->setAttribute("index", $index);
            $blf_item->setAttribute("paging", $paging);
            $blf_item->setAttribute("contact_id", $e['Extension']);
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

//         echo "<pre>";
//         echo "<xmp>" . $xml->saveXML() . "</xmp>";
//         echo "</pre>";

    return $xml;
}

function returnKey($products, $field, $value) {
    foreach ($products as $key => $product)
    {
//        print "Field: " . $product[$field] . "<br>";
        if ( trim($product[$field], '"') == trim($value, '"') )
            return $key;
    }
    return false;
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
            $key = returnKey($results, "Extension", $c);


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
    var_dump($results);
    return $results;
}

/**
 * @param $results
 * @param $adUser
 */
function createResDigPhoneConf($results, $adUser)
{
    $txt = "";
    echo "Starting to create digium files...<br>";

    foreach ($adUser as $e) {
//        print_r ($e);
        if ($e['digium'] == "y") {
            echo "Creating " . $e['Extension'] . "...<br>";
            if ($e["mac"] == "" | $e["mac"] == null) {
                $txt = $txt . "[" . $e['Extension'] . "](phone)\n";
            } else {
                $txt = $txt . "[" . $e['mac'] . "](phone)\n";
            }
            $txt = $txt . "line=" . $e['Extension'] . "\n";

            if ($e["mac"] == "" | $e["mac"] == null) {
            } else {
                $txt = $txt . "mac=" . $e['mac'] . "\n";
            }
            $txt = $txt . "alert=myfancyalert\n";
            $txt = $txt . "blf_contact_group=PBX Directory\n";

            if ($e['model'] == "D50") {
                $txt = $txt . "blf_items=smartblf-" . $e['Extension'] . ".xml\n";
            } elseif ($e['model'] == "D70") {
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
        } else {echo $e['Extension'] . " is not a digium phone. Nothing created...<br>";}
    }
    $txtPhone = $txt;
    echo "Finished Digium phones...<br>";
    $txt = "";
    echo "Starting Digium lines...<br>";
    foreach ($results as $e) {
        if ($e['digium'] = "y") {
            $txt = $txt . "[" . $e['Extension'] . "](line)\n";
            $txt = $txt . "line_label=" . $e['Extension'] . " " . $e['fname'] . "\n";
            if ($e['hasvoicemail'] == "y")
                $txt = $txt . "mailbox=" . $e['Extension'] . "@default\n";
            $txt = $txt . "exten=" . $e['Extension'] . "\n";
            $txt = $txt . "\n";
        }
    }

    $txtLine = $txt;

    //echo $txtPhone;
    //echo $txtLine;
    //TODO Modify filenames
    echo "Writing Digium phones file...<br>";
    $myfile = fopen("res_digium_phones_add.conf","w") or die("Unable to open file!");
    fwrite($myfile, $txtPhone);
    fclose($myfile);

    echo "Writing Digium lines file...<br>";
    $myfile = fopen("res_digium_lines_add.conf","w") or die("Unable to open file!");
    fwrite($myfile, $txtLine);
    fclose($myfile);

    return;

}

function test($users) {


}

function generateCsv($data, $filename, $delimiter = ',', $enclosure = '"') {
    $contents="";
    $handle = fopen($filename, 'r+');
    foreach ($data as $line) {
        fputcsv($handle, $line, $delimiter, $enclosure);
    }
    rewind($handle);
    while (!feof($handle)) {
        $contents .= fread($handle, 8192);
    }
    fclose($handle);
    return $contents;
}

function extConfig($users, $extensions, $contacts, $uservm) {
//    $csv = array_map('str_getcsv', file('/etc/asterisk/voicemail.conf'));

    $csv=array();
//    print_r($uservm);
    foreach ($extensions as $e) {
        $e[] = $uservm["default"][$e[0]]["pwd"];
//        print_r($users);
        $k = returnKey($users, "default_extension", $e[0]);

//        print_r($users[$k]);
//        print $users[$k]["email"];
        $n = array();
        $e[] = $users[$k]["email"];
        $n["extension"] = $e[0];
        $n["name"] = $users[$k]["dn"];
        if ($users[$k]["hasvoicemail"] = "y") {
            $n["voicemail"] = "default";
//        $n["mailbox"] = $e[0] . "@device";
//        $n["aggregate_mwi"] = "yes";
            $n["voicemail_enable"] = "yes";
            if ($uservm["default"][$e[0]]["pwd"] == "" | $uservm["default"][$e[0]]["pwd"] == null) {
                $n["voicemail_vmpwd"] = $e[0];
            } else {
                $n["voicemail_vmpwd"] = $uservm["default"][$e[0]]["pwd"];
            }
            $n["voicemail_email"] = $users[$k]["email"];
            $n["voicemail_options"] = "attach=yes|saycid=no|envelope=no|delete=no";
//        $n["voicemail_same_exten"] = "yes";
//        $n["findmefollow_voicemail"] = "default";

        }

//        echo "Password is :" . $uservm["default"][$e[0]]["pwd"] . ": ";
//        if ($uservm["default"][$e[0]]["pwd"] == "" | $uservm["default"][$e[0]]["pwd"] == null) {
//            echo "blank or null<br>";
//        } else {
//            echo "set to something<br>";
//        }

        $o = implode(",", $n);
//        print $o . "<br>";
        $c[] = $o;
//        print_r($n);
//        $newValues[$key] = $value;
        $csv[] = $n;

    }

//    print_r($c);



//    generateCsv($csv, 'test.csv');
    $pathToGenerate = 'ext-update.csv';  // your path and file name
    $header=null;
    $createFile = fopen($pathToGenerate,"w+");
    foreach ($csv as $row) {

        if(!$header) {

            fputcsv($createFile,array_keys($row));
            fputcsv($createFile, $row);   // do the first row of data too
            $header = true;
        }
        else {

            fputcsv($createFile, $row);
        }
    }
    fclose($createFile);

    $exec = "fwconsole bi --type='extensions' ext-update.csv";
    $exec = shell_exec($exec);
    echo "Updated extensions... " . $exec . "<br>";

//    print_r($csv);
}

function ldapFun($config) {


//LDAP Bind paramters, need to be a normal AD User account.

    $ldap_username = $config['AD']['username'];
    $ldap_password = $config['AD']['password'];
    $ldap_connection = ldap_connect($config['AD']['host']);

if (FALSE === $ldap_connection){
    // Uh-oh, something is wrong...
	echo 'Unable to connect to the ldap server';
}

// We have to set this option for the version of Active Directory we are using.
ldap_set_option($ldap_connection, LDAP_OPT_PROTOCOL_VERSION, 3) or die('Unable to set LDAP protocol version');
ldap_set_option($ldap_connection, LDAP_OPT_REFERRALS, 0); // We need this for doing an LDAP search.

if (TRUE === ldap_bind($ldap_connection, $ldap_username, $ldap_password)){

	//Your domains DN to query
    $ldap_base_dn = 'OU=USERS,OU=3ENDT,DC=3endt,DC=local';

	//Get standard users and contacts
    $search_filter = '(|(objectCategory=person)(objectCategory=contact))';

	//Connect to LDAP
	$result = ldap_search($ldap_connection, $ldap_base_dn, $search_filter);

    if (FALSE !== $result){
		$entries = ldap_get_entries($ldap_connection, $result);

		// Uncomment the below if you want to write all entries to debug somethingthing
		//var_dump($entries);

		//Create a table to display the output
		echo '<h2>AD User Results</h2></br>';
		echo '<table border = "1"><tr bgcolor="#cccccc"><td>Username</td><td>Last Name</td><td>First Name</td><td>Company</td><td>Department</td><td>Office Phone</td><td>Fax</td><td>Mobile</td><td>DDI</td><td>E-Mail Address</td><td>Home Phone</td></tr>';

		//For each account returned by the search
		for ($x=0; $x<$entries['count']; $x++){

			//
			//Retrieve values from Active Directory
			//

			//Windows Usernaame
			$LDAP_samaccountname = "";

			if (!empty($entries[$x]['samaccountname'][0])) {
				$LDAP_samaccountname = $entries[$x]['samaccountname'][0];
				if ($LDAP_samaccountname == "NULL"){
					$LDAP_samaccountname= "";
				}
			} else {
				//#There is no samaccountname s0 assume this is an AD contact record so generate a unique username

				$LDAP_uSNCreated = $entries[$x]['usncreated'][0];
				$LDAP_samaccountname= "CONTACT_" . $LDAP_uSNCreated;
			}

			//Last Name
			$LDAP_LastName = "";

			if (!empty($entries[$x]['sn'][0])) {
				$LDAP_LastName = $entries[$x]['sn'][0];
				if ($LDAP_LastName == "NULL"){
					$LDAP_LastName = "";
				}
			}

			//First Name
			$LDAP_FirstName = "";

			if (!empty($entries[$x]['givenname'][0])) {
				$LDAP_FirstName = $entries[$x]['givenname'][0];
				if ($LDAP_FirstName == "NULL"){
					$LDAP_FirstName = "";
				}
			}

			//Company
			$LDAP_CompanyName = "";

			if (!empty($entries[$x]['company'][0])) {
				$LDAP_CompanyName = $entries[$x]['company'][0];
				if ($LDAP_CompanyName == "NULL"){
					$LDAP_CompanyName = "";
				}
			}

			//Department
			$LDAP_Department = "";

			if (!empty($entries[$x]['department'][0])) {
				$LDAP_Department = $entries[$x]['department'][0];
				if ($LDAP_Department == "NULL"){
					$LDAP_Department = "";
				}
			}

			//Job Title
			$LDAP_JobTitle = "";

			if (!empty($entries[$x]['title'][0])) {
				$LDAP_JobTitle = $entries[$x]['title'][0];
				if ($LDAP_JobTitle == "NULL"){
					$LDAP_JobTitle = "";
				}
			}

			//IPPhone
			$LDAP_OfficePhone = "";

			if (!empty($entries[$x]['ipphone'][0])) {
				$LDAP_OfficePhone = $entries[$x]['ipphone'][0];
				if ($LDAP_OfficePhone == "NULL"){
					$LDAP_OfficePhone = "";
				}
			}

			//FAX Number
			$LDAP_OfficeFax = "";

			if (!empty($entries[$x]['facsimiletelephonenumber'][0])) {
				$LDAP_OfficeFax = $entries[$x]['facsimiletelephonenumber'][0];
				if ($LDAP_OfficeFax == "NULL"){
					$LDAP_OfficeFax = "";
				}
			}

			//Mobile Number
			$LDAP_CellPhone = "";

			if (!empty($entries[$x]['mobile'][0])) {
				$LDAP_CellPhone = $entries[$x]['mobile'][0];
				if ($LDAP_CellPhone == "NULL"){
					$LDAP_CellPhone = "";
				}
			}

			//Telephone Number
			$LDAP_DDI = "";

			if (!empty($entries[$x]['telephonenumber'][0])) {
				$LDAP_DDI = $entries[$x]['telephonenumber'][0];
				if ($LDAP_DDI == "NULL"){
					$LDAP_DDI = "";
				}
			}

			//Email address
			$LDAP_InternetAddress = "";

			if (!empty($entries[$x]['mail'][0])) {
				$LDAP_InternetAddress = $entries[$x]['mail'][0];
				if ($LDAP_InternetAddress == "NULL"){
					$LDAP_InternetAddress = "";
				}
			}

			//Home phone
			$LDAP_HomePhone = "";

			if (!empty($entries[$x]['homephone'][0])) {
				$LDAP_HomePhone = $entries[$x]['homephone'][0];
				if ($LDAP_HomePhone == "NULL"){
					$LDAP_HomePhone = "";
				}
			}

			echo "<tr><td><strong>" . $LDAP_samaccountname ."</strong></td><td>" .$LDAP_LastName."</td><td>".$LDAP_FirstName."</td><td>".$LDAP_CompanyName."</td><td>".$LDAP_Department."</td><td>".$LDAP_OfficePhone."</td><td>".$LDAP_OfficeFax."</td><td>".$LDAP_CellPhone."</td><td>".$LDAP_DDI."</td><td>".$LDAP_InternetAddress."</td><td>".$LDAP_HomePhone."</td></tr>";


		} //END for loop
	} //END FALSE !== $result

	ldap_unbind($ldap_connection); // Clean up after ourselves.
	echo("</table>"); //close the table

} //END ldap_bind


}

function adlfun()
{
    // Construct new Adldap instance.
    $ad = new \Adldap\Adldap();

// Create a configuration array.
    $config = [
        // The domain controllers option is an array of your LDAP hosts. You can
        // use the either the host name or the IP address of your host.
        'domain_controllers' => ['10.2.1.2', '10.2.1.19'],

        // The base distinguished name of your domain.
        'base_dn' => 'ou=Users,ou=3ENDT,dc=3endt,dc=local',

        // The account to use for querying / modifying LDAP records. This
        // does not need to be an actual admin account. This can also
        // be a full distinguished name of the user account.
        'admin_username' => '3endt\asp_admin',
        'admin_password' => 'Tess1996',
    ];

// Add a connection provider to Adldap.
    $ad->addProvider($config);

    try {
        echo "We're here<br>";
        // If a successful connection is made to your server, the provider will be returned.
        $provider = $ad->connect();

        // Performing a query.
        $results = $provider->search()->where('mail', '=', 'brittany@3endt.com')->get();
//        print_r($results);
        // Finding a record.
        $user = $provider->search()->find('brittany');
//        print_r($user);



    } catch (\Adldap\Auth\BindException $e) {

    // There was an issue binding / connecting to the server.
        echo "PROBLEM!<br>";

}
}

// converts pure string into a trimmed keyed array
function string2KeyedArray($string, $delimiter = ',', $kv = '=>') {
    if ($a = explode($delimiter, $string)) { // create parts
        foreach ($a as $s) { // each part
            if ($s) {
                if ($pos = strpos($s, $kv)) { // key/value delimiter
                    $ka[trim(substr($s, 0, $pos))] = trim(substr($s, $pos + strlen($kv)));
                } else { // key delimiter not found
                    $ka[] = trim($s);
                }
            }
        }
        return $ka;
    }
} // string2KeyedArray

function adLoad ()
{


//LDAP Bind paramters, need to be a normal AD User account.
    $ldap_password = 'Tess1996';
    $ldap_username = '3endt\asp_admin';
    $ldap_connection = ldap_connect ("10.2.1.2");
    $adUser = array ();

    if (FALSE === $ldap_connection) {
        // Uh-oh, something is wrong...
        echo 'Unable to connect to the ldap server';
    }

// We have to set this option for the version of Active Directory we are using.
    ldap_set_option ($ldap_connection, LDAP_OPT_PROTOCOL_VERSION, 3) or die('Unable to set LDAP protocol version');
    ldap_set_option ($ldap_connection, LDAP_OPT_REFERRALS, 0); // We need this for doing an LDAP search.

    if (TRUE === ldap_bind ($ldap_connection, $ldap_username, $ldap_password)) {

        //Your domains DN to query
        $ldap_base_dn = 'OU=USERS,OU=3ENDT,DC=3endt,DC=local';

        //Get standard users and contacts
        $search_filter = '(|(objectCategory=person)(objectCategory=contact))';

        //Connect to LDAP
        $result = ldap_search ($ldap_connection, $ldap_base_dn, $search_filter);

        if (FALSE !== $result) {
            $entries = ldap_get_entries ($ldap_connection, $result);

            // Uncomment the below if you want to write all entries to debug somethingthing
            //var_dump($entries);

            //For each account returned by the search
            for ($x = 0; $x < $entries['count']; $x ++) {

                //
                //Retrieve values from Active Directory
                //
                $LDAP_OfficePhone = "";

                if (!empty($entries[$x]['ipphone'][0])) {
                    $LDAP_OfficePhone = $entries[$x]['ipphone'][0];
                    if ($LDAP_OfficePhone == "NULL") {
                        $LDAP_OfficePhone = "";
                    }

                    $adUser[$LDAP_OfficePhone]['Extension'] = $LDAP_OfficePhone;
                    //Last Name

                    $adUser[$LDAP_OfficePhone]['lname'] = "";
                    if (!empty($entries[$x]['sn'][0])) {
                        $adUser[$LDAP_OfficePhone]['lname'] = $entries[$x]['sn'][0];
                        if ($adUser[$LDAP_OfficePhone]['lname'] == "NULL") {
                            $adUser[$LDAP_OfficePhone]['lname'] = "";
                        }
                    }

                    $adUser[$LDAP_OfficePhone]['fname'] = "";

                    if (!empty($entries[$x]['givenname'][0])) {
                        $adUser[$LDAP_OfficePhone]['fname'] = $entries[$x]['givenname'][0];
                        if ($adUser[$LDAP_OfficePhone]['fname'] == "NULL") {
                            $adUser[$LDAP_OfficePhone]['fname'] = "";
                        }
                    }

                    $adUser[$LDAP_OfficePhone]['company'] = "";
                    if (!empty($entries[$x]['company'][0])) {
                        $adUser[$LDAP_OfficePhone]['company'] = $entries[$x]['company'][0];
                        if ($adUser[$LDAP_OfficePhone]['company'] == "NULL") {
                            $adUser[$LDAP_OfficePhone]['company'] = "";
                        }
                    }

                    $adUser[$LDAP_OfficePhone]['department'] = "";
                    if (!empty($entries[$x]['department'][0])) {
                        $adUser[$LDAP_OfficePhone]['department'] = $entries[$x]['department'][0];
                        if ($adUser[$LDAP_OfficePhone]['department'] == "NULL") {
                            $adUser[$LDAP_OfficePhone]['department'] = "";
                        }
                    }

                    $adUser[$LDAP_OfficePhone]['title'] = "";
                    if (!empty($entries[$x]['title'][0])) {
                        $adUser[$LDAP_OfficePhone]['title'] = $entries[$x]['title'][0];
                        if ($adUser[$LDAP_OfficePhone]['title'] == "NULL") {
                            $adUser[$LDAP_OfficePhone]['title'] = "";
                        }
                    }

                    $adUser[$LDAP_OfficePhone]['fax'] = "";
                    if (!empty($entries[$x]['facsimiletelephonenumber'][0])) {
                        $adUser[$LDAP_OfficePhone]['fax'] = $entries[$x]['facsimiletelephonenumber'][0];
                        if ($adUser[$LDAP_OfficePhone]['fax'] == "NULL") {
                            $adUser[$LDAP_OfficePhone]['fax'] = "";
                        }
                    }

                    $adUser[$LDAP_OfficePhone]['cell'] = "";
                    if (!empty($entries[$x]['mobile'][0])) {
                        $adUser[$LDAP_OfficePhone]['cell'] = $entries[$x]['mobile'][0];
                        if ($adUser[$LDAP_OfficePhone]['cell'] == "NULL") {
                            $adUser[$LDAP_OfficePhone]['cell'] = "";
                        }
                    }

                    $adUser[$LDAP_OfficePhone]['email'] = "";
                    if (!empty($entries[$x]['mail'][0])) {
                        $adUser[$LDAP_OfficePhone]['email'] = $entries[$x]['mail'][0];
                        if ($adUser[$LDAP_OfficePhone]['email'] == "NULL") {
                            $adUser[$LDAP_OfficePhone]['email'] = "";
                        }
                    }

                    $adUser[$LDAP_OfficePhone]['home'] = "";
                    if (!empty($entries[$x]['homephone'][0])) {
                        $adUser[$LDAP_OfficePhone]['home'] = $entries[$x]['homephone'][0];
                        if ($adUser[$LDAP_OfficePhone]['home'] == "NULL") {
                            $adUser[$LDAP_OfficePhone]['home'] = "";
                        }
                    }

                    $adUser[$LDAP_OfficePhone]['displayname'] = "";
                    if (!empty($entries[$x]['displayname'][0])) {
                        $adUser[$LDAP_OfficePhone]['displayname'] = $entries[$x]['displayname'][0];
                        if ($adUser[$LDAP_OfficePhone]['displayname'] == "NULL") {
                            $adUser[$LDAP_OfficePhone]['displayname'] = "";
                        }
                    }

                    $digium = "";

                    if (!empty($entries[$x]['extensionattribute15'][0])) {
                        $digium = $entries[$x]['extensionattribute15'][0];
                        if ($digium == "NULL") {
                            $digium = "";
                        }
                        if (!empty($digium)) {
                            $adUser[$LDAP_OfficePhone] += string2KeyedArray ($digium);


                        }
                    }
                }
//                print_r($entries[$x]);
            } //END for loop

        } //END FALSE !== $result

        ldap_unbind ($ldap_connection); // Clean up after ourselves.

//        print_r($adUser);
    } //END ldap_bind

    return $adUser;
}
