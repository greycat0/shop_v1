<?php
require_once './vendor/autoload.php';

error_reporting(0);
session_start();
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : false;
$response = handle_request($action);
return print($response);

function handle_request($action)
{
    $response = process_request($action);
    $response_type = gettype($response);
    if ($response_type == 'object' || $response_type == 'array') {
        if ($response_type == 'array' && $response !== array_values((array) $response)) {
            $response = (object) $response;
        }
        header("Content-type: application/json");
        $response = json_encode($response);
    } else {
        $response = strval($response);
    }
    return $response;
}
function process_request($action)
{
    if ($action == "purchase_request") {
        $table = '19xAYUqx5pvkBD26TgBXh-FlajHP8twwwg_ym-LNX6f4';
        putenv("GOOGLE_APPLICATION_CREDENTIALS=credentials.json");

        $client = new Google_Client();
        $client->addScope(Google_Service_Sheets::SPREADSHEETS);
        $client->useApplicationDefaultCredentials();

        $service = new Google_Service_Sheets($client);
        $body = new Google_Service_Sheets_ValueRange([
            'values' => [[
                $_REQUEST['name'],
                $_REQUEST['tel'],
                $_REQUEST['email'],
                $_REQUEST['current_request']
            ]]
        ]);
        $service->spreadsheets_values->append($table, 'A1', $body, ['valueInputOption' => 'RAW']);
    }
}
