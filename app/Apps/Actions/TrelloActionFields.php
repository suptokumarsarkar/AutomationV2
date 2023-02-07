<?php

namespace App\Apps\Actions;

use App\Apps\Trello;
use App\Http\Controllers\Api\Apps\Manager;
use App\Logic\Helpers;
use App\Models\Account;

class TrelloActionFields
{
    function __construct($accountId = 0)
    {
        $this->mainClass = new Trello();
        if ($accountId != 0) {
            $this->account = Account::find($accountId);
            $this->mainClass = new Trello();
            $this->userId = $this->mainClass->getUserId();
            $this->access_token = $this->mainClass->getToken($accountId);
        }
    }

    public function create_board($data)
    {
        // Labels
        $actionAccount = $data['action']['account_id'];
        $drive = new Trello;
        $accessToken = $drive->getToken($actionAccount);
        $organizations = $drive->listOrganizations($accessToken, ['memberId' => $drive->getUserId()]);
        $form = [];

        foreach ($organizations as $key1 => $value1) {
            $form['Custom']['string'][] = [
                'id' => "idOrganization/24110/" . $value1['id'],
                'name' => $value1['name']
            ];
        }

        // Labels
        $id = rand(0, 4548575451);


        $view = view('App.Actions.Fields.Input', [
            'form' => $form,
            'id' => $id,
            'label' => "Select Organization",
            'labelId' => "idOrganization",
            'multiple' => false
        ])->render();
        $views[] = $view;

        $scripts[] = view('App.Actions.Fields.Script', [
            'form' => $form,
            'id' => $id,
            'labelId' => "idOrganization",
            'labelName' => "Select Organization",
        ])->render();

//BCC
        $dataV = json_decode(json_encode($data, true), true);
        $manager = new Manager;
        $api_fields = $manager->getTriggerValue($dataV);
        $form = [];
        $form['Custom']['custom'][] = [
            'id' => 'custom',
            'name' => Helpers::translate('Add Custom')
        ];
        if (isset($api_fields['string'])) {
            foreach ($api_fields['string'] as $key1 => $value1) {
                $form['Api']['api'][] = [
                    'id' => "name/24110/" . $key1,
                    'name' => $key1
                ];
            }
        }
        // Labels
        $id = rand(0, 4548575451);


        $view = view('App.Actions.Fields.Input', [
            'form' => $form,
            'id' => $id,
            'label' => "Title",
            'labelId' => "name",
        ])->render();
        $views[] = $view;

        $scripts[] = view('App.Actions.Fields.Script', [
            'form' => $form,
            'id' => $id,
            'labelId' => "name",
            'labelName' => "Title",
        ])->render();


        $form = [];
        $form['Custom']['custom'][] = [
            'id' => 'custom',
            'name' => Helpers::translate('Add Custom')
        ];
        if (isset($api_fields['string'])) {
            foreach ($api_fields['string'] as $key1 => $value1) {
                $form['Api']['api'][] = [
                    'id' => "desc/24110/" . $key1,
                    'name' => $key1
                ];
            }
        }
        // Labels
        $id = rand(0, 4548575451);


        $view = view('App.Actions.Fields.Input', [
            'form' => $form,
            'id' => $id,
            'label' => "Description",
            'labelId' => "desc",
        ])->render();
        $views[] = $view;

        $scripts[] = view('App.Actions.Fields.Script', [
            'form' => $form,
            'id' => $id,
            'labelId' => "desc",
            'labelName' => "Description",
        ])->render();


        $form = [];
        $id = rand(0, 4548575451);

        $form['Custom']['string'][] = [
            'id' => "prefs_permissionLevel/24110/org",
            'name' => 'ORG'
        ];
        $form['Custom']['string'][] = [
            'id' => "prefs_permissionLevel/24110/private",
            'name' => 'Private'
        ];
        $form['Custom']['string'][] = [
            'id' => "prefs_permissionLevel/24110/public",
            'name' => 'Public'
        ];

        $view = view('App.Actions.Fields.Input', [
            'form' => $form,
            'id' => $id,
            'label' => "Permission Level",
            'labelId' => "prefs_permissionLevel",
            'multiple' => false
        ])->render();
        $views[] = $view;

        $view = Helpers::rap_with_form($views, $data);


        return [
            'view' => $view,
            'script' => $scripts,
            'message' => Helpers::translate('Connected With Data Fields'),
            'status' => 200,
        ];

    }

    public function create_board_post($accountId, $mainData, $data)
    {
        $this->account = Account::find($accountId);
        $this->mainClass = new Trello;
        $this->userId = $this->mainClass->getUserId();

        $this->access_token = $this->mainClass->getToken($accountId);
        if (isset($mainData['name'])) {
            $params = [
                'name' => Helpers::stringorexplode($mainData['name']),
                'desc' => Helpers::stringorexplode($mainData['desc']),
                'idOrganization' => Helpers::stringorsingle($mainData['idOrganization']),
                'prefs_permissionLevel' => Helpers::stringorsingle($mainData['prefs_permissionLevel']),
            ];
            $this->mainClass->createBoard($this->access_token, $params);

            return json_encode([
                'status' => 200,
                'message' => Helpers::translate('Successfully Applied First Nit')
            ]);
        } else {
            return json_encode([
                'status' => 400,
                'message' => Helpers::translate('Failed to Process your request.')
            ]);

        }
    }

