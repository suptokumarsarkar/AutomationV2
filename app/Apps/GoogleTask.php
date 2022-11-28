<?php

namespace App\Apps;

use App\Apps\Triggers\GoogleTaskTrigger;
use App\Logic\Helpers;
use App\Models\Account;
use App\Models\AppsData;

class GoogleTask
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
            $this->client_id = $appInfo['client_id'];
            $this->client_secret = $appInfo['client_secret'];
        }
    }


    public function getTrigger(): array
    {
        return array(
            [
                'id' => 'new_task_list',
                'name' => Helpers::translate('New Task List'),
                'description' => Helpers::translate('Triggers when you create a new task list')
            ],
            [
                'id' => 'new_completed_task',
                'name' => Helpers::translate('New Completed Task'),
                'description' => Helpers::translate('Triggers when you mark a task as completed')
            ],
            [
                'id' => 'new_task',
                'name' => Helpers::translate('New Task'),
                'description' => Helpers::translate('Triggers when you create a new task')
            ]
        );
    }

    public function getActions(): array
    {
        return array(
            [
                'id' => 'create_task',
                'name' => Helpers::translate('Create Task'),
                'description' => Helpers::translate('Creates a new task')
            ],
            [
                'id' => 'create_task_list',
                'name' => Helpers::translate('Create Task List'),
                'description' => Helpers::translate('Creates a new task list')
            ],
            [
                'id' => 'update_task',
                'name' => Helpers::translate('Update Task'),
                'description' => Helpers::translate('Updates a task')
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
