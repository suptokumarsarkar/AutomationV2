<?php

namespace App\Apps;

use App\Apps\Triggers\TrelloTrigger;
use App\Logic\Helpers;
use App\Models\Account;
use App\Models\AppsData;

class Trello
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
        $gmail = AppsData::where("AppId", "GoogleTask")->first();
        if ($gmail) {
            $appInfo = json_decode($gmail->AppInfo, true);
            $this->client_id = $appInfo['api_key'];
            $this->client_secret = $appInfo['secret'];
        }
    }


    public function getTrigger(): array
    {
        return array(
            [
                'id' => 'new_board',
                'name' => Helpers::translate('New Board'),
                'description' => Helpers::translate('Triggers when a new board is added')
            ],
            [
                'id' => 'new_card',
                'name' => Helpers::translate('New Card'),
                'description' => Helpers::translate('Triggers when a new card is added')
            ],
            [
                'id' => 'card_archived',
                'name' => Helpers::translate('Card Archived'),
                'description' => Helpers::translate('Triggers when a card is archived in trello')
            ],
            [
                'id' => 'new_comment_in_card',
                'name' => Helpers::translate('New Comment in Card'),
                'description' => Helpers::translate('Triggers when a new comment comes into a card')
            ],
            [
                'id' => 'new_activity',
                'name' => Helpers::translate('New Activity'),
                'description' => Helpers::translate('Triggers when there is an activity in trello')
            ],
            [
                'id' => 'card_moved_to_list',
                'name' => Helpers::translate('Card Moved To List'),
                'description' => Helpers::translate('Triggers when a card is moved to list in trello, inside the same board')
            ],
            [
                'id' => 'card_due',
                'name' => Helpers::translate('Card Due'),
                'description' => Helpers::translate('Triggers at a specific time before a card is due')
            ],
            [
                'id' => 'card_updated',
                'name' => Helpers::translate('Card Updated'),
                'description' => Helpers::translate('Triggers when a card is updated in trello')
            ],
            [
                'id' => 'new_checklist',
                'name' => Helpers::translate('New Checklist'),
                'description' => Helpers::translate('Triggers when a new checklist is created in trello')
            ],
            [
                'id' => 'new_label',
                'name' => Helpers::translate('New Label'),
                'description' => Helpers::translate('Triggers when a new label is created')
            ],
            [
                'id' => 'new_label_added_to_card',
                'name' => Helpers::translate('New Label Added To Card'),
                'description' => Helpers::translate('Triggers when a new label is added to card')
            ],
            [
                'id' => 'new_list',
                'name' => Helpers::translate('New List'),
                'description' => Helpers::translate('Triggers when a new list is created in a board')
            ],
            [
                'id' => 'new_member',
                'name' => Helpers::translate('New Member In Board'),
                'description' => Helpers::translate('Triggers when a new member is added to a board')
            ],
            [
                'id' => 'new_notification',
                'name' => Helpers::translate('New Notification'),
                'description' => Helpers::translate('Triggers when a new notification comes in trello')
            ]
        );
    }

    public function getActions(): array
    {
        return array(
            [
                'id' => 'create_checklist_item_in_card',
                'name' => Helpers::translate('Create Checklist Item In Card'),
                'description' => Helpers::translate('Creates a new checklist item in card')
            ],
            [
                'id' => 'create_board',
                'name' => Helpers::translate('Create Board'),
                'description' => Helpers::translate('Creates a new board')
            ],
            [
                'id' => 'create_card',
                'name' => Helpers::translate('Create Card'),
                'description' => Helpers::translate('Creates a new card')
            ],
            [
                'id' => 'archive_card',
                'name' => Helpers::translate('Archive Card'),
                'description' => Helpers::translate('Archives a card')
            ],
            [
                'id' => 'add_attachment_to_card',
                'name' => Helpers::translate('Add Attachments To Card'),
                'description' => Helpers::translate('Adds one or more attachments to card')
            ],
            [
                'id' => 'add_label_to_card',
                'name' => Helpers::translate('Add Label To Card'),
                'description' => Helpers::translate('Adds Label to a specific card')
            ],
            [
                'id' => 'move_card',
                'name' => Helpers::translate('Move Card'),
                'description' => Helpers::translate('Moves a specific card to a board')
            ],
            [
                'id' => 'add_members_to_card',
                'name' => Helpers::translate('Add Members To Card'),
                'description' => Helpers::translate('Adds member(s) to a specific card')
            ],
            [
                'id' => 'update_card',
                'name' => Helpers::translate('Update Card'),
                'description' => Helpers::translate('Updates a specific card')
            ],
            [
                'id' => 'add_checklist_to_card',
                'name' => Helpers::translate('Add Checklist To Card'),
                'description' => Helpers::translate('Adds checklist to card')
            ],
            [
                'id' => 'close_board',
                'name' => Helpers::translate('Close Board'),
                'description' => Helpers::translate('Closes a board without permanently deleting')
            ],
            [
                'id' => 'write_comment',
                'name' => Helpers::translate('Write Comment'),
                'description' => Helpers::translate('Writes a comment into a specific card')
            ],
            [
                'id' => 'complete_checklist_item_in_card',
                'name' => Helpers::translate('Complete Checklist Item In Card'),
                'description' => Helpers::translate('Completes a checklist item in a specific card')
            ],
            [
                'id' => 'copy_board',
                'name' => Helpers::translate('Copy Board'),
                'description' => Helpers::translate('Copies an existing board')
            ],
            [
                'id' => 'delete_checklist_in_card',
                'name' => Helpers::translate('Delete Checklist In Card'),
                'description' => Helpers::translate('Deletes an checklist in card')
            ],
            [
                'id' => 'create_label',
                'name' => Helpers::translate('Create Label'),
                'description' => Helpers::translate('Creates a new label')
            ],
            [
                'id' => 'create_list',
                'name' => Helpers::translate('Create List'),
                'description' => Helpers::translate('Creates a new list')
            ],
            [
                'id' => 'remove_label_from_card',
                'name' => Helpers::translate('Remove Label From Card'),
                'description' => Helpers::translate('Removes an exising label from card')
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
        $trigger = new GoogleTaskTrigger($accountId);
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


    // Task List API
    public function getTaskLists($access_token)
    {
        $url = "https://tasks.googleapis.com/tasks/v1/users/@me/lists?access_token=" . $access_token;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        return json_decode($response, true)['items'];
    }

    public function createTaskList($access_token, $param)
    {
        $params = [
            'title' => $param['title'],
        ];
        $url = "https://tasks.googleapis.com/tasks/v1/users/@me/lists?access_token=" . $access_token;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Authorization: Bearer " . $access_token, 'Accept: application/json', 'Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params, true));
        $data = curl_exec($ch);

        return json_decode($data, true);
    }

    // Tasks API
    public function getTasks($access_token, $param)
    {
        $url = "https://tasks.googleapis.com/tasks/v1/lists/{$param['taskListId']}/tasks?showCompleted=1&showHidden=1&access_token=" . $access_token;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        return json_decode($response, true)['items'];
    }

    public function createTask($access_token, $param)
    {
        $params = [
            'title' => $param['title'],
            'notes' => $param['notes'],
            'due' => date("c", strtotime($param['due'])),
        ];
        $url = "https://tasks.googleapis.com/tasks/v1/lists/{$param['taskListId']}/tasks?access_token=" . $access_token;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Authorization: Bearer " . $access_token, 'Accept: application/json', 'Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params, true));
        $data = curl_exec($ch);

        return json_decode($data, true);
    }

    public function updateTask($access_token, $param)
    {
        $params = [
            'title' => $param['title'],
            'notes' => $param['notes'],
            'id' => $param['taskId'],
            'completed' => $param['completed'],
            'due' => date("c", strtotime($param['due'])),
        ];
        $url = "https://tasks.googleapis.com/tasks/v1/lists/{$param['taskListId']}/tasks/{$param['taskId']}?access_token=" . $access_token;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Authorization: Bearer " . $access_token, 'Accept: application/json', 'Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params, true));
        $data = curl_exec($ch);

        return json_decode($data, true);
    }

}