    // Close Board
    public function close_board($data)
    {
        $form = [];


        // Labels
        $actionAccount = $data['action']['account_id'];
        $drive = new Trello;
        $accessToken = $drive->getToken($actionAccount);
        $userId = $drive->getUserId($actionAccount);
        $organizations = $drive->listBoards($accessToken, ['memberId' => $drive->getUserId()]);
        $form = [];

        foreach ($organizations as $key1 => $value1) {
            $form['Custom']['string'][] = [
                'id' => "id/24110/" . $value1['id'],
                'name' => $value1['name']
            ];
        }

        // Labels
        $id = rand(0, 4548575451);


        $view = view('App.Actions.Fields.Input', [
            'form' => $form,
            'id' => $id,
            'label' => "Select Board",
            'labelId' => "id",
            'multiple' => false
        ])->render();
        $views[] = $view;

        $scripts[] = view('App.Actions.Fields.Script', [
            'form' => $form,
            'id' => $id,
            'labelId' => "id",
            'labelName' => "Select Board",
        ])->render();

        $view = Helpers::rap_with_form($views, $data);


        return [
            'view' => $view,
            'script' => $scripts,
            'message' => Helpers::translate('Connected With Data Fields'),
            'status' => 200,
        ];

    }

    public function close_board_post($accountId, $mainData, $data)
    {
        $this->account = Account::find($accountId);
        $this->mainClass = new Trello;
        $this->userId = $this->mainClass->getUserId();

        $this->access_token = $this->mainClass->getToken($accountId);
        if (isset($mainData['id'])) {
            $params = [
                'id' => Helpers::stringorsingle($mainData['id']),
            ];
            $this->mainClass->closeBoard($this->access_token, $params);

            return json_encode([
                'status' => 200,
                'message' => Helpers::translate('Successfully Applied First Nit')
            ]);
        } else {
            return json_encode([
                'status' => 400,
                'message' => Helpers::translate('Failed to Process your request.')
            ]);

        }
    }

    // Close Board
    public function delete_board($data)
    {
        $form = [];


        // Labels
        $actionAccount = $data['action']['account_id'];
        $drive = new Trello;
        $accessToken = $drive->getToken($actionAccount);
        $userId = $drive->getUserId($actionAccount);
        $organizations = $drive->listBoards($accessToken, ['memberId' => $drive->getUserId()]);
        $form = [];

        foreach ($organizations as $key1 => $value1) {
            $form['Custom']['string'][] = [
                'id' => "id/24110/" . $value1['id'],
                'name' => $value1['name']
            ];
        }

        // Labels
        $id = rand(0, 4548575451);


        $view = view('App.Actions.Fields.Input', [
            'form' => $form,
            'id' => $id,
            'label' => "Select Board",
            'labelId' => "id",
            'multiple' => false
        ])->render();
        $views[] = $view;

        $scripts[] = view('App.Actions.Fields.Script', [
            'form' => $form,
            'id' => $id,
            'labelId' => "id",
            'labelName' => "Select Board",
        ])->render();

        $view = Helpers::rap_with_form($views, $data);


        return [
            'view' => $view,
            'script' => $scripts,
            'message' => Helpers::translate('Connected With Data Fields'),
            'status' => 200,
        ];

    }

    public function delete_board_post($accountId, $mainData, $data)
    {
        $this->account = Account::find($accountId);
        $this->mainClass = new Trello;
        $this->userId = $this->mainClass->getUserId();

        $this->access_token = $this->mainClass->getToken($accountId);
        if (isset($mainData['id'])) {
            $params = [
                'id' => Helpers::stringorsingle($mainData['id']),
            ];
            $this->mainClass->deleteBoard($this->access_token, $params);

            return json_encode([
                'status' => 200,
                'message' => Helpers::translate('Successfully Applied First Nit')
            ]);
        } else {
            return json_encode([
                'status' => 400,
                'message' => Helpers::translate('Failed to Process your request.')
            ]);

        }
    }

    // Create List
    public function create_list($data)
    {

        // Labels
        $actionAccount = $data['action']['account_id'];
        $drive = new Trello;
        $accessToken = $drive->getToken($actionAccount);
        $userId = $drive->getUserId($actionAccount);
        $organizations = $drive->listBoards($accessToken, ['memberId' => $drive->getUserId()]);
        $form = [];

        foreach ($organizations as $key1 => $value1) {
            $form['Custom']['string'][] = [
                'id' => "idBoard/24110/" . $value1['id'],
                'name' => $value1['name']
            ];
        }

        // Labels
        $id = rand(0, 4548575451);


        $view = view('App.Actions.Fields.Input', [
            'form' => $form,
            'id' => $id,
            'label' => "Select Board",
            'labelId' => "idBoard",
            'multiple' => false
        ])->render();
        $views[] = $view;

        $scripts[] = view('App.Actions.Fields.Script', [
            'form' => $form,
            'id' => $id,
            'labelId' => "idBoard",
            'labelName' => "Select Board",
        ])->render();


        $form = [];
        $form['Custom']['custom'][] = [
            'id' => 'custom',
            'name' => Helpers::translate('Add Custom')
        ];
        $dataV = json_decode(json_encode($data, true), true);
        $manager = new Manager;
        $api_fields = $manager->getTriggerValue($dataV);
        if (isset($api_fields['string'])) {
            foreach ($api_fields['string'] as $key1 => $value1) {
                $form['Api']['api'][] = [
                    'id' => "name/24110/" . $key1,
                    'name' => $key1
                ];
            }
        }
        // Labels
        $id = rand(0, 4548575451);


        $view = view('App.Actions.Fields.Input', [
            'form' => $form,
            'id' => $id,
            'label' => "Name",
            'labelId' => "name",
        ])->render();
        $views[] = $view;

        $scripts[] = view('App.Actions.Fields.Script', [
            'form' => $form,
            'id' => $id,
            'labelId' => "name",
            'labelName' => "Name",
        ])->render();


        $form = [];
        $id = rand(0, 4548575451);

        $form['Custom']['string'][] = [
            'id' => "pos/24110/top",
            'name' => 'Top'
        ];
        $form['Custom']['string'][] = [
            'id' => "pos/24110/bottom",
            'name' => 'Bottom'
        ];


        $view = view('App.Actions.Fields.Input', [
            'form' => $form,
            'id' => $id,
            'label' => "Position",
            'labelId' => "pos",
            'multiple' => false
        ])->render();
        $views[] = $view;


        $view = Helpers::rap_with_form($views, $data);


        return [
            'view' => $view,
            'script' => $scripts,
            'message' => Helpers::translate('Connected With Data Fields'),
            'status' => 200,
        ];

    }

