<?php

namespace App\Apps\Triggers;

use App\Apps\Trello;
use App\Http\Controllers\Api\Apps\Manager;
use App\Logic\Helpers;
use App\Models\Account;

class LinkedInTrigger
{
    private $account;
    /**
     * @var Trello
     */
    private $mainClass;
    /**
     * @var mixed
     */
    private $access_token;

    function __construct($accountId)
    {
        $this->account = Account::find($accountId);
        $this->mainClass = new Trello;
        $this->userId = $this->mainClass->getUserId();
        $this->access_token = $this->mainClass->getToken($accountId);
    }

    public function new_board()
    {
        return [
            'view' => Helpers::translate('Will be fired when a new board is created. Your account has been selected. Click \'Check Action\' Button to go on.'),
            'script' => '',
            'message' => Helpers::translate('Connected With Trello'),
            'status' => 200,
        ];
    }

    public function new_board_check($data = [])
    {
        $files = $this->mainClass->listBoards($this->access_token, ['memberId' =>$this->mainClass->getUserId()]);
        $dataString['string'] = $files[count($files) - 1];
        return $dataString;
    }


    public function new_board_changes($zapDatabase = [], $zapData = [])
    {
        $zapDatabase = json_decode($zapDatabase, true)['Files'];
        $zapData = json_decode($zapData, true);
        $data = Helpers::IllitarableArray($zapData['trigger']['Data']);
        $value = Helpers::evaluteData($data);
        $files = $this->mainClass->listBoards($this->access_token, ['memberId' =>$this->mainClass->getUserId()]);
        $oldFiles = [];
        $dataFiles = [];
        foreach ($files as $key => $file) {
            $dataFiles[$file['id']] = $file;
        }
        foreach ($zapDatabase as $key => $file) {
            $oldFiles[] = $file['id'];
        }
        $files = [];
        foreach ($dataFiles as $key => $latest) {
            if (!in_array($key, $oldFiles)) {
                $files[] = $latest;
            }
        }
        $dataString['string'] = $files;
        return $dataString;
    }

    public function new_board_update_database($data = null)
    {
        $files = $this->mainClass->listBoards($this->access_token, ['memberId' => $this->mainClass->getUserId()]);
        return ['Files' => $files];
    }

//    New Completed Task
    public function new_card(): array
    {
        $actionAccount = $this->account;

        $organizations = $this->mainClass->listBoards($this->access_token, ['memberId' => $this->mainClass->getUserId()]);
        $lists = $this->mainClass->listCardsByBoards($this->access_token, ['boardId' => $organizations[0]['id']]);
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
//            'dataLoad' => 'sheetId2250sssd',
//            'dataAction' => json_encode([
//                'AppId' => 'Trello',
//                'Func' => 'UpdateCardIds',
//                'Mode' => 'Triggers',
//                'AccountId' => $actionAccount['id']
//            ])
        ])->render();

        $views[] = $view;

        $form = [];


