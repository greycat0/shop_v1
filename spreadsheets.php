<?php

$table = '19xAYUqx5pvkBD26TgBXh-FlajHP8twwwg_ym-LNX6f4';
putenv("GOOGLE_APPLICATION_CREDENTIALS=credentials.json");

$client = new Google_Client();
$client->useApplicationDefaultCredentials();
$client->addScope(Google_Service_Sheets::SPREADSHEETS);

$service = new Google_Service_Sheets($client);
$spreadsheetId = $table;
$range = 'A2';
$body = new Google_Service_Sheets_ValueRange([
    'values' => [['lalala']]
]);
$params = ['valueInputOption' => 'RAW'];
$service->spreadsheets_values->append($spreadsheetId, $range, $body, $params);