    public function create_list_post($accountId, $mainData, $data)
    {
        $this->account = Account::find($accountId);
        $this->mainClass = new Trello;
        $this->userId = $this->mainClass->getUserId();

        $this->access_token = $this->mainClass->getToken($accountId);
        if (isset($mainData['idBoard']) && isset($mainData['name'])) {
            $params = [
                'name' => Helpers::stringorexplode($mainData['name']),
                'idBoard' => Helpers::stringorsingle($mainData['idBoard']),
                'pos' => Helpers::stringorsingle($mainData['pos']),
            ];
            $this->mainClass->createList($this->access_token, $params);

            return json_encode([
                'status' => 200,
                'message' => Helpers::translate('Successfully Applied First Nit')
            ]);
        } else {
            return json_encode([
                'status' => 400,
                'message' => Helpers::translate('Failed to Process your request.')
            ]);

        }
    }


    // Create List
    public function create_card($data)
    {

        // Labels
        $actionAccount = $data['action']['account_id'];
        $drive = new Trello;
        $accessToken = $drive->getToken($actionAccount);
        $userId = $drive->getUserId($actionAccount);
        $organizations = $drive->listBoards($accessToken, ['memberId' => $drive->getUserId()]);
        $lists = $drive->listLists($accessToken, ['boardId' => $organizations[0]['id']]);
        $form = [];

        foreach ($organizations as $key1 => $value1) {
            $form['Custom']['string'][] = [
                'id' => "idBoard/24110/" . $value1['id'],
                'name' => $value1['name']
            ];
        }

        // Labels
        $id = rand(0, 4548575451);


        $view = view('App.Actions.Fields.Input', [
            'form' => $form,
            'id' => $id,
            'label' => "Select Board",
            'labelId' => "idBoard",
            'multiple' => false,
            'required' => true,
            'dataLoad' => 'sheetId2250sssd',
            'formName' => 'action_suffer',
            'dataAction' => json_encode([
                'AppId' => 'Trello',
                'Func' => 'UpdateListIds',
                'Mode' => 'Actions',
                'AccountId' => $actionAccount
            ])
        ])->render();

        $views[] = $view;

        $form = [];

        foreach ($lists as $key1 => $value1) {
            $form['Custom']['string'][] = [
                'id' => "idList/24110/" . $value1['id'],
                'name' => $value1['name']
            ];
        }

        // Labels
        $id = rand(0, 4548575451);


        $view = view('App.Actions.Fields.Input', [
            'form' => $form,
            'id' => $id,
            'label' => "Select List",
            'labelId' => "idList",
            'multiple' => false,
            'required' => true,
            'acceptDataLoad' => 'sheetId2250sssd',
        ])->render();
        $views[] = $view;

        $scripts[] = view('App.Actions.Fields.Script', [
            'form' => $form,
            'id' => $id,
            'labelId' => "idList",
            'labelName' => "Select List",
        ])->render();


        $form['Custom']['custom'][] = [
            'id' => 'custom',
            'name' => Helpers::translate('Add Custom')
        ];
        $dataV = json_decode(json_encode($data, true), true);
        $manager = new Manager;

        $api_fields = $manager->getTriggerValue($dataV);

        $form = [];

        if (isset($api_fields['string'])) {
            foreach ($api_fields['string'] as $key1 => $value1) {
                $form['Api']['api'][] = [
                    'id' => "name/24110/" . $key1,
                    'name' => $key1
                ];
            }
        }

        // Labels
        $id = rand(0, 4548575451);


        $view = view('App.Actions.Fields.Input', [
            'form' => $form,
            'id' => $id,
            'label' => "Name",
            'labelId' => "name",
        ])->render();
        $views[] = $view;

        $scripts[] = view('App.Actions.Fields.Script', [
            'form' => $form,
            'id' => $id,
            'labelId' => "name",
            'labelName' => "Name",
        ])->render();


        $form = [];

        if (isset($api_fields['string'])) {
            foreach ($api_fields['string'] as $key1 => $value1) {
                $form['Api']['api'][] = [
                    'id' => "desc/24110/" . $key1,
                    'name' => $key1
                ];
            }
        }

        // Labels
        $id = rand(0, 4548575451);


        $view = view('App.Actions.Fields.Input', [
            'form' => $form,
            'id' => $id,
            'label' => "Description",
            'labelId' => "desc",
        ])->render();
        $views[] = $view;

        $scripts[] = view('App.Actions.Fields.Script', [
            'form' => $form,
            'id' => $id,
            'labelId' => "desc",
            'labelName' => "Description",
        ])->render();

        $form = [];

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
            'label' => "Start (Date)",
            'labelId' => "start",
        ])->render();
        $views[] = $view;

        $scripts[] = view('App.Actions.Fields.Script', [
            'form' => $form,
            'id' => $id,
            'labelId' => "start",
            'labelName' => "Start (Date)",
        ])->render();


        $form = [];

        if (isset($api_fields['string'])) {
            foreach ($api_fields['string'] as $key1 => $value1) {
                $form['Api']['api'][] = [
                    'id' => "locationName/24110/" . $key1,
                    'name' => $key1
                ];
            }
        }

        // Labels
        $id = rand(0, 4548575451);


        $view = view('App.Actions.Fields.Input', [
            'form' => $form,
            'id' => $id,
            'label' => "Location Name",
            'labelId' => "locationName",
        ])->render();
        $views[] = $view;

        $scripts[] = view('App.Actions.Fields.Script', [
            'form' => $form,
            'id' => $id,
            'labelId' => "locationName",
            'labelName' => "Location Name",
        ])->render();

        $form = [];

        if (isset($api_fields['string'])) {
            foreach ($api_fields['string'] as $key1 => $value1) {
                $form['Api']['api'][] = [
                    'id' => "address/24110/" . $key1,
                    'name' => $key1
                ];
            }
        }

        // Labels
        $id = rand(0, 4548575451);


        $view = view('App.Actions.Fields.Input', [
            'form' => $form,
            'id' => $id,
            'label' => "Address",
            'labelId' => "address",
        ])->render();
        $views[] = $view;

        $scripts[] = view('App.Actions.Fields.Script', [
            'form' => $form,
            'id' => $id,
            'labelId' => "address",
            'labelName' => "Address",
        ])->render();


        $form = [];

        if (isset($api_fields['string'])) {
            foreach ($api_fields['string'] as $key1 => $value1) {
                $form['Api']['api'][] = [
                    'id' => "due/24110/" . $key1,
                    'name' => $key1
                ];
            }
        }

        // Labels
        $id = rand(0, 4548575451);


        $view = view('App.Actions.Fields.Input', [
            'form' => $form,
            'id' => $id,
            'label' => "Due (Date)",
            'labelId' => "due",
        ])->render();
        $views[] = $view;

        $scripts[] = view('App.Actions.Fields.Script', [
            'form' => $form,
            'id' => $id,
            'labelId' => "due",
            'labelName' => "Due (Date)",
        ])->render();


        $view = Helpers::rap_with_form($views, $data);


        return [
            'view' => $view,
            'script' => $scripts,
            'message' => Helpers::translate('Connected With Data Fields'),
            'status' => 200,
        ];

    }

    public function create_card_post($accountId, $mainData, $data)
    {
        $this->account = Account::find($accountId);
        $this->mainClass = new Trello;
        $this->userId = $this->mainClass->getUserId();

        $this->access_token = $this->mainClass->getToken($accountId);
        if (isset($mainData['idBoard']) && isset($mainData['name']) && isset($mainData['idList'])) {
            $params = [
                'name' => Helpers::stringorexplode($mainData['name']),
                'desc' => Helpers::stringorexplode($mainData['desc']),
                'locationName' => Helpers::stringorexplode($mainData['locationName'] ?? null),
                'address' => Helpers::stringorexplode($mainData['address'] ?? null),
                'start' => Helpers::stringorsingle($mainData['start'] ?? null),
                'due' => Helpers::stringorsingle($mainData['due'] ?? null),
                'idBoard' => Helpers::stringorsingle($mainData['idBoard']),
                'idList' => Helpers::stringorsingle($mainData['idList']),
            ];
            $this->mainClass->createCard($this->access_token, $params);

            return json_encode([
                'status' => 200,
                'message' => Helpers::translate('Successfully Applied First Nit')
            ]);
        } else {
            return json_encode([
                'status' => 400,
                'message' => Helpers::translate('Failed to Process your request.')
            ]);

        }
    }


    // Add Label to card List
    public function add_label_to_card($data)
    {

        // Labels
        $actionAccount = $data['action']['account_id'];
        $drive = new Trello;
        $accessToken = $drive->getToken($actionAccount);
        $userId = $drive->getUserId($actionAccount);
        $organizations = $drive->listBoards($accessToken, ['memberId' => $drive->getUserId()]);
        $lists = $drive->listCardsByBoards($accessToken, ['boardId' => $organizations[0]['id']]);
        $form = [];

        foreach ($organizations as $key1 => $value1) {
            $form['Custom']['string'][] = [
                'id' => "idBoard/24110/" . $value1['id'],
                'name' => $value1['name']
            ];
        }

        // Labels
        $id = rand(0, 4548575451);


        $view = view('App.Actions.Fields.Input', [
            'form' => $form,
            'id' => $id,
            'label' => "Select Board",
            'labelId' => "idBoard",
            'multiple' => false,
            'required' => true,
            'dataLoad' => 'sheetId2250sssd',
            'formName' => 'action_suffer',
            'dataAction' => json_encode([
                'AppId' => 'Trello',
                'Func' => 'UpdateCardIds',
                'Mode' => 'Actions',
                'AccountId' => $actionAccount
            ])
        ])->render();

        $views[] = $view;

        $form = [];

        foreach ($lists as $key1 => $value1) {
            $form['Custom']['string'][] = [
                'id' => "idCard/24110/" . $value1['id'],
                'name' => $value1['name']
            ];
        }

        // Labels
        $id = rand(0, 4548575451);


        $view = view('App.Actions.Fields.Input', [
            'form' => $form,
            'id' => $id,
            'label' => "Select Card",
            'labelId' => "idCard",
            'multiple' => false,
            'required' => true,
            'acceptDataLoad' => 'sheetId2250sssd',
        ])->render();
        $views[] = $view;

        $scripts[] = view('App.Actions.Fields.Script', [
            'form' => $form,
            'id' => $id,
            'labelId' => "idCard",
            'labelName' => "Select Card",
        ])->render();


        $dataV = json_decode(json_encode($data, true), true);
        $manager = new Manager;

        $api_fields = $manager->getTriggerValue($dataV);

        $form = [];
        $labels = $this->mainClass->getLabels($accessToken, ['boardId' => $organizations[0]['id']]);
        foreach ($labels as $key1 => $value1) {
            $form['Custom']['string'][] = [
                'id' => "labelId/24110/" . $value1['id'],
                'name' => $value1['name'] . " " . $value1['color']
            ];
        }
        if (isset($api_fields['string'])) {
            foreach ($api_fields['string'] as $key1 => $value1) {
                $form['Api']['api'][] = [
                    'id' => "labelId/24110/" . $key1,
                    'name' => $key1
                ];
            }
        }

        // Labels
        $id = rand(0, 4548575451);


        $view = view('App.Actions.Fields.Input', [
            'form' => $form,
            'id' => $id,
            'label' => "Label Id",
            'labelId' => "labelId",
        ])->render();
        $views[] = $view;

        $scripts[] = view('App.Actions.Fields.Script', [
            'form' => $form,
            'id' => $id,
            'labelId' => "labelId",
            'labelName' => "Label Id",
        ])->render();


        $view = Helpers::rap_with_form($views, $data);


        return [
            'view' => $view,
            'script' => $scripts,
            'message' => Helpers::translate('Connected With Data Fields'),
            'status' => 200,
        ];

    }

    public function add_label_to_card_post($accountId, $mainData, $data)
    {
        $this->account = Account::find($accountId);
        $this->mainClass = new Trello;
        $this->userId = $this->mainClass->getUserId();

        $this->access_token = $this->mainClass->getToken($accountId);
        if (isset($mainData['idBoard']) && isset($mainData['idCard']) && isset($mainData['labelId'])) {
            foreach ($mainData['labelId'] as $labelId) {
                $params = [
                    'idBoard' => Helpers::stringorsingle($mainData['idBoard']),
                    'id' => Helpers::stringorsingle($mainData['idCard']),
                    'labelId' => Helpers::stringorsingle($labelId),
                ];
                $this->mainClass->addLabelToCard($this->access_token, $params);
            }


            return json_encode([
                'status' => 200,
                'message' => Helpers::translate('Successfully Applied First Nit')
            ]);
        } else {
            return json_encode([
                'status' => 400,
                'message' => Helpers::translate('Failed to Process your request.')
            ]);

        }
    }

    // Archive Card
    public function archive_card($data)
    {

        // Labels
        $actionAccount = $data['action']['account_id'];
        $drive = new Trello;
        $accessToken = $drive->getToken($actionAccount);
        $userId = $drive->getUserId($actionAccount);
        $organizations = $drive->listBoards($accessToken, ['memberId' => $drive->getUserId()]);
        $lists = $drive->listCardsByBoards($accessToken, ['boardId' => $organizations[0]['id']]);
        $form = [];

        foreach ($organizations as $key1 => $value1) {
            $form['Custom']['string'][] = [
                'id' => "idBoard/24110/" . $value1['id'],
                'name' => $value1['name']
            ];
        }

        // Labels
        $id = rand(0, 4548575451);


        $view = view('App.Actions.Fields.Input', [
            'form' => $form,
            'id' => $id,
            'label' => "Select Board",
            'labelId' => "idBoard",
            'multiple' => false,
            'required' => true,
            'dataLoad' => 'sheetId2250sssds',
            'formName' => 'action_suffer',
            'dataAction' => json_encode([
                'AppId' => 'Trello',
                'Func' => 'UpdateCardIds',
                'Mode' => 'Actions',
                'AccountId' => $actionAccount
            ])
        ])->render();

        $views[] = $view;

        $form = [];

        $dataV = json_decode(json_encode($data, true), true);
        $manager = new Manager;

        $api_fields = $manager->getTriggerValue($dataV);


        foreach ($lists as $key1 => $value1) {
            $form['Custom']['string'][] = [
                'id' => "id/24110/" . $value1['id'],
                'name' => $value1['name']
            ];
        }
        if (isset($api_fields['string'])) {
            foreach ($api_fields['string'] as $key1 => $value1) {
                $form['Api']['api'][] = [
                    'id' => "id/24110/" . $key1,
                    'name' => $key1
                ];
            }
        }
        // Labels
        $id = rand(0, 4548575451);


        $view = view('App.Actions.Fields.Input', [
            'form' => $form,
            'id' => $id,
            'label' => "Card Id",
            'labelId' => "id",
            'multiple' => false,
            'required' => true,
            'acceptDataLoad' => 'sheetId2250sssds',
        ])->render();
        $views[] = $view;

        $scripts[] = view('App.Actions.Fields.Script', [
            'form' => $form,
            'id' => $id,
            'labelId' => "id",
            'labelName' => "Card Id",
        ])->render();


        $view = Helpers::rap_with_form($views, $data);


        return [
            'view' => $view,
            'script' => $scripts,
            'message' => Helpers::translate('Connected With Data Fields'),
            'status' => 200,
        ];

    }

    public function archive_card_post($accountId, $mainData, $data)
    {
        $this->account = Account::find($accountId);
        $this->mainClass = new Trello;
        $this->userId = $this->mainClass->getUserId();

        $this->access_token = $this->mainClass->getToken($accountId);
        if (isset($mainData['id'])) {
            $params = [
                'id' => Helpers::stringorsingle($mainData['id']),
            ];
            $this->mainClass->archiveCard($this->access_token, $params);

            return json_encode([
                'status' => 200,
                'message' => Helpers::translate('Successfully Applied First Nit')
            ]);
        } else {
            return json_encode([
                'status' => 400,
                'message' => Helpers::translate('Failed to Process your request.')
            ]);

        }
    }


    // Create Checklist Item On Card
    public function create_checklist_item_in_card($data)
    {

        // Labels
        $actionAccount = $data['action']['account_id'];
        $drive = new Trello;
        $accessToken = $drive->getToken($actionAccount);
        $userId = $drive->getUserId($actionAccount);
        $organizations = $drive->listBoards($accessToken, ['memberId' => $drive->getUserId()]);
        $lists = $drive->listCardsByBoards($accessToken, ['boardId' => $organizations[0]['id']]);
        $form = [];

        foreach ($organizations as $key1 => $value1) {
            $form['Custom']['string'][] = [
                'id' => "idBoard/24110/" . $value1['id'],
                'name' => $value1['name']
            ];
        }

        // Labels
        $id = rand(0, 4548575451);


        $view = view('App.Actions.Fields.Input', [
            'form' => $form,
            'id' => $id,
            'label' => "Select Board",
            'labelId' => "idBoard",
            'multiple' => false,
            'required' => true,
            'dataLoad' => 'sheetId2250sssd',
            'formName' => 'action_suffer',
            'dataAction' => json_encode([
                'AppId' => 'Trello',
                'Func' => 'UpdateCardIds',
                'Mode' => 'Actions',
                'AccountId' => $actionAccount
            ])
        ])->render();

        $views[] = $view;

        $form = [];

        $dataV = json_decode(json_encode($data, true), true);
        $manager = new Manager;

        $api_fields = $manager->getTriggerValue($dataV);


        foreach ($lists as $key1 => $value1) {
            $form['Custom']['string'][] = [
                'id' => "idList/24110/" . $value1['id'],
                'name' => $value1['name']
            ];
        }

        // Labels
        $id = rand(0, 4548575451);


        $view = view('App.Actions.Fields.Input', [
            'form' => $form,
            'id' => $id,
            'label' => "Card Id",
            'labelId' => "idList",
            'multiple' => false,
            'required' => true,
            'acceptDataLoad' => 'sheetId2250sssd',
        ])->render();
        $views[] = $view;

        $scripts[] = view('App.Actions.Fields.Script', [
            'form' => $form,
            'id' => $id,
            'labelId' => "idList",
            'labelName' => "Card Id",
        ])->render();

        $form = [];
        $form['Custom']['custom'][] = [
            'id' => 'custom',
            'name' => Helpers::translate('Add Custom')
        ];
        $dataV = json_decode(json_encode($data, true), true);
        $manager = new Manager;
        $api_fields = $manager->getTriggerValue($dataV);
        if (isset($api_fields['string'])) {
            foreach ($api_fields['string'] as $key1 => $value1) {
                $form['Api']['api'][] = [
                    'id' => "name/24110/" . $key1,
                    'name' => $key1
                ];
            }
        }
        // Labels
        $id = rand(0, 4548575451);


        $view = view('App.Actions.Fields.Input', [
            'form' => $form,
            'id' => $id,
            'label' => "Name",
            'labelId' => "name",
        ])->render();
        $views[] = $view;

        $scripts[] = view('App.Actions.Fields.Script', [
            'form' => $form,
            'id' => $id,
            'labelId' => "name",
            'labelName' => "Name",
        ])->render();


        $form = [];
        $id = rand(0, 4548575451);

        $form['Custom']['string'][] = [
            'id' => "pos/24110/top",
            'name' => 'Top'
        ];
        $form['Custom']['string'][] = [
            'id' => "pos/24110/bottom",
            'name' => 'Bottom'
        ];


        $view = view('App.Actions.Fields.Input', [
            'form' => $form,
            'id' => $id,
            'label' => "Position",
            'labelId' => "pos",
            'multiple' => false
        ])->render();
        $views[] = $view;
        $view = Helpers::rap_with_form($views, $data);


        return [
            'view' => $view,
            'script' => $scripts,
            'message' => Helpers::translate('Connected With Data Fields'),
            'status' => 200,
        ];

    }

    public function create_checklist_item_in_card_post($accountId, $mainData, $data)
    {
        $this->account = Account::find($accountId);
        $this->mainClass = new Trello;
        $this->userId = $this->mainClass->getUserId();

        $this->access_token = $this->mainClass->getToken($accountId);
        if (isset($mainData['name'])) {
            $params = [
                'id' => Helpers::stringorsingle($mainData['idList']),
                'idBoard' => Helpers::stringorsingle($mainData['idBoard']),
                'pos' => Helpers::stringorsingle($mainData['pos']),
                'name' => Helpers::stringorexplode($mainData['name']),
            ];
            $this->mainClass->createCheckListItemOnCard($this->access_token, $params);

            return json_encode([
                'status' => 200,
                'message' => Helpers::translate('Successfully Applied First Nit')
            ]);
        } else {
            return json_encode([
                'status' => 400,
                'message' => Helpers::translate('Failed to Process your request.')
            ]);

        }
    }


    // Create Label
    public function create_label($data)
    {

        // Labels
        $actionAccount = $data['action']['account_id'];
        $drive = new Trello;
        $accessToken = $drive->getToken($actionAccount);
        $userId = $drive->getUserId($actionAccount);
        $organizations = $drive->listBoards($accessToken, ['memberId' => $drive->getUserId()]);
        $form = [];

        foreach ($organizations as $key1 => $value1) {
            $form['Custom']['string'][] = [
                'id' => "idBoard/24110/" . $value1['id'],
                'name' => $value1['name']
            ];
        }

        // Labels
        $id = rand(0, 4548575451);


        $view = view('App.Actions.Fields.Input', [
            'form' => $form,
            'id' => $id,
            'label' => "Select Board",
            'labelId' => "idBoard",
            'multiple' => false,
            'required' => true,
        ])->render();

        $views[] = $view;

        $form = [];

        $dataV = json_decode(json_encode($data, true), true);
        $manager = new Manager;

        $api_fields = $manager->getTriggerValue($dataV);


        if (isset($api_fields['string'])) {
            foreach ($api_fields['string'] as $key1 => $value1) {
                $form['Api']['api'][] = [
                    'id' => "name/24110/" . $key1,
                    'name' => $key1
                ];
            }
        }
        // Labels
        $id = rand(0, 4548575451);


        $view = view('App.Actions.Fields.Input', [
            'form' => $form,
            'id' => $id,
            'label' => "Name",
            'labelId' => "name",
            'required' => true,
        ])->render();
        $views[] = $view;

        $scripts[] = view('App.Actions.Fields.Script', [
            'form' => $form,
            'id' => $id,
            'labelId' => "name",
            'labelName' => "Name",
        ])->render();

        $form = [];


        foreach ($this->mainClass->labelColors() as $key1 => $value1) {
            $form['Custom']['string'][] = [
                'id' => "color/24110/" . $value1,
                'name' => $value1
            ];
        }
        // Labels
        $id = rand(0, 4548575451);


        $view = view('App.Actions.Fields.Input', [
            'form' => $form,
            'id' => $id,
            'label' => "Color",
            'labelId' => "color",
            'required' => true,
        ])->render();
        $views[] = $view;

        $scripts[] = view('App.Actions.Fields.Script', [
            'form' => $form,
            'id' => $id,
            'labelId' => "color",
            'labelName' => "Color",
        ])->render();


        $view = Helpers::rap_with_form($views, $data);


        return [
            'view' => $view,
            'script' => $scripts,
            'message' => Helpers::translate('Connected With Data Fields'),
            'status' => 200,
        ];

    }

    public function create_label_post($accountId, $mainData, $data)
    {
        $this->account = Account::find($accountId);
        $this->mainClass = new Trello;
        $this->userId = $this->mainClass->getUserId();
        $this->access_token = $this->mainClass->getToken($accountId);
        if (isset($mainData['name'])) {
            $params = [
                'name' => Helpers::stringorsingle($mainData['name']),
                'idBoard' => Helpers::stringorsingle($mainData['idBoard']),
                'color' => Helpers::stringorsingle($mainData['color']),
            ];
            $this->mainClass->createLabel($this->access_token, $params);

            return json_encode([
                'status' => 200,
                'message' => Helpers::translate('Successfully Applied First Nit')
            ]);
        } else {
            return json_encode([
                'status' => 400,
                'message' => Helpers::translate('Failed to Process your request.')
            ]);

        }
    }


    // Archive Card
    public function add_attachment_to_card($data)
    {

        // Labels
        $actionAccount = $data['action']['account_id'];
        $drive = new Trello;
        $accessToken = $drive->getToken($actionAccount);
        $userId = $drive->getUserId($actionAccount);
        $organizations = $drive->listBoards($accessToken, ['memberId' => $drive->getUserId()]);
        $lists = $drive->listCardsByBoards($accessToken, ['boardId' => $organizations[0]['id']]);
        $form = [];

        foreach ($organizations as $key1 => $value1) {
            $form['Custom']['string'][] = [
                'id' => "idBoard/24110/" . $value1['id'],
                'name' => $value1['name']
            ];
        }

        // Labels
        $id = rand(0, 4548575451);


        $view = view('App.Actions.Fields.Input', [
            'form' => $form,
            'id' => $id,
            'label' => "Select Board",
            'labelId' => "idBoard",
            'multiple' => false,
            'required' => true,
            'dataLoad' => 'sheetId2250sssds',
            'formName' => 'action_suffer',
            'dataAction' => json_encode([
                'AppId' => 'Trello',
                'Func' => 'UpdateCardIds',
                'Mode' => 'Actions',
                'AccountId' => $actionAccount
            ])
        ])->render();

        $views[] = $view;

        $form = [];

        $dataV = json_decode(json_encode($data, true), true);
        $manager = new Manager;

        $api_fields = $manager->getTriggerValue($dataV);


        foreach ($lists as $key1 => $value1) {
            $form['Custom']['string'][] = [
                'id' => "id/24110/" . $value1['id'],
                'name' => $value1['name']
            ];
        }
        if (isset($api_fields['string'])) {
            foreach ($api_fields['string'] as $key1 => $value1) {
                $form['Api']['api'][] = [
                    'id' => "id/24110/" . $key1,
                    'name' => $key1
                ];
            }
        }
        // Labels
        $id = rand(0, 4548575451);


        $view = view('App.Actions.Fields.Input', [
            'form' => $form,
            'id' => $id,
            'label' => "Card Id",
            'labelId' => "id",
            'multiple' => false,
            'required' => true,
            'acceptDataLoad' => 'sheetId2250sssds',
        ])->render();
        $views[] = $view;

        $scripts[] = view('App.Actions.Fields.Script', [
            'form' => $form,
            'id' => $id,
            'labelId' => "id",
            'labelName' => "Card Id",
        ])->render();


        $form = [];

        if (isset($api_fields['string'])) {
            foreach ($api_fields['string'] as $key1 => $value1) {
                $form['Api']['api'][] = [
                    'id' => "name/24110/" . $key1,
                    'name' => $key1
                ];
            }
        }

        // Labels
        $id = rand(0, 4548575451);


        $view = view('App.Actions.Fields.Input', [
            'form' => $form,
            'id' => $id,
            'label' => "Name",
            'labelId' => "name",
        ])->render();
        $views[] = $view;

        $scripts[] = view('App.Actions.Fields.Script', [
            'form' => $form,
            'id' => $id,
            'labelId' => "name",
            'labelName' => "Name",
        ])->render();


        $form = [];

        if (isset($api_fields['string'])) {
            foreach ($api_fields['string'] as $key1 => $value1) {
                $form['Api']['api'][] = [
                    'id' => "mimeType/24110/" . $key1,
                    'name' => $key1
                ];
            }
        }

        // Labels
        $id = rand(0, 4548575451);


        $view = view('App.Actions.Fields.Input', [
            'form' => $form,
            'id' => $id,
            'label' => "Mime Type",
            'labelId' => "mimeType",
        ])->render();
        $views[] = $view;

        $scripts[] = view('App.Actions.Fields.Script', [
            'form' => $form,
            'id' => $id,
            'labelId' => "mimeType",
            'labelName' => "Mime Type",
        ])->render();

        $form = [];

        if (isset($api_fields['string'])) {
            foreach ($api_fields['string'] as $key1 => $value1) {
                $form['Api']['api'][] = [
                    'id' => "url/24110/" . $key1,
                    'name' => $key1
                ];
            }
        }

        // Labels
        $id = rand(0, 4548575451);


        $view = view('App.Actions.Fields.Input', [
            'form' => $form,
            'id' => $id,
            'label' => "Url Attachment",
            'labelId' => "url",
        ])->render();
        $views[] = $view;

        $scripts[] = view('App.Actions.Fields.Script', [
            'form' => $form,
            'id' => $id,
            'labelId' => "url",
            'labelName' => "Url Attachment",
        ])->render();

        $form = [];

        if (isset($api_fields['file'])) {
            foreach ($api_fields['file'] as $key1 => $value1) {
                $form['Api']['api'][] = [
                    'id' => "file/24110/" . $key1,
                    'name' => $key1
                ];
            }
        }

        // Labels
        $id = rand(0, 4548575451);


        $view = view('App.Actions.Fields.Input', [
            'form' => $form,
            'id' => $id,
            'label' => "File Attachment",
            'labelId' => "file",
        ])->render();
        $views[] = $view;

        $scripts[] = view('App.Actions.Fields.Script', [
            'form' => $form,
            'id' => $id,
            'labelId' => "file",
            'labelName' => "File Attachment",
        ])->render();


        $form = [];

        $form['Custom']['string'][] = [
            'id' => "setCover/24110/true",
            'name' => "True"
        ];
        $form['Custom']['string'][] = [
            'id' => "setCover/24110/false",
            'name' => "False"
        ];


        // Labels
        $id = rand(0, 4548575451);


        $view = view('App.Actions.Fields.Input', [
            'form' => $form,
            'id' => $id,
            'label' => "Set as Cover of the card",
            'labelId' => "setCover",
        ])->render();
        $views[] = $view;

        $scripts[] = view('App.Actions.Fields.Script', [
            'form' => $form,
            'id' => $id,
            'labelId' => "setCover",
            'labelName' => "Set as Cover of the card",
        ])->render();


        $view = Helpers::rap_with_form($views, $data);


        return [
            'view' => $view,
            'script' => $scripts,
            'message' => Helpers::translate('Connected With Data Fields'),
            'status' => 200,
        ];

    }

    public function add_attachment_to_card_post($accountId, $mainData, $data)
    {
        $this->account = Account::find($accountId);
        $this->mainClass = new Trello;
        $this->userId = $this->mainClass->getUserId();

        $this->access_token = $this->mainClass->getToken($accountId);
        // Pass Trigger Access Token
        $class = 'App\\Apps\\' . $data['trigger']['AppId'];
        $classData = new $class;
        $mainData['TriggerAccessToken'] = $classData->fileManagerInstance($data['trigger']['account_id']);
        if (isset($mainData['id'])) {
            $fileData = null;
            if (isset($mainData['file'])) {
                foreach ($mainData['file'] as $attachment) {
                    $class = 'App\\Apps\\' . $attachment['FileHandler'];
                    $fileManager = new $class;
                    $file = $fileManager->setFile($attachment, $mainData['TriggerAccessToken']);
                    $params = [
                        'id' => Helpers::stringorsingle($mainData['id']),
                        'url' => Helpers::stringorsingle($mainData['url'] ?? null),
                        'mimeType' => Helpers::stringorsingle($mainData['mimeType'] ?? null),
                        'setCover' => $mainData['setCover'] ? Helpers::stringorsingle($mainData['setCover']) : false,
                        'name' => Helpers::stringorexplode($mainData['name']),
                        'file' => $file,
                    ];
                    $this->mainClass->addAttachmentToCard($this->access_token, $params);
                }
            } else {

                $params = [
                    'id' => Helpers::stringorsingle($mainData['id']),
                    'url' => Helpers::stringorsingle($mainData['url'] ?? null),
                    'mimeType' => Helpers::stringorsingle($mainData['mimeType'] ?? null),
                    'setCover' => $mainData['setCover'] ? Helpers::stringorsingle($mainData['setCover']) : false,
                    'name' => Helpers::stringorexplode($mainData['name']),
                    'file' => null,
                ];
                $this->mainClass->addAttachmentToCard($this->access_token, $params);
            }

            return json_encode([
                'status' => 200,
                'message' => Helpers::translate('Successfully Applied First Nit')
            ]);
        } else {
            return json_encode([
                'status' => 400,
                'message' => Helpers::translate('Failed to Process your request.')
            ]);

        }
    }

