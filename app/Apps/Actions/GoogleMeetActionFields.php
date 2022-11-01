<?php

namespace App\Apps\Actions;

use App\Apps\GoogleMeet;
use App\Http\Controllers\Api\Apps\Manager;
use App\Logic\Helpers;
use App\Models\Account;

class GoogleMeetActionFields
{

    public function schedule_a_meeting($data)
    {
        $form = [];


        // Labels
        $actionAccount = $data['action']['account_id'];
        $drive = new GoogleMeet;
        $accessToken = $drive->getToken($actionAccount);
        $userId = $drive->getUserId($actionAccount);
        $allLabels = $drive->getCalenderList($accessToken);
        // Custom Data
        foreach ($allLabels as $key => $value) {
            $form['Custom']['string'][] = [
                'id' => "calenderId/24110/" . $value['id'],
                'name' => $value['id']
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


        $view = Helpers::rap_with_form($views, $data);


        return [
            'view' => $view,
            'script' => $scripts,
            'message' => Helpers::translate('Connected With Data Fields'),
            'status' => 200,
        ];

    }

    public function schedule_a_meeting_post($accountId, $mainData, $data)
    {
        $this->account = Account::find($accountId);
        $this->userId = json_decode($this->account['data'], true)['sub'];
        $this->mainClass = new GoogleMeet;
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
            $params['attendee'] = $mainData['attendee'];
        } else {
            $error = 1;
        }
        if (!$error) {
            $this->mainClass->createMeeting($this->access_token, $params);
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
}
