<?php

namespace App\Apps\Actions;

use App\Apps\GoogleCalendar;
use App\Apps\GoogleMeet;
use App\Apps\GoogleSheet;
use App\Http\Controllers\Api\Apps\Manager;
use App\Logic\Helpers;
use App\Models\Account;
use PHPUnit\TextUI\Help;

class GoogleCalendarActionFields
{
    private $mainClass;

    function __construct($accountId = 0)
    {
        $this->mainClass = new GoogleCalendar;
        if ($accountId != 0) {
            $this->account = Account::find($accountId);
            $this->userId = json_decode($this->account['data'], true)['sub'];
            $this->mainClass = new GoogleCalendar;
            $this->access_token = $this->mainClass->getToken($accountId);
        }
    }

    public function create_calendar($data)
    {
        $form = [];
        $id = rand();
        $dataV = json_decode(json_encode($data, true), true);
        $manager = new Manager;
        $api_fields = $manager->getTriggerValue($dataV);
        $form['Custom']['custom'][] = [
            'id' => 'custom',
            'name' => Helpers::translate('Add Custom')
        ];
        if (isset($api_fields['string'])) {
            foreach ($api_fields['string'] as $key1 => $value1) {
                $form['Api']['api'][] = [
                    'id' => "summary/24110/" . $key1,
                    'name' => $key1
                ];
            }
        }

        $view = view('App.Actions.Fields.Input', [
            'form' => $form,
            'id' => $id,
            'label' => Helpers::translate('Calender Title'),
            'labelId' => "summary",
            'required' => true
        ])->render();
        $views[] = $view;

        $scripts[] = view('App.Actions.Fields.Script', [
            'form' => $form,
            'id' => $id,
            'labelId' => "summary",
            'labelName' => Helpers::translate('Calender Title'),
        ])->render();


        $view = Helpers::rap_with_form($views, $data);


        return [
            'view' => $view,
            'script' => $scripts,
            'message' => Helpers::translate('Connected With Data Fields'),
            'status' => 200,
        ];

    }

    public function create_calendar_post($accountId, $mainData, $data)
    {
        $this->account = Account::find($accountId);
        $this->userId = json_decode($this->account['data'], true)['sub'];
        $this->mainClass = new GoogleCalendar;
        $this->access_token = $this->mainClass->getToken($accountId);
        $error = 0;
        $params = [];
        if (isset($mainData['summary'])) {
            $params['summary'] = $mainData['summary'];
            $this->mainClass->createCalendar($this->access_token, $params);
            return json_encode([
                'status' => 200,
                'message' => Helpers::translate('Successfully Applied First Nit')
            ]);
        } else {
            return json_encode([
                'status' => 400,
                'message' => Helpers::translate('Failed to Process your request. Please check again the information.')
            ]);

        }
    }

// Quick Add Event
    public function quick_add_event($data)
    {
        // Labels
        $form = [];
        $actionAccount = $data['action']['account_id'];
        $drive = new GoogleCalendar;
        $accessToken = $drive->getToken($actionAccount);
        $userId = $drive->getUserId($actionAccount);
        $allLabels = $drive->getCalenderList($accessToken);
        // Custom Data
        foreach ($allLabels as $key => $value) {
            $form['Custom']['string'][] = [
                'id' => "calenderId/24110/" . $value['id'],
                'name' => $value['summary']
            ];
        }
        $id = rand(0, 4548575451);

        $dataV = json_decode(json_encode($data, true), true);
        $manager = new Manager;
        $api_fields = $manager->getTriggerValue($dataV);
        $form['Custom']['custom'][] = [
            'id' => 'custom',
            'name' => Helpers::translate('Add Custom')
        ];
        if (isset($api_fields['string'])) {
            foreach ($api_fields['string'] as $key1 => $value1) {
                $form['Api']['api'][] = [
                    'id' => "calenderId/24110/" . $key1,
                    'name' => $key1
                ];
            }
        }

        $view = view('App.Actions.Fields.Input', [
            'form' => $form,
            'id' => $id,
            'label' => Helpers::translate('Calender Id'),
            'labelId' => "calenderId",
            'required' => true
        ])->render();
        $views[] = $view;

        $scripts[] = view('App.Actions.Fields.Script', [
            'form' => $form,
            'id' => $id,
            'labelId' => "calenderId",
            'labelName' => "Calender Id",
        ])->render();

        $form = [];
        $id = rand();
        $dataV = json_decode(json_encode($data, true), true);
        $manager = new Manager;
        $api_fields = $manager->getTriggerValue($dataV);
        $form['Custom']['custom'][] = [
            'id' => 'custom',
            'name' => Helpers::translate('Add Custom')
        ];
        if (isset($api_fields['string'])) {
            foreach ($api_fields['string'] as $key1 => $value1) {
                $form['Api']['api'][] = [
                    'id' => "text/24110/" . $key1,
                    'name' => $key1
                ];
            }
        }

        $view = view('App.Actions.Fields.Input', [
            'form' => $form,
            'id' => $id,
            'label' => Helpers::translate('Event Title'),
            'labelId' => "text",
            'required' => true
        ])->render();
        $views[] = $view;

        $scripts[] = view('App.Actions.Fields.Script', [
            'form' => $form,
            'id' => $id,
            'labelId' => "text",
            'labelName' => Helpers::translate('Event Title'),
        ])->render();


        $view = Helpers::rap_with_form($views, $data);


        return [
            'view' => $view,
            'script' => $scripts,
            'message' => Helpers::translate('Connected With Data Fields'),
            'status' => 200,
        ];

    }

