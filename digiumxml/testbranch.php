<?php
$blf = null;
$debug = false;
$pjsip_phones_cfg="/etc/asterisk/pjsip_phones.conf";
$res_digium_lines_cfg=  "/etc/asterisk/res_digium_lines_add.conf";
$res_digium_phones_cfg= "/etc/asterisk/res_digium_phones_add.conf";
$voicemail_cfg="/etc/asterisk/voicemail.conf";
$contact_cfg="/var/www/html/digium_phones/contact.xml";
$buttons_cfg="/usr/local/fop2/buttons.cfg";
$smart_blf_ext_cfg="/var/www/html/digium_phones/";

require 'vendor/autoload.php';
require 'xlsx2csv.php';

require_once "lib/random.php";
include ('config/config.php');
include('Net/SFTP.php');
include('digiumxml.data.php');
//include('functions.inc.php');
include('testfunctions.php');

$bootstrap_settings = array();
$bootstrap_settings['freepbx_auth'] = false;
include '/etc/freepbx.conf';
echo "<pre>";
if(function_exists('setup_userman')){
    $setup = setup($config);
    $contacts = $setup[1];
    $blf=$setup[0];

    /** @var TYPE_NAME $adUser */
    /** @var TYPE_NAME $adUser */
    $adUser = adLoad();
    /** @var TYPE_NAME $userman */
    $userman = setup_userman();
    /** @var TYPE_NAME $extensions */
    $extensions = \FreePBX::Core()->listUsers(true);
    /** @var TYPE_NAME $users */
    $users = $userman->getAllUsers();
    /** @var TYPE_NAME $xml */
    $xml = newUsermanCreateContactsXML($adUser);
    /** @var TYPE_NAME $xml */
    if (!empty($xml)) {
        $xml->save("contact.xml");
        echo "Created contact.xml... <br>";
    }

    // Sort by first name
    uasort($adUser, 'compare_firstname');
    /** @var TYPE_NAME $xml */
    /** @var TYPE_NAME $xml */
    $xml = createSmartBLFXML($adUser, $debug, NULL);
    if (!empty($xml)) {
        $xml->save("smartblf.xml");
        echo "Created smartblf.xml... <br>";
    }
    createSmartBlfExt($adUser, $debug, $blf, $smart_blf_ext_cfg);
    createResDigPhoneConf($users, $adUser);
    $out = correctFreePBXExtensions ($adUser, $extensions);
    echo "Corrected FreePBX extensions: " . $out . "<br>";

    // Copy files to location
    $output = shell_exec('cp contact.xml ' . $contact_cfg );
    echo "Copied Contacts... " . $output . "\n<br>";
    $output = shell_exec('cp res_digium_phones_add.conf ' . $res_digium_phones_cfg );
    echo "Copied digium_phones... " . $output . "\n<br>";
    $output = shell_exec('cp res_digium_lines_add.conf ' . $res_digium_lines_cfg );
    echo "Copied digium_lines... " . $output . "\n<br>";
    $output = shell_exec('cp smartblf.xml ' . $smart_blf_ext_cfg);
    echo "Copied smartblf.xml... " . $output . "\n<br>";

    $output = shell_exec('asterisk -rx "module reload res_digium_phone.so"');
    echo "Reloading res_digium_phone... " . $output . "\n<br>";
    $output = shell_exec('asterisk -rx "digium_phones reconfigure all"');
    echo "Reconfiguring Digium Phones... " . $output . "\n<br>";

} else {
    echo "Something is BROKE!! <br>";
}


echo "</pre>";
