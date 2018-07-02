<?php
/**
 * Created by PhpStorm.
 * User: lacy
 * Date: 3/22/2016
 * Time: 2:04 PM
 */
// $blf[0] = "207,209,232,204";
// $blf[1] = "209,207,232,204";
/**
 * @param $config
 */
function copyExcelFiles($config)
{
    $sftp = new Net_SFTP($config['sftp'][0]);
    if (!$sftp->login($config['sftp'][1], $config['sftp'][2])) {
        exit('Login Failed');
    }

    if ($sftp->size('pbx-3e/smartblf.txt')) {
        $sftp->get('pbx-3e/smartblf.txt', 'smartblf.txt');
    }
    if ($sftp->size('pbx-3e/contacts.xlsx')) {
        $sftp->get('pbx-3e/contacts.xlsx', 'contacts.xlsx');
    }
}

/**
 * @param $config
 *
 * @return mixed
 */
function setup($config) {
//    $inputFileType = 'Xlsx';
//    $inputFileName = 'contacts.xlsx';
//    $sheetname = 'Extensions';
//
//    /**  Create a new Reader of the type defined in $inputFileType  **/
//    $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
//    /**  Advise the Reader of which WorkSheets we want to load  **/
////    $reader->setLoadSheetsOnly($sheetname);
//    /**  Load $inputFileName to a Spreadsheet Object  **/
//    $spreadsheet = $reader->load($inputFileName);
//
////    $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
////    $reader->setReadDataOnly(true);
////    $reader->setLoadSheetsOnly(["Extensions"]);
////    $spreadsheet = $reader->load("contacts.xlsx");
//    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Csv($spreadsheet);
//    $writer->setSheetIndex(0);
//
//    $reader = new \PhpOffice\PhpSpreadsheet\Reader\Csv();
//
//    $spreadsheet = $reader->load("contacts.csv");
//    $sheetData = $spreadsheet->getActiveSheet()->toArray();
//
////    print_r($sheetData);
//
//
////    $writer->save("contacts.csv");
    copyExcelFiles ($config);
    $blf = file('smartblf.txt');
    $contacts = file('contacts.csv');


//$contacts = $writer;

//  echo "<h2>BLF Lists</h2>";
//  echo"<pre>"; print_r($blf); echo"</pre>";

//  echo "<h2>Contact Lists</h2>";
//  echo"<pre>"; print_r($contacts); echo"</pre>";
    $setup[0] = $blf;
    $setup[1] = $contacts;
    return $setup;
}