    public function quick_add_event_post($accountId, $mainData, $data)
    {
        $this->account = Account::find($accountId);
        $this->userId = json_decode($this->account['data'], true)['sub'];
        $this->mainClass = new GoogleCalendar;
        $this->access_token = $this->mainClass->getToken($accountId);

        $error = 0;
        $params = [];
        if (isset($mainData['text'])) {
            $params['text'] = urlencode(Helpers::stringorsingle($mainData['text']));
            $params['calenderId'] = Helpers::stringorsingle($mainData['calenderId']);
            $this->mainClass->quickAddEvent($this->access_token, $params);
            return json_encode([
                'status' => 200,
                'message' => Helpers::translate('Successfully Applied First Nit')
            ]);
        } else {
            return json_encode([
                'status' => 400,
                'message' => Helpers::translate('Failed to Process your request. Please check again the information.')
            ]);

        }
    }


    public function add_attendees($data)
    {

        // Labels
        $actionAccount = $data['action']['account_id'];
        $accountId = $data['action']['account_id'];
        $drive = new GoogleCalendar;
        $accessToken = $drive->getToken($actionAccount);
        $userId = $drive->getUserId($actionAccount);
        $allLabels = $drive->getCalenderList($accessToken);
        $events = $drive->getEventsList($accessToken, ['calenderId' => $allLabels[0]['id']]);
        // Custom Data
        foreach ($allLabels as $key => $value) {
            $form['Custom']['string'][] = [
                'id' => "calenderId/24110/" . $value['id'],
                'name' => $value['summary']
            ];
        }
        $id = rand(0, 4548575451);

        $dataV = json_decode(json_encode($data, true), true);
        $manager = new Manager;
        $api_fields = $manager->getTriggerValue($dataV);

        $view = view('App.Actions.Fields.Input', [
            'form' => $form,
            'id' => $id,
            'label' => Helpers::translate('Calender Id'),
            'labelId' => "calenderId",
            'required' => true,
            'multiple' => false,
            'dataLoad' => 'sheetId2250ssdsd',
            'formName' => 'action_suffer',
            'dataAction' => json_encode([
                'AppId' => 'GoogleCalendar',
                'Func' => 'UpdateEvents',
                'Mode' => 'Actions',
                'AccountId' => $accountId
            ])
        ])->render();
        $views[] = $view;

//BCC


        $form = [];
        $form['Custom']['custom'][] = [
            'id' => 'custom',
            'name' => Helpers::translate('Add Custom')
        ];
        $id = rand(0, 4548575451);
        $form = [];
        foreach ($events as $sheet) {
            $form['Custom']['string'][] = [
                'id' => "event/24110/" . $sheet['id'],
                'name' => $sheet['summary']
            ];
        }
        $view = view('App.Actions.Fields.Input', [
            'form' => $form,
            'id' => $id,
            'label' => Helpers::translate('Select Event'),
            'required' => true,
            'multiple' => false,
            'acceptDataLoad' => 'sheetId2250ssdsd',
        ])->render();

        $views[] = $view;
        $form = [];
        // Labels
        $id = rand(0, 4548575451);
        $form['Custom']['custom'][] = [
            'id' => 'custom',
            'name' => Helpers::translate('Add Custom')
        ];
        if (isset($api_fields['string'])) {
            foreach ($api_fields['string'] as $key1 => $value1) {
                $form['Api']['api'][] = [
                    'id' => "attendee/24110/" . $key1,
                    'name' => $key1
                ];
            }
        }

        $view = view('App.Actions.Fields.Input', [
            'form' => $form,
            'id' => $id,
            'label' => "attendee",
            'labelId' => "Attendee Emails",
        ])->render();
        $views[] = $view;

        $scripts[] = view('App.Actions.Fields.Script', [
            'form' => $form,
            'id' => $id,
            'labelId' => "attendee",
            'labelName' => "Attendee Emails",
        ])->render();
        $view = Helpers::rap_with_form($views, $data);

        return [
            'view' => $view,
            'script' => $scripts,
            'message' => Helpers::translate('Connected With Data Fields'),
            'status' => 200,
        ];

    }

