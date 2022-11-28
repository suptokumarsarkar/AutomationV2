<?php

namespace App\Apps\Triggers;

use App\Apps\GoogleCalendar;
use App\Http\Controllers\Api\Apps\Manager;
use App\Logic\Helpers;
use App\Models\Account;

class GoogleCalendarTrigger
{
    private $account;
    /**
     * @var Gmail
     */
    private $mainClass;
    /**
     * @var mixed
     */
    private $access_token;

    function __construct($accountId)
    {
        $this->account = Account::find($accountId);
        $this->userId = json_decode($this->account['data'], true)['sub'];
        $this->mainClass = new GoogleCalendar;
        $this->access_token = $this->mainClass->getToken($accountId);
    }

    public function new_calendar(): array
    {

        $view = Helpers::maxNote(Helpers::translate('Will be fired while a new calendar is detected'));
        return [
            'view' => $view,
            'script' => [],
            'message' => Helpers::translate('Connected With Google Calendar'),
            'status' => 200,
        ];

    }


    public function new_calendar_check($data = [])
    {
        $file = $this->mainClass->getCalenderList($this->access_token);
        $string['string'] = $file[count($file) - 1];
        return $string;
    }


    public function new_calendar_changes($zapDatabase = [], $zapData = [])
    {
        $zapDatabase = json_decode($zapDatabase, true)['Files'];
        $zapData = json_decode($zapData, true);
        $data = Helpers::IllitarableArray($zapData['trigger']['Data']);
        $value = Helpers::evaluteData($data);

        $fileData = [];

        $files = $this->mainClass->getCalenderList($this->access_token);

        foreach ($zapDatabase as $key => $database) {
            $zapDatabase[$database['id']] = $database;
            unset($zapDatabase[$key]);
        }
        foreach ($files as $file) {
            if (!array_key_exists($file['id'], $zapDatabase)) {
                $fileDetails = $file;
                $fileData[] = $fileDetails;
            }
        }

        return $fileData;
    }

    public function new_calendar_update_database($data = null)
    {
        $data = Helpers::IllitarableArray($data);
        $value = Helpers::evaluteData($data);
        $files = $this->mainClass->getCalenderList($this->access_token);
        return ['Files' => $files];
    }

    // New Event
    public function new_event(): array
    {

        $files = $this->mainClass->getCalenderList($this->access_token);

        foreach ($files as $file) {
            $form['Custom']['string'][] = [
                'id' => 'calendarId/24110/' . $file['id'],
                'name' => $file['summary']
            ];
        }
        $id = rand(0, 4548575451);
        $view[] = view('App.Actions.Fields.Input', [
            'form' => $form,
            'id' => $id,
            'label' => Helpers::translate('Select Calendar'),
            'required' => true,
            'multiple' => false
        ])->render();
        $view = Helpers::rap_with_form($view, [], 'triggerForm');
        $scripts[] = view('App.Actions.Fields.Script', [
            'form' => $form,
            'id' => $id,
            'labelId' => 'calendarId',
            'labelName' => Helpers::translate('Select Calendar'),
        ])->render();

        return [
            'view' => $view,
            'script' => $scripts,
            'message' => Helpers::translate('Connected With Google Calendar'),
            'status' => 200,
        ];

    }


    public function new_event_check($data = [])
    {
        $data = Helpers::IllitarableArray($data);
        $value = Helpers::evaluteData($data);
        $calendarId = $value['string']['calendarId'][0];

        $events = $this->mainClass->getEventsList($this->access_token, ['calenderId' => $calendarId]);
        $string['string'] = $this->mainClass->getEventObject($events[count($events) - 1]);
        return $string;
    }


    public function new_event_changes($zapDatabase = [], $zapData = [])
    {
        $zapDatabase = json_decode($zapDatabase, true)['Files'];
        $zapData = json_decode($zapData, true);
        $data = Helpers::IllitarableArray($zapData['trigger']['Data']);
        $value = Helpers::evaluteData($data);

        $fileData = [];

        $calendarId = $value['string']['calendarId'][0];
        $files = $this->mainClass->getEventsList($this->access_token, ['calenderId' => $calendarId]);
        $i = 5;

        foreach ($zapDatabase as $key => $database) {
            $zapDatabase[$database['id']] = $database;
            unset($zapDatabase[$key]);
        }
        foreach ($files as $file) {
            if (!array_key_exists($file['id'], $zapDatabase)) {
                $fileDetails = $file;
                $fileData[] = $this->mainClass->getEventObject($fileDetails);
            }
        }


        return $fileData;
    }