// Update List Via AJAX
    public function UpdateListIds($Fid, $Fsheet, $data = null): array
    {
        $id = rand(0, 4548575451);
        $form = [];
        $idBoard = $Fsheet['string']['idBoard'][0];
        $this->mainClass = new Trello();
        $lists = $this->mainClass->listLists($this->access_token, ['boardId' => $idBoard]);

        foreach ($lists as $key1 => $value1) {
            $form['Custom']['string'][] = [
                'id' => "idList/24110/" . $value1['id'],
                'name' => $value1['name']
            ];
        }

        // Labels
        $view = view('App.Actions.Fields.Input', [
            'form' => $form,
            'id' => $id,
            'label' => "Select List",
            'labelId' => "idList",
            'multiple' => false,
            'required' => true,
            'acceptDataLoad' => 'sheetId2250sssd',
        ])->render();

        return [
            'view' => $view,
            'id' => $Fid
        ];
    }

    public function UpdateCardIds($Fid, $Fsheet, $data = null): array
    {
        $id = rand(0, 4548575451);
        $form = [];
        $idBoard = $Fsheet['string']['idBoard'][0];
        $this->mainClass = new Trello();
        $lists = $this->mainClass->listCardsByBoards($this->access_token, ['boardId' => $idBoard]);

        foreach ($lists as $key1 => $value1) {
            $form['Custom']['string'][] = [
                'id' => "idList/24110/" . $value1['id'],
                'name' => $value1['name']
            ];
        }

        // Labels
        $view = view('App.Actions.Fields.Input', [
            'form' => $form,
            'id' => $id,
            'label' => "Select Card",
            'labelId' => "idList",
            'multiple' => false,
            'required' => true,
            'acceptDataLoad' => 'sheetId2250sssd',
        ])->render();

        return [
            'view' => $view,
            'id' => $Fid
        ];
    }

}