    public function add_attendees_post($accountId, $mainData, $data)
    {
        $this->account = Account::find($accountId);
        $this->userId = json_decode($this->account['data'], true)['sub'];
        $this->mainClass = new GoogleCalendar;
        $this->access_token = $this->mainClass->getToken($accountId);
        $error = 0;
        $params = [];
        if (isset($mainData['calenderId'])) {
            $params['calenderId'] = Helpers::stringorsingle($mainData['calenderId']);
            $error = 1;
            $calenderList = $this->mainClass->getCalenderList($this->access_token);
            foreach ($calenderList as $value) {
                if ($value['id'] == $params['calenderId']) {
                    $params['timezone'] = $value['timeZone'];
                    $error = 0;
                }
            }
        } else {
            $error = 1;
        }
        if (isset($mainData['event'])) {
            $params['event'] = Helpers::stringorexplode($mainData['event']);
        } else {
            $error = 1;
        }
        if (isset($mainData['attendee'])) {
            $params['attendees'] = $mainData['attendee'];
        }
        if (!$error) {
            $this->mainClass->updateEvent($this->access_token, $params);
            return json_encode([
                'status' => 200,
                'message' => Helpers::translate('Successfully Applied First Nit')
            ]);
        } else {
            return json_encode([
                'status' => 400,
                'message' => Helpers::translate('Failed to Process your request. Please check again the information.')
            ]);

        }
    }