//
//        foreach ($lists as $key1 => $value1) {
//            $form['Custom']['string'][] = [
//                'id' => "idList/24110/" . $value1['id'],
//                'name' => $value1['name']
//            ];
//        }
//
//        // Labels
//        $id = rand(0, 4548575451);
//
//
//        $view = view('App.Actions.Fields.Input', [
//            'form' => $form,
//            'id' => $id,
//            'label' => "Card Id",
//            'labelId' => "idList",
//            'multiple' => false,
//            'required' => true,
//            'acceptDataLoad' => 'sheetId2250sssd',
//        ])->render();
//        $views[] = $view;




        $view = Helpers::rap_with_form($views, [], 'triggerForm');
        $scripts[] = view('App.Actions.Fields.Script', [
            'form' => $form,
            'id' => $id,
            'labelId' => 'cardId',
            'labelName' => Helpers::translate('Card Id'),
        ])->render();

        return [
            'view' => $view,
            'script' => $scripts,
            'message' => Helpers::translate('Connected With Tasks'),
            'status' => 200,
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


    public function new_card_check($data = []): array
    {
        $data = Helpers::IllitarableArray($data);
        $value = Helpers::evaluteData($data);
        $param = [
            'boardId' => $value['string']['idBoard'][0]
        ];
        $filesData = $this->mainClass->listCardsByBoards($this->access_token, $param);
        $dataString['string'] = $filesData[count($filesData) - 1];
        return $dataString;
    }


    public function new_card_changes($zapDatabase = [], $zapData = []): array
    {
        $zapDatabase = json_decode($zapDatabase, true)['Files'];
        $zapData = json_decode($zapData, true);
        $data = Helpers::IllitarableArray($zapData['trigger']['Data']);
        $value = Helpers::evaluteData($data);
        $param = [
            'boardId' => $value['string']['idBoard'][0]
        ];
        $files = $this->mainClass->listCardsByBoards($this->access_token, $param);

        $oldFiles = [];
        $dataFiles = [];
        foreach ($files as $key => $file) {
            $dataFiles[$file['id']] = $file;
        }
        foreach ($zapDatabase as $key => $file) {
            $oldFiles[] = $file['id'];
        }
        $files = [];
        foreach ($dataFiles as $key => $latest) {
            if (!in_array($key, $oldFiles)) {
                $files[] = $latest;
            }
        }
        $dataString['string'] = $files;
        return $dataString;
    }

    public function new_card_update_database($data = null): array
    {
        $data = Helpers::IllitarableArray($data);
        $value = Helpers::evaluteData($data);
        $param = [
            'boardId' => $value['string']['idBoard'][0]
        ];
        $files = $this->mainClass->listCardsByBoards($this->access_token, $param);

        return ['Files' => $files];
    }

//    New CheckList
    public function new_checklist(): array
    {
        $actionAccount = $this->account;

        $organizations = $this->mainClass->listBoards($this->access_token, ['memberId' => $this->mainClass->getUserId()]);
        $lists = $this->mainClass->listCardsByBoards($this->access_token, ['boardId' => $organizations[0]['id']]);
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





        $view = Helpers::rap_with_form($views, [], 'triggerForm');
        $scripts[] = view('App.Actions.Fields.Script', [
            'form' => $form,
            'id' => $id,
            'labelId' => 'cardId',
            'labelName' => Helpers::translate('Card Id'),
        ])->render();

        return [
            'view' => $view,
            'script' => $scripts,
            'message' => Helpers::translate('Connected With Trello'),
            'status' => 200,
        ];
    }


    public function new_checklist_check($data = []): array
    {
        $data = Helpers::IllitarableArray($data);
        $value = Helpers::evaluteData($data);
        $param = [
            'boardId' => $value['string']['idBoard'][0]
        ];
        $filesData = $this->mainClass->listLists($this->access_token, $param);
        $dataString['string'] = $filesData[count($filesData) - 1];
        return $dataString;
    }


    public function new_checklist_changes($zapDatabase = [], $zapData = []): array
    {
        $zapDatabase = json_decode($zapDatabase, true)['Files'];
        $zapData = json_decode($zapData, true);
        $data = Helpers::IllitarableArray($zapData['trigger']['Data']);
        $value = Helpers::evaluteData($data);
        $param = [
            'boardId' => $value['string']['idBoard'][0]
        ];
        $files = $this->mainClass->listLists($this->access_token, $param);

        $oldFiles = [];
        $dataFiles = [];
        foreach ($files as $key => $file) {
            $dataFiles[$file['id']] = $file;
        }
        foreach ($zapDatabase as $key => $file) {
            $oldFiles[] = $file['id'];
        }
        $files = [];
        foreach ($dataFiles as $key => $latest) {
            if (!in_array($key, $oldFiles)) {
                $files[] = $latest;
            }
        }
        $dataString['string'] = $files;
        return $dataString;
    }

    public function new_checklist_update_database($data = null): array
    {
        $data = Helpers::IllitarableArray($data);
        $value = Helpers::evaluteData($data);
        $param = [
            'boardId' => $value['string']['idBoard'][0]
        ];
        $files = $this->mainClass->listLists($this->access_token, $param);

        return ['Files' => $files];
    }


//    New Labels
    public function new_label(): array
    {
        $actionAccount = $this->account;

        $organizations = $this->mainClass->listBoards($this->access_token, ['memberId' => $this->mainClass->getUserId()]);
        $lists = $this->mainClass->listCardsByBoards($this->access_token, ['boardId' => $organizations[0]['id']]);
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


        $view = Helpers::rap_with_form($views, [], 'triggerForm');
        $scripts[] = view('App.Actions.Fields.Script', [
            'form' => $form,
            'id' => $id,
            'labelId' => 'cardId',
            'labelName' => Helpers::translate('Card Id'),
        ])->render();

        return [
            'view' => $view,
            'script' => $scripts,
            'message' => Helpers::translate('Connected With Trello'),
            'status' => 200,
        ];
    }


    public function new_label_check($data = []): array
    {
        $data = Helpers::IllitarableArray($data);
        $value = Helpers::evaluteData($data);
        $param = [
            'boardId' => $value['string']['idBoard'][0]
        ];
        $filesData = $this->mainClass->getLabels($this->access_token, $param);
        $dataString['string'] = $filesData[count($filesData) - 1];
        return $dataString;
    }


    public function new_label_changes($zapDatabase = [], $zapData = []): array
    {
        $zapDatabase = json_decode($zapDatabase, true)['Files'];
        $zapData = json_decode($zapData, true);
        $data = Helpers::IllitarableArray($zapData['trigger']['Data']);
        $value = Helpers::evaluteData($data);
        $param = [
            'boardId' => $value['string']['idBoard'][0]
        ];
        $files = $this->mainClass->getLabels($this->access_token, $param);

        $oldFiles = [];
        $dataFiles = [];
        foreach ($files as $key => $file) {
            $dataFiles[$file['id']] = $file;
        }
        foreach ($zapDatabase as $key => $file) {
            $oldFiles[] = $file['id'];
        }
        $files = [];
        foreach ($dataFiles as $key => $latest) {
            if (!in_array($key, $oldFiles)) {
                $files[] = $latest;
            }
        }
        $dataString['string'] = $files;
        return $dataString;
    }

    public function new_label_update_database($data = null): array
    {
        $data = Helpers::IllitarableArray($data);
        $value = Helpers::evaluteData($data);
        $param = [
            'boardId' => $value['string']['idBoard'][0]
        ];
        $files = $this->mainClass->getLabels($this->access_token, $param);

        return ['Files' => $files];
    }

//    New Lists
    public function new_list(): array
    {
        $actionAccount = $this->account;

        $organizations = $this->mainClass->listBoards($this->access_token, ['memberId' => $this->mainClass->getUserId()]);
        $lists = $this->mainClass->listCardsByBoards($this->access_token, ['boardId' => $organizations[0]['id']]);
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


        $view = Helpers::rap_with_form($views, [], 'triggerForm');
        $scripts[] = view('App.Actions.Fields.Script', [
            'form' => $form,
            'id' => $id,
            'labelId' => 'cardId',
            'labelName' => Helpers::translate('Card Id'),
        ])->render();

        return [
            'view' => $view,
            'script' => $scripts,
            'message' => Helpers::translate('Connected With Trello'),
            'status' => 200,
        ];
    }


    public function new_list_check($data = []): array
    {
        $data = Helpers::IllitarableArray($data);
        $value = Helpers::evaluteData($data);
        $param = [
            'boardId' => $value['string']['idBoard'][0]
        ];
        $filesData = $this->mainClass->listLists($this->access_token, $param);
        $dataString['string'] = $filesData[count($filesData) - 1];
        return $dataString;
    }


    public function new_list_changes($zapDatabase = [], $zapData = []): array
    {
        $zapDatabase = json_decode($zapDatabase, true)['Files'];
        $zapData = json_decode($zapData, true);
        $data = Helpers::IllitarableArray($zapData['trigger']['Data']);
        $value = Helpers::evaluteData($data);
        $param = [
            'boardId' => $value['string']['idBoard'][0]
        ];
        $files = $this->mainClass->listLists($this->access_token, $param);

        $oldFiles = [];
        $dataFiles = [];
        foreach ($files as $key => $file) {
            $dataFiles[$file['id']] = $file;
        }
        foreach ($zapDatabase as $key => $file) {
            $oldFiles[] = $file['id'];
        }
        $files = [];
        foreach ($dataFiles as $key => $latest) {
            if (!in_array($key, $oldFiles)) {
                $files[] = $latest;
            }
        }
        $dataString['string'] = $files;
        return $dataString;
    }

    public function new_list_update_database($data = null): array
    {
        $data = Helpers::IllitarableArray($data);
        $value = Helpers::evaluteData($data);
        $param = [
            'boardId' => $value['string']['idBoard'][0]
        ];
        $files = $this->mainClass->listLists($this->access_token, $param);

        return ['Files' => $files];
    }


//    New Members
    public function new_member(): array
    {
        $actionAccount = $this->account;

        $organizations = $this->mainClass->listBoards($this->access_token, ['memberId' => $this->mainClass->getUserId()]);
        $lists = $this->mainClass->listCardsByBoards($this->access_token, ['boardId' => $organizations[0]['id']]);
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


        $view = Helpers::rap_with_form($views, [], 'triggerForm');
        $scripts[] = view('App.Actions.Fields.Script', [
            'form' => $form,
            'id' => $id,
            'labelId' => 'cardId',
            'labelName' => Helpers::translate('Card Id'),
        ])->render();

        return [
            'view' => $view,
            'script' => $scripts,
            'message' => Helpers::translate('Connected With Trello'),
            'status' => 200,
        ];
    }


    public function new_member_check($data = []): array
    {
        $data = Helpers::IllitarableArray($data);
        $value = Helpers::evaluteData($data);
        $param = [
            'boardId' => $value['string']['idBoard'][0]
        ];
        $filesData = $this->mainClass->listMembers($this->access_token, $param);
        $dataString['string'] = $filesData[count($filesData) - 1];
        return $dataString;
    }


    public function new_member_changes($zapDatabase = [], $zapData = []): array
    {
        $zapDatabase = json_decode($zapDatabase, true)['Files'];
        $zapData = json_decode($zapData, true);
        $data = Helpers::IllitarableArray($zapData['trigger']['Data']);
        $value = Helpers::evaluteData($data);
        $param = [
            'boardId' => $value['string']['idBoard'][0]
        ];
        $files = $this->mainClass->listMembers($this->access_token, $param);

        $oldFiles = [];
        $dataFiles = [];
        foreach ($files as $key => $file) {
            $dataFiles[$file['id']] = $file;
        }
        foreach ($zapDatabase as $key => $file) {
            $oldFiles[] = $file['id'];
        }
        $files = [];
        foreach ($dataFiles as $key => $latest) {
            if (!in_array($key, $oldFiles)) {
                $files[] = $latest;
            }
        }
        $dataString['string'] = $files;
        return $dataString;
    }

    public function new_member_update_database($data = null): array
    {
        $data = Helpers::IllitarableArray($data);
        $value = Helpers::evaluteData($data);
        $param = [
            'boardId' => $value['string']['idBoard'][0]
        ];
        $files = $this->mainClass->listMembers($this->access_token, $param);

        return ['Files' => $files];
    }


    public function card_archived(): array
    {
        $actionAccount = $this->account;

        $organizations = $this->mainClass->listBoards($this->access_token, ['memberId' => $this->mainClass->getUserId()]);
        $lists = $this->mainClass->listCardsByBoards($this->access_token, ['boardId' => $organizations[0]['id']]);
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
//            'dataLoad' => 'sheetId2250sssd',
//            'dataAction' => json_encode([
//                'AppId' => 'Trello',
//                'Func' => 'UpdateCardIds',
//                'Mode' => 'Triggers',
//                'AccountId' => $actionAccount['id']
//            ])
        ])->render();

        $views[] = $view;

        $form = [];


