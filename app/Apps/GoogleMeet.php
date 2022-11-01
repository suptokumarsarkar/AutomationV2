<?php

namespace App\Apps;

use App\Apps\Triggers\GoogleFormTrigger;
use App\Logic\Helpers;
use App\Models\Account;
use App\Models\AppsData;

class GoogleMeet
{

//    private $client_id = '68667692288-4bkagbc71fb2k0c9dr481ip9ib22iffv.apps.googleusercontent.com';
//    private $client_secret = "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";

    /**
     * @var mixed
     */
    private $client_id;
    /**
     * @var mixed
     */
    private $client_secret;
    public $dataOptionTrigger = false;

    public function __construct()
    {
        $gmail = AppsData::where("AppId", "GoogleMeet")->first();
        if ($gmail) {
            $appInfo = json_decode($gmail->AppInfo, true);
            $this->client_id = $appInfo['client_id'];
            $this->client_secret = $appInfo['client_secret'];
        }
    }


    public function getTrigger(): array
    {
        return array(
            [
                'id' => 'new_response',
                'name' => Helpers::translate('New Response'),
                'description' => Helpers::translate('Triggers when you create a new folder')
            ]
        );
    }

    public function getActions(): array
    {
        return array(
            [
                'id' => 'schedule_a_meeting',
                'name' => Helpers::translate('Schedule a meeting'),
                'description' => Helpers::translate('It will make a meeting timeline on your Google Calender.')
            ]
        );
    }


    public function getRefreshToken($refreshToken)
    {
        $data = [
            'refresh_token' => $refreshToken,
            'client_id' => $this->client_id,
            'client_secret' => $this->client_secret,
            'grant_type' => 'refresh_token'
        ];

        $url = "https://oauth2.googleapis.com/token";

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));

// execute!
        $response = curl_exec($ch);
        return json_decode($response, 1);
    }

    public function getToken($id)
    {
        $getProfile = Account::find($id);
        $token = json_decode($getProfile->token, true);
        $refreshToken = $this->getRefreshToken($token['refresh_token']);
        return $refreshToken['access_token'];
    }

    public function getUserId($actionAccount)
    {
        $getProfile = Account::find($actionAccount);
        $token = json_decode($getProfile->data, true);
        return $token['sub'];
    }

    public function getCheckupData($accountId, $labelId)
    {
        $getProfile = Account::find($accountId);
        $data = json_decode($getProfile->data, true);
        $access_token = $this->getToken($accountId);
        $trigger = new GoogleFormTrigger($accountId);
        return [
            'access_token' => $access_token,
            'view' => $trigger->$labelId(),
        ];
    }

    public function checkAccount($id)
    {
        if ($access_token = $this->getToken($id)) {
            $url = "https://www.googleapis.com/oauth2/v3/userinfo?access_token=" . $access_token;

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $response = curl_exec($ch);
            $profile = json_decode($response, 1);

            if (isset($profile) && count($profile) != 0) {
                return true;
            }
        }
        return false;
    }

    public function fileManagerInstance($accountId)
    {
        return [$this->getToken($accountId)];
    }

// Real API
    public function createMeeting($access_token, $param)
    {

        $params = [
            "end" => [
                "dateTime" => date('c', strtotime($param['end'])),
                "timeZone" => $param['timezone']
            ],
            "start" => [
                "dateTime" => date('c', strtotime($param['start'])),
                "timeZone" => $param['timezone']
            ],
            "description" => $param['description'],
            "summary" => $param['summary'],
            "conferenceData" => [
                "createRequest" => [
                    "conferenceSolutionKey" => [
                        "type" => "hangoutsMeet"
                    ],
                    "requestId" => sha1(rand())
                ]
            ]
        ];


        $url = "https://www.googleapis.com/calendar/v3/calendars/{$param['calenderId']}/events?conferenceDataVersion=1&access_token=" . $access_token;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        // don't do ssl checks
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Authorization: Bearer " . $access_token, 'Accept: application/json', 'Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
        $data = curl_exec($ch);

        return json_decode($data, true);
    }

    public function getCalenderList($access_token)
    {
        $url = "https://www.googleapis.com/calendar/v3/users/me/calendarList?access_token=" . $access_token;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        return json_decode($response, 1)['items'];
    }
}