    public function create_event($data)
    {

        // Labels
        $actionAccount = $data['action']['account_id'];
        $drive = new GoogleCalendar;
        $accessToken = $drive->getToken($actionAccount);
        $userId = $drive->getUserId($actionAccount);
        $allLabels = $drive->getCalenderList($accessToken);
        // Custom Data
        foreach ($allLabels as $key => $value) {
            $form['Custom']['string'][] = [
                'id' => "calenderId/24110/" . $value['id'],
                'name' => $value['summary']
            ];
        }
        $id = rand(0, 4548575451);

        $dataV = json_decode(json_encode($data, true), true);
        $manager = new Manager;
        $api_fields = $manager->getTriggerValue($dataV);
        $form['Custom']['custom'][] = [
            'id' => 'custom',
            'name' => Helpers::translate('Add Custom')
        ];
        if (isset($api_fields['string'])) {
            foreach ($api_fields['string'] as $key1 => $value1) {
                $form['Api']['api'][] = [
                    'id' => "calenderId/24110/" . $key1,
                    'name' => $key1
                ];
            }
        }

        $view = view('App.Actions.Fields.Input', [
            'form' => $form,
            'id' => $id,
            'label' => Helpers::translate('Calender Id'),
            'labelId' => "calenderId",
            'required' => true
        ])->render();
        $views[] = $view;

        $scripts[] = view('App.Actions.Fields.Script', [
            'form' => $form,
            'id' => $id,
            'labelId' => "calenderId",
            'labelName' => "Calender Id",
        ])->render();

//BCC


        $form = [];
        $form['Custom']['custom'][] = [
            'id' => 'custom',
            'name' => Helpers::translate('Add Custom')
        ];
        if (isset($api_fields['string'])) {
            foreach ($api_fields['string'] as $key1 => $value1) {
                $form['Api']['api'][] = [
                    'id' => "summary/24110/" . $key1,
                    'name' => $key1
                ];
            }
        }
        // Labels
        $id = rand(0, 4548575451);


        $view = view('App.Actions.Fields.Input', [
            'form' => $form,
            'id' => $id,
            'label' => "summary",
            'labelId' => "Summary",
        ])->render();
        $views[] = $view;

        $scripts[] = view('App.Actions.Fields.Script', [
            'form' => $form,
            'id' => $id,
            'labelId' => "summary",
            'labelName' => "Summary",
        ])->render();


        $form = [];
        $form['Custom']['custom'][] = [
            'id' => 'custom',
            'name' => Helpers::translate('Add Custom')
        ];
        if (isset($api_fields['string'])) {
            foreach ($api_fields['string'] as $key1 => $value1) {
                $form['Api']['api'][] = [
                    'id' => "description/24110/" . $key1,
                    'name' => $key1
                ];
            }
        }
        // Labels
        $id = rand(0, 4548575451);


        $view = view('App.Actions.Fields.Input', [
            'form' => $form,
            'id' => $id,
            'label' => "description",
            'labelId' => "Description",
        ])->render();
        $views[] = $view;

        $scripts[] = view('App.Actions.Fields.Script', [
            'form' => $form,
            'id' => $id,
            'labelId' => "description",
            'labelName' => "Description",
        ])->render();


        $form = [];
        $form['Custom']['custom'][] = [
            'id' => 'custom',
            'name' => Helpers::translate('Add Custom')
        ];
        if (isset($api_fields['string'])) {
            foreach ($api_fields['string'] as $key1 => $value1) {
                $form['Api']['api'][] = [
                    'id' => "start/24110/" . $key1,
                    'name' => $key1
                ];
            }
        }
        // Labels
        $id = rand(0, 4548575451);


        $view = view('App.Actions.Fields.Input', [
            'form' => $form,
            'id' => $id,
            'label' => "start",
            'labelId' => "Start Date & Time",
        ])->render();
        $views[] = $view;

        $scripts[] = view('App.Actions.Fields.Script', [
            'form' => $form,
            'id' => $id,
            'labelId' => "start",
            'labelName' => "Start Date & Time",
        ])->render();


        $form = [];
        $form['Custom']['custom'][] = [
            'id' => 'custom',
            'name' => Helpers::translate('Add Custom')
        ];
        if (isset($api_fields['string'])) {
            foreach ($api_fields['string'] as $key1 => $value1) {
                $form['Api']['api'][] = [
                    'id' => "end/24110/" . $key1,
                    'name' => $key1
                ];
            }
        }
        // Labels
        $id = rand(0, 4548575451);


        $view = view('App.Actions.Fields.Input', [
            'form' => $form,
            'id' => $id,
            'label' => "end",
            'labelId' => "End Date & Time",
        ])->render();
        $views[] = $view;

        $scripts[] = view('App.Actions.Fields.Script', [
            'form' => $form,
            'id' => $id,
            'labelId' => "end",
            'labelName' => "End Date & Time",
        ])->render();

        $form = [];
        $form['Custom']['custom'][] = [
            'id' => 'custom',
            'name' => Helpers::translate('Add Custom')
        ];
        if (isset($api_fields['string'])) {
            foreach ($api_fields['string'] as $key1 => $value1) {
                $form['Api']['api'][] = [
                    'id' => "attendee/24110/" . $key1,
                    'name' => $key1
                ];
            }
        }
        // Labels
        $id = rand(0, 4548575451);


        $view = view('App.Actions.Fields.Input', [
            'form' => $form,
            'id' => $id,
            'label' => "attendee",
            'labelId' => "Attendee Emails",
        ])->render();
        $views[] = $view;

        $scripts[] = view('App.Actions.Fields.Script', [
            'form' => $form,
            'id' => $id,
            'labelId' => "attendee",
            'labelName' => "Attendee Emails",
        ])->render();

        // Labels
        $id = rand(0, 4548575451);

        $form = [];

        $form['Custom']['string'][] = [
            'id' => "add_google_meet/24110/true",
            'name' => 'True'
        ];
        $form['Custom']['string'][] = [
            'id' => "add_google_meet/24110/false",
            'name' => 'False'
        ];
        $view = view('App.Actions.Fields.Input', [
            'form' => $form,
            'id' => $id,
            'label' => "Add Google Meet",
            'labelId' => "add_google_meet",
            'multiple' => false
        ])->render();
        $views[] = $view;

        $scripts[] = view('App.Actions.Fields.Script', [
            'form' => $form,
            'id' => $id,
            'labelId' => "add_google_meet",
            'labelName' => "Add Google Meet",
        ])->render();


        $view = Helpers::rap_with_form($views, $data);

        return [
            'view' => $view,
            'script' => $scripts,
            'message' => Helpers::translate('Connected With Data Fields'),
            'status' => 200,
        ];

    }