    public function new_event_update_database($data = [])
    {
        $data = Helpers::IllitarableArray($data);
        $value = Helpers::evaluteData($data);
        $calendarId = $value['string']['calendarId'][0];

        $files = $this->mainClass->getEventsList($this->access_token, ['calenderId' => $calendarId]);
        $save = [];
        foreach ($files as $file) {
            $save[] = ['id' => $file['id']];
        }
        return ['Files' => $save];
    }

    // New Event Started
    public function event_started(): array
    {

        $files = $this->mainClass->getCalenderList($this->access_token);

        foreach ($files as $file) {
            $form['Custom']['string'][] = [
                'id' => 'calendarId/24110/' . $file['id'],
                'name' => $file['summary']
            ];
        }
        $id = rand(0, 4548575451);
        $view[] = view('App.Actions.Fields.Input', [
            'form' => $form,
            'id' => $id,
            'label' => Helpers::translate('Select Calendar'),
            'required' => true,
            'multiple' => false
        ])->render();
        $view = Helpers::rap_with_form($view, [], 'triggerForm');
        $scripts[] = view('App.Actions.Fields.Script', [
            'form' => $form,
            'id' => $id,
            'labelId' => 'calendarId',
            'labelName' => Helpers::translate('Select Calendar'),
        ])->render();

        return [
            'view' => $view,
            'script' => $scripts,
            'message' => Helpers::translate('Connected With Google Calendar'),
            'status' => 200,
        ];

    }


    public function event_started_check($data = [])
    {
        $data = Helpers::IllitarableArray($data);
        $value = Helpers::evaluteData($data);
        $calendarId = $value['string']['calendarId'][0];

        $events = $this->mainClass->getEventsList($this->access_token, ['calenderId' => $calendarId]);
        $string['string'] = $this->mainClass->getEventObject($events[count($events) - 1]);
        return $string;
    }


    public function event_started_changes($zapDatabase = [], $zapData = [])
    {
        $zapDatabase = json_decode($zapDatabase, true)['Files'];
        $zapData = json_decode($zapData, true);
        $data = Helpers::IllitarableArray($zapData['trigger']['Data']);
        $value = Helpers::evaluteData($data);

        $fileData = [];

        $calendarId = $value['string']['calendarId'][0];
        $files = $this->mainClass->getEventsList($this->access_token, ['calenderId' => $calendarId]);
        $i = 5;

        foreach ($zapDatabase as $key => $database) {
            $zapDatabase[$database['id']] = $database;
            unset($zapDatabase[$key]);
        }
        foreach ($files as $file) {
            if (isset($file['start']['dateTime']) && isset($file['end']['dateTime'])) {
                date_default_timezone_set($file['start']['timeZone']);
                $start = strtotime($file['start']['dateTime']);
                $end = strtotime($file['end']['dateTime']);
                if (time() > $start && time() < $end) {
                    $fileData[] = $this->mainClass->getEventObject($file);
                }
            }
        }

        foreach ($fileData as $key => $file) {
            $id = $file['Id'];
            if (isset($zapDatabase[$id]['done']) && $zapDatabase[$id]['done'] == 1) {
                unset($fileData[$key]);
            }
        }

        return $fileData;
    }

    public function event_started_update_database($data = []): array
    {
        $data = Helpers::IllitarableArray($data);
        $value = Helpers::evaluteData($data);
        $calendarId = $value['string']['calendarId'][0];

        $files = $this->mainClass->getEventsList($this->access_token, ['calenderId' => $calendarId]);
        $save = [];

        foreach ($files as $file) {
            if (isset($file['start']['dateTime']) && isset($file['end']['dateTime'])) {
                date_default_timezone_set($file['start']['timeZone']);
                $start = strtotime($file['start']['dateTime']);
                $end = strtotime($file['end']['dateTime']);

                if (time() > $start) {
                    $save[] = [
                        'id' => $file['id'],
                        'done' => 1,
                    ];
                } else {
                    $save[] = [
                        'id' => $file['id']
                    ];
                }
            }
        }

        return ['Files' => $save];
    }

    // New Event Ended
    public function event_ended(): array
    {

        $files = $this->mainClass->getCalenderList($this->access_token);

        foreach ($files as $file) {
            $form['Custom']['string'][] = [
                'id' => 'calendarId/24110/' . $file['id'],
                'name' => $file['summary']
            ];
        }
        $id = rand(0, 4548575451);
        $view[] = view('App.Actions.Fields.Input', [
            'form' => $form,
            'id' => $id,
            'label' => Helpers::translate('Select Calendar'),
            'required' => true,
            'multiple' => false
        ])->render();
        $view = Helpers::rap_with_form($view, [], 'triggerForm');
        $scripts[] = view('App.Actions.Fields.Script', [
            'form' => $form,
            'id' => $id,
            'labelId' => 'calendarId',
            'labelName' => Helpers::translate('Select Calendar'),
        ])->render();

        return [
            'view' => $view,
            'script' => $scripts,
            'message' => Helpers::translate('Connected With Google Calendar'),
            'status' => 200,
        ];

    }