//
//        foreach ($lists as $key1 => $value1) {
//            $form['Custom']['string'][] = [
//                'id' => "idList/24110/" . $value1['id'],
//                'name' => $value1['name']
//            ];
//        }
//
//        // Labels
//        $id = rand(0, 4548575451);
//
//
//        $view = view('App.Actions.Fields.Input', [
//            'form' => $form,
//            'id' => $id,
//            'label' => "Card Id",
//            'labelId' => "idList",
//            'multiple' => false,
//            'required' => true,
//            'acceptDataLoad' => 'sheetId2250sssd',
//        ])->render();
//        $views[] = $view;




        $view = Helpers::rap_with_form($views, [], 'triggerForm');
        $scripts[] = view('App.Actions.Fields.Script', [
            'form' => $form,
            'id' => $id,
            'labelId' => 'cardId',
            'labelName' => Helpers::translate('Card Id'),
        ])->render();

        return [
            'view' => $view,
            'script' => $scripts,
            'message' => Helpers::translate('Connected With Tasks'),
            'status' => 200,
        ];
    }
    public function card_archived_check($data = []): array
    {
        $data = Helpers::IllitarableArray($data);
        $value = Helpers::evaluteData($data);
        $param = [
            'boardId' => $value['string']['idBoard'][0]
        ];
        $filesData = $this->mainClass->listCardsByBoards($this->access_token, $param);
        $files = [];
        foreach($filesData as $file)
        {
            if ($file['closed'] === true)
            {
                $files[] = $file;
            }
        }
        $dataString['string'] = $filesData[count($files) - 1];
        return $dataString;
    }


    public function card_archived_changes($zapDatabase = [], $zapData = []): array
    {
        $zapDatabase = json_decode($zapDatabase, true)['Files'];
        $zapData = json_decode($zapData, true);
        $data = Helpers::IllitarableArray($zapData['trigger']['Data']);
        $value = Helpers::evaluteData($data);
        $param = [
            'boardId' => $value['string']['idBoard'][0]
        ];
        $filesData = $this->mainClass->listCardsByBoards($this->access_token, $param);
        $files = [];
        foreach($filesData as $file)
        {
            if ($file['closed'] === true)
            {
                $files[] = $file;
            }
        }
        $oldFiles = [];
        $dataFiles = [];
        foreach ($files as $key => $file) {
            $dataFiles[$file['id']] = $file;
        }
        foreach ($zapDatabase as $key => $file) {
            $oldFiles[] = $file['id'];
        }
        $files = [];
        foreach ($dataFiles as $key => $latest) {
            if (!in_array($key, $oldFiles)) {
                $files[] = $latest;
            }
        }
        $dataString['string'] = $files;
        return $dataString;
    }

    public function card_archived_update_database($data = null): array
    {
        $data = Helpers::IllitarableArray($data);
        $value = Helpers::evaluteData($data);
        $param = [
            'boardId' => $value['string']['idBoard'][0]
        ];
        $filesData = $this->mainClass->listCardsByBoards($this->access_token, $param);
        $files = [];
        foreach($filesData as $file)
        {
            if ($file['closed'] === true)
            {
                $files[] = $file;
            }
        }
        return ['Files' => $files];
    }



}
