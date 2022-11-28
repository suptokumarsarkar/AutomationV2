<?php

namespace App\Apps;

use App\Apps\Triggers\GoogleCalendarTrigger;
use App\Logic\Helpers;
use App\Models\Account;
use App\Models\AppsData;

class GoogleCalendar
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

    public function __construct()
    {
        $gmail = AppsData::where("AppId", "GoogleCalendar")->first();
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
                'id' => 'new_calendar',
                'name' => Helpers::translate('New Calendar'),
                'description' => Helpers::translate('Triggers when you create a new calendar')
            ],
            [
                'id' => 'new_event',
                'name' => Helpers::translate('New Event'),
                'description' => Helpers::translate('Triggers when you create a new event')
            ],
            [
                'id' => 'event_started',
                'name' => Helpers::translate('Event Started'),
                'description' => Helpers::translate('Triggers when you an event started')
            ],
            [
                'id' => 'event_ended',
                'name' => Helpers::translate('Event Ended'),
                'description' => Helpers::translate('Triggers when you an event ended')
            ],
            [
                'id' => 'event_cancelled',
                'name' => Helpers::translate('Event Cancelled'),
                'description' => Helpers::translate('Triggers when you an event event cancelled')
            ]
        );
    }

    public function getActions(): array
    {
        return array(
            [
                'id' => 'create_calendar',
                'name' => Helpers::translate('Create Calendar'),
                'description' => Helpers::translate('Creates a new Calendar into your existing google account.')
            ],
            [
                'id' => 'create_event',
                'name' => Helpers::translate('Create Event'),
                'description' => Helpers::translate('Creates a new event into your existing calendar.')
            ],
            [
                'id' => 'quick_add_event',
                'name' => Helpers::translate('Quick Add Event'),
                'description' => Helpers::translate('Creates a new event with a sort title only.')
            ],
            [
                'id' => 'add_attendees',
                'name' => Helpers::translate('Add Attendees'),
                'description' => Helpers::translate('Adds additional member to the event')
            ],
            [
                'id' => 'update_event',
                'name' => Helpers::translate('Update Event'),
                'description' => Helpers::translate('Updates your current event')
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
        $trigger = new GoogleCalendarTrigger($accountId);
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
    public function addEvent($access_token, $param)
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

        ];
        $paramData = 'showDeleted=true&';

        if (isset($param['add_google_meet']) && $param['add_google_meet']) {
            $params["conferenceData"] = [
                "createRequest" => [
                    "conferenceSolutionKey" => [
                        "type" => "hangoutsMeet"
                    ],
                    "requestId" => sha1(rand())
                ]
            ];
            $paramData .= 'conferenceDataVersion=1&';
        }

        if ($param['attendees']) {
            foreach ($param['attendees'] as $email) {
                $params["attendees"][] = ['email' => $email];
            }

        }


        $url = "https://www.googleapis.com/calendar/v3/calendars/{$param['calenderId']}/events?{$paramData}access_token=" . $access_token;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        // don't do ssl checks
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Authorization: Bearer " . $access_token, 'Accept: application/json', 'Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
        $data = curl_exec($ch);

        return json_decode($data, true);
    }

    public function updateEvent($access_token, array $params)
    {

        $param = $this->paramModifier($access_token, $params);
        $params = [
            "end" => [
                "dateTime" => date('c', strtotime($param['end']['dateTime'] ?? date("Y-m-d H:i:s"))),
                "timeZone" => $param['timezone']
            ],
            "start" => [
                "dateTime" => date('c', strtotime($param['start']['dateTime'] ?? date("Y-m-d H:i:s"))),
                "timeZone" => $param['timezone']
            ],
            "description" => $param['description'] ?? '',
            "summary" => $param['summary'] ?? '',

        ];
        $paramData = 'showDeleted=true&';
        if (isset($param['add_google_meet']) && $param['add_google_meet']) {
            $params["conferenceData"] = [
                "createRequest" => [
                    "conferenceSolutionKey" => [
                        "type" => "hangoutsMeet"
                    ],
                    "requestId" => sha1(rand())
                ]
            ];
            $paramData .= 'conferenceDataVersion=1&';
        }

        if (isset($param['attendees'])) {
            foreach ($param['attendees'] as $email) {
                $params["attendees"][] = ['email' => $email];
            }

        }

        $url = "https://www.googleapis.com/calendar/v3/calendars/{$param['calenderId']}/events/{$param['event']}?{$paramData}access_token=" . $access_token;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        // don't do ssl checks
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Authorization: Bearer " . $access_token, 'Accept: application/json', 'Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
        $data = curl_exec($ch);
        return json_decode($data, true);
    }

    public function createCalendar($access_token, $param)
    {

        $params = [
            "summary" => $param['summary']
        ];

        $url = "https://www.googleapis.com/calendar/v3/calendars?access_token=" . $access_token;
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

    public function getEventsList($access_token, $params)
    {
        $url = "https://www.googleapis.com/calendar/v3/calendars/{$params['calenderId']}/events?showDeleted=true&access_token=" . $access_token;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        return json_decode($response, 1)['items'] ?? [];
    }

    public function getEventListCancelled($access_token, $params): array
    {
        $data = [];
        $allEvents = $this->getEventsList($access_token, $params);
        foreach ($allEvents as $events) {
            if ($events['status'] == 'cancelled') {
                $data[] = $events;
            }
        }
        return $data;
    }

    public function getEvent($access_token, $params)
    {
        $url = "https://www.googleapis.com/calendar/v3/calendars/{$params['calenderId']}/events/{$params['eventId']}?showDeleted=true&access_token=" . $access_token;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        return json_decode($response, 1) ?? [];
    }

    public function quickAddEvent($access_token, $param)
    {
        $url = "https://www.googleapis.com/calendar/v3/calendars/{$param['calenderId']}/events/quickAdd?text={$param['text']}&access_token=" . $access_token;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        // don't do ssl checks
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Authorization: Bearer " . $access_token, 'Accept: application/json', 'Content-Type: application/json'));
        $data = curl_exec($ch);
        return json_decode($data, true);
    }

    public function getEventObject($param)
    {
        $attendees = [];
        if (isset($param['attendees'])) {
            foreach ($param['attendees'] as $attendee) {
                $attendees[] = $attendee['email'];
            }
        }
        return [
            'Kind' => $param['kind'] ?? "",
            'eTag' => $param['etag'] ?? "",
            'Id' => $param['id'] ?? "",
            'Status' => $param['status'] ?? "",
            'Html Link' => $param['htmlLink'] ?? "",
            'Created' => $param['created'] ?? "",
            'Updated' => $param['updated'] ?? "",
            'Summary' => $param['summary'] ?? "",
            'Description' => $param['description'] ?? "",
            'Location' => $param['location'] ?? "",
            'Creator' => $param['creator']['email'] ?? "",
            'Start Time' => $param['start']['dateTime'] ?? date("Y-m-d H:i:s"),
            'End Time' => $param['end']['dateTime'] ?? date("Y-m-d H:i:s"),
            'Timezone' => $param['end']['timeZone'] ?? "",
            'Hangout Link' => $param['hangoutLink'] ?? "",
            'Attendees' => $attendees ?? [],
        ];
    }

    private function paramModifier($accessToken, $params)
    {
        $event = $this->getEvent($accessToken, [
            'calenderId' => $params['calenderId'],
            'eventId' => $params['event'],
        ]);
        foreach ($params as $key => $param) {
            $event[$key] = $param;
        }

        return $event;
    }


}