    public function create_event_post($accountId, $mainData, $data)
    {
        $this->account = Account::find($accountId);
        $this->userId = json_decode($this->account['data'], true)['sub'];
        $this->mainClass = new GoogleCalendar;
        $this->access_token = $this->mainClass->getToken($accountId);
        $error = 0;
        $params = [];
        if (isset($mainData['calenderId'])) {
            $params['calenderId'] = Helpers::stringorsingle($mainData['calenderId']);
            $error = 1;
            $calenderList = $this->mainClass->getCalenderList($this->access_token);
            foreach ($calenderList as $value) {
                if ($value['id'] == $params['calenderId']) {
                    $params['timezone'] = $value['timeZone'];
                    $error = 0;
                }
            }
        } else {
            $error = 1;
        }
        if (isset($mainData['description'])) {
            $params['description'] = Helpers::stringorexplode($mainData['description']);
        } else {
            $error = 1;
        }
        if (isset($mainData['summary'])) {
            $params['summary'] = Helpers::stringorexplode($mainData['summary']);
        } else {
            $error = 1;
        }
        date_default_timezone_set($params['timezone']);
        if (isset($mainData['start'])) {
            $params['start'] = date('c', strtotime(Helpers::stringorsingle($mainData['start'])));
        } else {
            $error = 1;
        }
        if (isset($mainData['end'])) {
            $params['end'] = date('c', strtotime(Helpers::stringorsingle($mainData['end'])));
        } else {
            $error = 1;
        }
        if (isset($mainData['attendee'])) {
            $params['attendees'] = $mainData['attendee'];
        }
        if (isset($mainData['add_google_meet'])) {
            $params['add_google_meet'] = $mainData['add_google_meet'];
        }
        if (!$error) {
            $this->mainClass->addEvent($this->access_token, $params);
            return json_encode([
                'status' => 200,
                'message' => Helpers::translate('Successfully Applied First Nit')
            ]);
        } else {
            return json_encode([
                'status' => 400,
                'message' => Helpers::translate('Failed to Process your request. Please check again the information.')
            ]);

        }
    }