    public function event_ended_check($data = [])
    {
        $data = Helpers::IllitarableArray($data);
        $value = Helpers::evaluteData($data);
        $calendarId = $value['string']['calendarId'][0];

        $events = $this->mainClass->getEventsList($this->access_token, ['calenderId' => $calendarId]);
        $string['string'] = $this->mainClass->getEventObject($events[count($events) - 1]);
        return $string;
    }


    public function event_ended_changes($zapDatabase = [], $zapData = [])
    {
        $zapDatabase = json_decode($zapDatabase, true)['Files'];
        $zapData = json_decode($zapData, true);
        $data = Helpers::IllitarableArray($zapData['trigger']['Data']);
        $value = Helpers::evaluteData($data);

        $fileData = [];

        $calendarId = $value['string']['calendarId'][0];
        $files = $this->mainClass->getEventsList($this->access_token, ['calenderId' => $calendarId]);
        $i = 5;

        foreach ($zapDatabase as $key => $database) {
            $zapDatabase[$database['id']] = $database;
            unset($zapDatabase[$key]);
        }
        foreach ($files as $file) {
            if (isset($file['start']['dateTime']) && isset($file['end']['dateTime'])) {
                date_default_timezone_set($file['start']['timeZone']);
                $start = strtotime($file['start']['dateTime']);
                $end = strtotime($file['end']['dateTime']);
                if (time() > $end) {
                    $fileData[] = $this->mainClass->getEventObject($file);
                }
            }
        }

        foreach ($fileData as $key => $file) {
            $id = $file['Id'];
            if (isset($zapDatabase[$id]['done']) && $zapDatabase[$id]['done'] == 1) {
                unset($fileData[$key]);
            }
        }

        return $fileData;
    }

    public function event_ended_update_database($data = []): array
    {
        $data = Helpers::IllitarableArray($data);
        $value = Helpers::evaluteData($data);
        $calendarId = $value['string']['calendarId'][0];

        $files = $this->mainClass->getEventsList($this->access_token, ['calenderId' => $calendarId]);
        $save = [];

        foreach ($files as $file) {
            if (isset($file['start']['dateTime']) && isset($file['end']['dateTime'])) {
                date_default_timezone_set($file['start']['timeZone']);
                $start = strtotime($file['start']['dateTime']);
                $end = strtotime($file['end']['dateTime']);

                if (time() > $end) {
                    $save[] = [
                        'id' => $file['id'],
                        'done' => 1,
                    ];
                } else {
                    $save[] = [
                        'id' => $file['id']
                    ];
                }
            }
        }

        return ['Files' => $save];
    }

    // Event Cancelled

    public function event_cancelled_check($data = [])
    {
        $data = Helpers::IllitarableArray($data);
        $value = Helpers::evaluteData($data);
        $calendarId = $value['string']['calendarId'][0];

        $events = $this->mainClass->getEventListCancelled($this->access_token, ['calenderId' => $calendarId]);
        $string['string'] = $this->mainClass->getEventObject($events[count($events) - 1]);
        return $string;
    }


    public function event_cancelled_changes($zapDatabase = [], $zapData = [])
    {
        $zapDatabase = json_decode($zapDatabase, true)['Files'];
        $zapData = json_decode($zapData, true);
        $data = Helpers::IllitarableArray($zapData['trigger']['Data']);
        $value = Helpers::evaluteData($data);

        $fileData = [];

        $calendarId = $value['string']['calendarId'][0];
        $files = $this->mainClass->getEventListCancelled($this->access_token, ['calenderId' => $calendarId]);
        $i = 5;

        foreach ($zapDatabase as $key => $database) {
            $zapDatabase[$database['id']] = $database;
            unset($zapDatabase[$key]);
        }
        foreach ($files as $file) {
            $fileData[] = $this->mainClass->getEventObject($file);
        }

        foreach ($fileData as $key => $file) {
            $id = $file['Id'];
            if (isset($zapDatabase[$id]['done']) && $zapDatabase[$id]['done'] == 1) {
                unset($fileData[$key]);
            }
        }

        return $fileData;
    }

    public function event_cancelled_update_database($data = []): array
    {
        $data = Helpers::IllitarableArray($data);
        $value = Helpers::evaluteData($data);
        $calendarId = $value['string']['calendarId'][0];

        $files = $this->mainClass->getEventListCancelled($this->access_token, ['calenderId' => $calendarId]);
        $save = [];

        foreach ($files as $file) {
            $save[] = [
                'id' => $file['id'],
                'done' => 1,
            ];
        }

        return ['Files' => $save];
    }

}
