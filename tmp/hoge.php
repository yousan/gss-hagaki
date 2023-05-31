<?php

use Google\Service\Sheets\Sheet;

require_once('../vendor/autoload.php');


// putenv('GOOGLE_APPLICATION_CREDENTIALS=/path/to/credentials.json');
// echo $_ENV['GOOGLE_APPLICATION_CREDENTIALS'];
// echo getenv('GOOGLE_APPLICATION_CREDENTIALS');



// use Google\Spreadsheet\DefaultServiceRequest;
// use Google\Spreadsheet\ServiceRequestFactory;

main();

function main() {
    // スプレッドシートIDとシート名の設定
    $spreadsheetId = '1yfMIdt8wgBPrMY3UwiCTsX3EN_2gcLCmPAEy8dfYeLY';
    $sheetName = 'サンプル（編集不可）';

    // 認証とAPIクライアントの設定
    $client = new Google_Client(['credentials'=> '../secret.json']);
    $client->useApplicationDefaultCredentials();
    $client->addScope(Google_Service_Sheets::SPREADSHEETS_READONLY);

    // スプレッドシートデータの取得
    $spreadsheet = (new Google_Service_Sheets($client))->spreadsheets->get($spreadsheetId);
    $sheetData = $spreadsheet->getSheets();
	// theFirstSheet($sheetData);
	// var_dump($sheetData); exit;
	/** @var Sheet $sheet **/
	$sheet = array_pop($sheetData);
	// var_dump($sheet);
	// var_dump($sheet->getData());
	$sheetName = $sheet->getProperties()->title;
	$data = (new \Google\Service\Sheets($client))->spreadsheets_values->get($spreadsheetId, $sheetName);
	var_dump($data);
	exit;
	$sheetProperties = $sheetData[0]->getProperties();
    $sheetId = $sheetProperties->sheetId;

    // シートの値の取得
    $response = (new Google_Service_Sheets($client))->spreadsheets_values->get($spreadsheetId, $sheetName);
    $values = $response->getValues();

    var_dump($values);
}

/**
 * @param Sheet[] $sheetData
 *
 * @return void
 */
function theFirstSheet($sheetData) {
	foreach($sheetData as $key => $sheet) {
		var_dump($sheet->getData());
	}
}