    public function update_event($data)
    {

        // Labels
        $actionAccount = $data['action']['account_id'];
        $drive = new GoogleCalendar;
        $accessToken = $drive->getToken($actionAccount);
        $userId = $drive->getUserId($actionAccount);
        $allLabels = $drive->getCalenderList($accessToken);
        $events = $drive->getEventsList($accessToken, ['calenderId' => $allLabels[0]['id']]);
        // Custom Data
        $form = [];
        foreach ($allLabels as $key => $value) {
            $form['Custom']['string'][] = [
                'id' => "calenderId/24110/" . $value['id'],
                'name' => $value['summary']
            ];
        }
        $id = rand(0, 4548575451);

        $dataV = json_decode(json_encode($data, true), true);
        $manager = new Manager;
        $api_fields = $manager->getTriggerValue($dataV);

        $view = view('App.Actions.Fields.Input', [
            'form' => $form,
            'id' => $id,
            'label' => Helpers::translate('Calender Id'),
            'labelId' => "calenderId",
            'required' => true,
            'multiple' => false,
            'dataLoad' => 'sheetId2250ssdsd',
            'formName' => 'action_suffer',
            'dataAction' => json_encode([
                'AppId' => 'GoogleCalendar',
                'Func' => 'UpdateEvents',
                'Mode' => 'Actions',
                'AccountId' => $actionAccount
            ])
        ])->render();
        $views[] = $view;

//BCC


        $form = [];
        $form['Custom']['custom'][] = [
            'id' => 'custom',
            'name' => Helpers::translate('Add Custom')
        ];
        $id = rand(0, 4548575451);
        $form = [];
        foreach ($events as $sheet) {
            $form['Custom']['string'][] = [
                'id' => "event/24110/" . $sheet['id'],
                'name' => $sheet['summary']
            ];
        }
        $view = view('App.Actions.Fields.Input', [
            'form' => $form,
            'id' => $id,
            'label' => Helpers::translate('Select Event'),
            'required' => true,
            'multiple' => false,
            'acceptDataLoad' => 'sheetId2250ssdsd',
        ])->render();

        $views[] = $view;

        $form = [];
        $form['Custom']['custom'][] = [
            'id' => 'custom',
            'name' => Helpers::translate('Add Custom')
        ];
        if (isset($api_fields['string'])) {
            foreach ($api_fields['string'] as $key1 => $value1) {
                $form['Api']['api'][] = [
                    'id' => "summary/24110/" . $key1,
                    'name' => $key1
                ];
            }
        }
        // Labels
        $id = rand(0, 4548575451);


        $view = view('App.Actions.Fields.Input', [
            'form' => $form,
            'id' => $id,
            'label' => "summary",
            'labelId' => "Summary",
        ])->render();
        $views[] = $view;

        $scripts[] = view('App.Actions.Fields.Script', [
            'form' => $form,
            'id' => $id,
            'labelId' => "summary",
            'labelName' => "Summary",
        ])->render();


        $form = [];
        $form['Custom']['custom'][] = [
            'id' => 'custom',
            'name' => Helpers::translate('Add Custom')
        ];
        if (isset($api_fields['string'])) {
            foreach ($api_fields['string'] as $key1 => $value1) {
                $form['Api']['api'][] = [
                    'id' => "description/24110/" . $key1,
                    'name' => $key1
                ];
            }
        }
        // Labels
        $id = rand(0, 4548575451);


        $view = view('App.Actions.Fields.Input', [
            'form' => $form,
            'id' => $id,
            'label' => "description",
            'labelId' => "Description",
        ])->render();
        $views[] = $view;

        $scripts[] = view('App.Actions.Fields.Script', [
            'form' => $form,
            'id' => $id,
            'labelId' => "description",
            'labelName' => "Description",
        ])->render();


        $form = [];
        $form['Custom']['custom'][] = [
            'id' => 'custom',
            'name' => Helpers::translate('Add Custom')
        ];
        if (isset($api_fields['string'])) {
            foreach ($api_fields['string'] as $key1 => $value1) {
                $form['Api']['api'][] = [
                    'id' => "start/24110/" . $key1,
                    'name' => $key1
                ];
            }
        }
        // Labels
        $id = rand(0, 4548575451);


        $view = view('App.Actions.Fields.Input', [
            'form' => $form,
            'id' => $id,
            'label' => "start",
            'labelId' => "Start Date & Time",
        ])->render();
        $views[] = $view;

        $scripts[] = view('App.Actions.Fields.Script', [
            'form' => $form,
            'id' => $id,
            'labelId' => "start",
            'labelName' => "Start Date & Time",
        ])->render();


        $form = [];
        $form['Custom']['custom'][] = [
            'id' => 'custom',
            'name' => Helpers::translate('Add Custom')
        ];
        if (isset($api_fields['string'])) {
            foreach ($api_fields['string'] as $key1 => $value1) {
                $form['Api']['api'][] = [
                    'id' => "end/24110/" . $key1,
                    'name' => $key1
                ];
            }
        }
        // Labels
        $id = rand(0, 4548575451);


        $view = view('App.Actions.Fields.Input', [
            'form' => $form,
            'id' => $id,
            'label' => "end",
            'labelId' => "End Date & Time",
        ])->render();
        $views[] = $view;

        $scripts[] = view('App.Actions.Fields.Script', [
            'form' => $form,
            'id' => $id,
            'labelId' => "end",
            'labelName' => "End Date & Time",
        ])->render();

        $form = [];
        $form['Custom']['custom'][] = [
            'id' => 'custom',
            'name' => Helpers::translate('Add Custom')
        ];
        if (isset($api_fields['string'])) {
            foreach ($api_fields['string'] as $key1 => $value1) {
                $form['Api']['api'][] = [
                    'id' => "attendee/24110/" . $key1,
                    'name' => $key1
                ];
            }
        }
        // Labels
        $id = rand(0, 4548575451);


        $view = view('App.Actions.Fields.Input', [
            'form' => $form,
            'id' => $id,
            'label' => "attendee",
            'labelId' => "Attendee Emails",
        ])->render();
        $views[] = $view;

        $scripts[] = view('App.Actions.Fields.Script', [
            'form' => $form,
            'id' => $id,
            'labelId' => "attendee",
            'labelName' => "Attendee Emails",
        ])->render();

        // Labels
        $id = rand(0, 4548575451);

        $form = [];

        $form['Custom']['string'][] = [
            'id' => "add_google_meet/24110/true",
            'name' => 'True'
        ];
        $form['Custom']['string'][] = [
            'id' => "add_google_meet/24110/false",
            'name' => 'False'
        ];
        $view = view('App.Actions.Fields.Input', [
            'form' => $form,
            'id' => $id,
            'label' => "Add Google Meet",
            'labelId' => "add_google_meet",
            'multiple' => false
        ])->render();
        $views[] = $view;

        $scripts[] = view('App.Actions.Fields.Script', [
            'form' => $form,
            'id' => $id,
            'labelId' => "add_google_meet",
            'labelName' => "Add Google Meet",
        ])->render();


        $view = Helpers::rap_with_form($views, $data);

        return [
            'view' => $view,
            'script' => $scripts,
            'message' => Helpers::translate('Connected With Data Fields'),
            'status' => 200,
        ];

    }

