<?php
//if (!@include_once(getenv('FREEPBX_CONF') ? getenv('FREEPBX_CONF') : '/etc/freepbx.conf')) {
//    include_once('/etc/asterisk/freepbx.conf');
//}

//TODO: BLF on D70s need to be in alphabetical order.


ini_set('display_errors', 1); 
error_reporting(E_ALL);

$blf = null;
$debug = false;
$pjsip_phones_cfg="/etc/asterisk/pjsip_phones.conf";
$res_digium_lines_cfg=  "/etc/asterisk/res_digium_lines.conf";
$res_digium_phones_cfg= "/etc/asterisk/res_digium_phones.conf";
$voicemail_cfg="/etc/asterisk/voicemail.conf";
$contact_cfg="/var/www/html/digium_phones/contact.xml";
$buttons_cfg="/usr/local/fop2/buttons.cfg";
$smart_blf_ext_cfg="/var/www/html/digium_phones/";
//$buttons_cfg="tmp/buttons.cfg";

require_once "lib/random.php";

include('Net/SFTP.php');
include('functions.inc.php');
include('digiumxml.data.php');


if (isset($argc)) {
    foreach ($argv as $arg) {
        if (strtolower($arg) == "debug") {
            $debug = true;
        }
    }
}

if (is_null($debug)) {$debug= false;}


$results = readContacts ($contacts);
if ($debug) {echo "<hr>"; echo "<pre>";print_r($results);echo "</pre>";}
// echo "<hr>"; echo "<pre>";print_r($results);echo "</pre>";

//fixSipDb($resultsSip, $results);
//$results = fixnames($results, $debug);

// Create the contacts
$xml = createContactsXML($results, $debug);
$xml->save("contact.xml");
echo "Created contact.xml... <br>";
// $xml->save("/etc/asterisk/digium_phones/contact.xml");
//$xml->save("contacts.xml");
$xml = null;



// Sort by first name
uasort($results, 'compare_firstname');
createSmartBlfExt ($results, $debug, $blf, $smart_blf_ext_cfg);
createResDigPhoneConf($results);
createPjsip_PhonesConf($results);

// Following two lines commented out
$xml = createSmartBLFXML($results, $debug, NULL);
$xml->save("/etc/asterisk/digium_phones/smartblf.xml");
$xml->save("smartblf.xml");
echo "Created smartblf.xml... <br>";

$buttons = createButtons($results);
// echo "<pre>". $buttons ."</pre>";
// $buttons->save("/usr/local/fop2/buttons.cfg");
//$output = file_put_contents("buttons.cfg", $buttons);
//echo "Created buttons config... <br>";

//$voicemail = createVoicemail($results);
//
//$output = file_put_contents("voicemail.conf", $voicemail);
//echo "Created voicemail config... <br>";

// Copy files to location
//$output = shell_exec('cp buttons.cfg ' . $buttons_cfg );
//echo "Copied buttons.cfg... " . $output . "\n<br>";
$output = shell_exec('cp contact.xml ' . $contact_cfg );
echo "Copied Contacts... " . $output . "\n<br>";
//$output = shell_exec('cp voicemail.conf ' . $voicemail_cfg );
//echo "Copied Voicemail... " . $output . "\n<br>";
//$output = shell_exec('cp pjsip_phones.conf ' . $pjsip_phones_cfg );
//echo "Copied pjsip_phones... " . $output . "\n<br>";
$output = shell_exec('cp res_digium_phones.conf ' . $res_digium_phones_cfg );
echo "Copied digium_phones... " . $output . "\n<br>";
$output = shell_exec('cp res_digium_lines.conf ' . $res_digium_lines_cfg );
echo "Copied digium_lines... " . $output . "\n<br>";
$output = shell_exec('cp smartblf.xml ' . $smart_blf_ext_cfg);
echo "Copied smartblf.xml... " . $output . "\n<br>";

//$output = shell_exec('sudo -S /etc/init.d/fop2 restart');
//echo "Restarting Flash Operator Panel... " . $output . "\n<br>";
//$output = shell_exec('sudo service fop2 restart');
//echo "Restarting Flash Operator Panel... " . $output . "\n<br>";
// echo "<pre>$output</pre>";
// $output = shell_exec('sudo asterisk -rx"module reload res_digium_phone.so"');
// echo "<pre>$output</pre>";
// $output = shell_exec('sudo asterisk -rx"digium_phones reconfigure all"');
// echo "<pre>$output</pre>";

$output = shell_exec('asterisk -rx "module reload res_digium_phone.so"');
echo "Reloading res_digium_phone... " . $output . "\n<br>";
//if ($debug) { echo "<pre>$output</pre>"; }
//
$output = shell_exec('asterisk -rx "digium_phones reconfigure all"');
echo "Reconfiguring Digium Phones... " . $output . "\n<br>";
$output = shell_exec('asterisk -rx "module reload res_pjsip.so"');
echo "Reloading res_pjsip... " . $output . "\n<br>";
$output = shell_exec('asterisk -rx "module reload app_voicemail.so"');
echo "Reloading Voicemail... " . $output . "\n<br>";

//$output = shell_exec('asterisk -rx "core restart when convenient"');
//echo "Restarting Asterisk... " . $output . "\n";
//if ($debug) { echo "<pre>$output</pre>"; }
//
//$sql = "update sip SET data='yes' WHERE keyword='trustrpid'";
//$db->getAll($sql, DB_FETCHMODE_ASSOC);
//
//$sql = "update sip SET data='pai' WHERE keyword='sendrpid'";
//$db->getAll($sql, DB_FETCHMODE_ASSOC);
//
//$output = shell_exec('/var/lib/asterisk/bin/module_admin reload');
//if ($debug) { echo "<pre>$output</pre>"; }


// phpinfo();