    public function update_event_post($accountId, $mainData, $data)
    {
        $this->account = Account::find($accountId);
        $this->userId = json_decode($this->account['data'], true)['sub'];
        $this->mainClass = new GoogleCalendar;
        $this->access_token = $this->mainClass->getToken($accountId);
        $error = 0;
        $params = [];
        if (isset($mainData['calenderId'])) {
            $params['calenderId'] = Helpers::stringorsingle($mainData['calenderId']);
            $error = 1;
            $calenderList = $this->mainClass->getCalenderList($this->access_token);
            foreach ($calenderList as $value) {
                if ($value['id'] == $params['calenderId']) {
                    $params['timezone'] = $value['timeZone'];
                    $error = 0;
                }
            }
        }
        if (isset($mainData['description'])) {
            $params['description'] = Helpers::stringorexplode($mainData['description']);
        }
        if (isset($mainData['summary'])) {
            $params['summary'] = Helpers::stringorexplode($mainData['summary']);
        }
        if (isset($mainData['event'])) {
            $params['event'] = Helpers::stringorexplode($mainData['event']);
        }
        date_default_timezone_set($params['timezone']);
        if (isset($mainData['start'])) {
            $params['start']['dateTime'] = date('c', strtotime(Helpers::stringorsingle($mainData['start'])));
        }
        if (isset($mainData['end'])) {
            $params['end']['dateTime'] = date('c', strtotime(Helpers::stringorsingle($mainData['end'])));
        }
        if (isset($mainData['attendee'])) {
            $params['attendees'] = $mainData['attendee'];
        }
        if (isset($mainData['add_google_meet'])) {
            $params['add_google_meet'] = $mainData['add_google_meet'][0];
        }
        if (!$error) {
            $this->mainClass->updateEvent($this->access_token, $params);
            return json_encode([
                'status' => 200,
                'message' => Helpers::translate('Successfully Applied First Nit')
            ]);
        } else {
            return json_encode([
                'status' => 400,
                'message' => Helpers::translate('Failed to Process your request. Please check again the information.')
            ]);

        }
    }


    public function UpdateEvents($Fid, $Fsheet, $data = null): array
    {
        // Labels
        $id = rand(0, 4548575451);
        $form = [];
        $sheetId = $Fsheet['string']['calenderId'][0];
        $sheets = $this->mainClass->getEventsList($this->access_token, ['calenderId' => $sheetId]);
        foreach ($sheets as $sheet) {
            $form['Custom']['string'][] = [
                'id' => "event/24110/" . $sheet['id'],
                'name' => $sheet['summary'] ?? "Unknown"
            ];
        }
        $view = view('App.Actions.Fields.Input', [
            'form' => $form,
            'id' => $id,
            'label' => Helpers::translate('Select Event'),
            'required' => true,
            'multiple' => false,
            'acceptDataLoad' => 'sheetId2250ssdsd',
        ])->render();
        return [
            'view' => $view,
            'id' => $Fid
        ];
    }
}
