<?php

namespace App\Apps;

use App\Apps\Triggers\TrelloTrigger;
use App\Logic\Helpers;
use App\Models\Account;
use App\Models\AppsData;

class LinkedIn
{

//    private $client_id = '68667692288-4bkagbc71fb2k0c9dr481ip9ib22iffv.apps.googleusercontent.com';
//    private $client_secret = "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";

    /**
     * @var mixed
     */
    public $client_id;
    /**
     * @var mixed
     */
    public $client_secret;
    public $dataOptionTrigger = false;

    public function __construct()
    {
        $gmail = AppsData::where("AppId", "LinkedIn")->first();
        if ($gmail) {
            $appInfo = json_decode($gmail->AppInfo, true);
            $this->client_id = $appInfo['api_key'];
            $this->client_secret = $appInfo['client_secret'];
        }
    }

    public function addAccount($data = [])
    {
        return json_encode($this->getAccessToken($data['token']['code']), true);
    }

    public function getProfile($data = [], $tokenGroup = [])
    {
        $access_token = $tokenGroup['access_token'];

        return json_encode($this->getMember($access_token), true);

    }


    public function getTrigger(): array
    {
        return array();
    }

    public function getActions(): array
    {
        return array(
            [
                'id' => 'create_share_update',
                'name' => Helpers::translate('Create Share Update'),
                'description' => Helpers::translate('Post a Content in your linkedIn Profile')
            ],
            [
                'id' => 'create_company_update',
                'name' => Helpers::translate('Create Company Update'),
                'description' => Helpers::translate('Update your page with new data')
            ],
        );
    }


    public function getToken($id)
    {
        $getProfile = Account::find($id);
        $token = json_decode($getProfile->token, true);
        return $token['access_token'];
    }

    public function getUserId($id = null)
    {
        if ($id == null) return 'me';
        $getProfile = Account::find($id);
        $token = json_decode($getProfile->data, true);
        return $token['id'];
    }

    public function getCheckupData($accountId, $labelId)
    {
        $getProfile = Account::find($accountId);
        $data = json_decode($getProfile->data, true);
        $access_token = $this->getToken($accountId);
        $trigger = new TrelloTrigger($accountId);
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


    // Default API
    public function getMember($access_token)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://api.linkedin.com/v2/me');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');


        $headers = array();
        $headers[] = "Authorization: Bearer $access_token";
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);
        return json_decode($response, true);

    }

    public function getAccessToken($code)
    {
        $params = [
            'grant_type' => 'authorization_code',
            'code' => $code,
            'client_id' => $this->client_id,
            'client_secret' => $this->client_secret,
            'redirect_uri' => 'http://localhost/saas/oauth/linkedin',
        ];

        $ch = curl_init();
        $url = 'https://www.linkedin.com/oauth/v2/accessToken?' . http_build_query($params);
        curl_setopt($ch, CURLOPT_URL, $url);
//        dd($params, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);

        $headers = array();
        $headers[] = 'Content-Type: application/x-www-form-urlencoded';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        return json_decode($response, true);
    }

    public function shareContent($access_token, $param)
    {


        $ch = curl_init();
        $url = "https://api.linkedin.com/v2/ugcPosts";

        $headers = array('Content-Type: application/json', 'X-Restli-Protocol-Version: 2.0.0', 'x-li-format: json', 'Authorization: Bearer ' . $access_token);
        $fields = [
            "author" => "urn:li:person:{$param['user_id']}",
            "lifecycleState" => "PUBLISHED",
            "specificContent" => [
                "com.linkedin.ugc.ShareContent" => [
                    "shareCommentary" => [
                        "text" => $param['desc']
                    ],
                    "shareMediaCategory" => "ARTICLE",
                    "media" => [
                        "status" => "READY",
                        "description" => [
                            "text" => $param['desc']
                        ],
                        "title" => [
                            "text" => $param['comment']
                        ],
                        "originalUrl" => $param['content_url']
                    ]
                ]
            ],
            "visibility" => [
                "com.linkedin.ugc.MemberNetworkVisibility" => "PUBLIC"
            ]

        ];
        $fields = json_encode($fields, true);

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $fields);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE); // this results 0 every time
        $response = curl_exec($curl);

        if ($response === false)
            $response = curl_error($curl);

//        echo stripslashes($response);

        curl_close($curl);
        return json_decode($response, true);
    }


    // Boards Api
    public function listBoards($access_token, $param)
    {
        $url = "https://api.trello.com/1/members/{$param['memberId']}/boards?key={$this->client_id}&token=" . $access_token;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        return json_decode($response, true);
    }

    public function getBoard($access_token, $param)
    {
        $url = "https://api.trello.com/1/boards/{$param['id']}?key={$this->client_id}&token=" . $access_token;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        return json_decode($response, true);
    }

    public function createBoard($access_token, $param)
    {
        $params = [
            'name' => $param['name'],
            'idOrganization ' => $param['idOrganization'],
            'desc' => $param['desc'],
            'prefs_permissionLevel' => $param['prefs_permissionLevel'],
            'key' => $this->client_id,
            'token' => $access_token,
        ];
        $url = "https://api.trello.com/1/boards";
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Authorization: Bearer " . $access_token, 'Accept: application/json', 'Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params, true));
        $data = curl_exec($ch);

        return json_decode($data, true);
    }

    public function deleteBoard($access_token, $param)
    {
        $params = [
            'key' => $this->client_id,
            'token' => $access_token,
        ];
        $url = "https://api.trello.com/1/boards/{$param['id']}";
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Authorization: Bearer " . $access_token, 'Accept: application/json', 'Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params, true));
        $data = curl_exec($ch);
        return json_decode($data, true);
    }

    public function closeBoard($access_token, $param)
    {
        $params = [
            'closed' => true,
            'key' => $this->client_id,
            'token' => $access_token,
        ];
        $url = "https://api.trello.com/1/boards/{$param['id']}";
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Authorization: Bearer " . $access_token, 'Accept: application/json', 'Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params, true));
        $data = curl_exec($ch);
        return json_decode($data, true);
    }

    // Organizations
    public function listOrganizations($access_token, $param)
    {
        $url = "https://api.trello.com/1/members/{$param['memberId']}/organizations?key={$this->client_id}&token=" . $access_token;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        return json_decode($response, true);
    }


    // Lists
    public function listLists($access_token, $param)
    {
        $url = "https://api.trello.com/1/boards/{$param['boardId']}/lists?key={$this->client_id}&token=" . $access_token;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        return json_decode($response, true);
    }

    public function listMembers($access_token, $param)
    {
        $url = "https://api.trello.com/1/boards/{$param['boardId']}/members?key={$this->client_id}&token=" . $access_token;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        return json_decode($response, true);
    }

    public function getLists($access_token, $param)
    {
        $url = "https://api.trello.com/1/lists/{$param['id']}?key={$this->client_id}&token=" . $access_token;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        return json_decode($response, true);
    }

    public function createList($access_token, $param)
    {
        $params = [
            'name' => $param['name'],
            'idBoard' => $param['idBoard'],
            'pos' => $param['pos'],
            'key' => $this->client_id,
            'token' => $access_token,
        ];
        $url = "https://api.trello.com/1/lists";
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Authorization: Bearer " . $access_token, 'Accept: application/json', 'Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params, true));
        $data = curl_exec($ch);

        return json_decode($data, true);
    }

    // Card APIs

    public function listCards($access_token, $param)
    {
        $url = "https://api.trello.com/1/lists/{$param['listId']}/cards?key={$this->client_id}&token=" . $access_token;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        return json_decode($response, true);
    }

    public function listCardsByBoards($access_token, $param)
    {
        $url = "https://api.trello.com/1/boards/{$param['boardId']}/cards/all?key={$this->client_id}&token=" . $access_token;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        return json_decode($response, true);
    }

    public function getCard($access_token, $param)
    {
        $url = "https://api.trello.com/1/cards/{$param['cardId']}?key={$this->client_id}&token=" . $access_token;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        return json_decode($response, true);
    }

    public function createCard($access_token, $param)
    {
        $params = [
            'name' => $param['name'],
            'idBoard' => $param['idBoard'],
            'idList' => $param['idList'],
            'desc' => $param['desc'],
            'address' => $param['address'],
            'start' => $param['start'],
            'due' => $param['due'],
            'locationName' => $param['locationName'],
            'key' => $this->client_id,
            'token' => $access_token,
        ];
        $url = "https://api.trello.com/1/cards";
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Authorization: Bearer " . $access_token, 'Accept: application/json', 'Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params, true));
        $data = curl_exec($ch);

        return json_decode($data, true);
    }


    public function updateCard($access_token, $param)
    {
        $params = [
            'name' => $param['name'],
            'idBoard' => $param['idBoard'],
            'idList' => $param['idList'],
            'desc' => $param['desc'],
            'address' => $param['address'],
            'start' => $param['start'],
            'due' => $param['due'],
            'locationName' => $param['locationName'],
            'key' => $this->client_id,
            'token' => $access_token,
        ];
        $url = "https://api.trello.com/1/cards/{$param['id']}";
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Authorization: Bearer " . $access_token, 'Accept: application/json', 'Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params, true));
        $data = curl_exec($ch);
        return json_decode($data, true);
    }

    public function archiveCard($access_token, $param)
    {
        $params = [
            'closed' => true,
            'key' => $this->client_id,
            'token' => $access_token,
        ];
        $url = "https://api.trello.com/1/cards/{$param['id']}";
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Authorization: Bearer " . $access_token, 'Accept: application/json', 'Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params, true));
        $data = curl_exec($ch);
        return json_decode($data, true);
    }

    public function addAttachmentToCard($access_token, $param)
    {
        $params = [
            'name' => $param['name'],
            'mimeType' => $param['mimeType'],
            'url' => $param['url'],
            'setCover' => $param['setCover'],
        ];
        if (isset($param['file']['dataDecode'])) {
            define('MULTIPART_BOUNDARY', '--------------------------' . rand() . time());
            $content = "--" . MULTIPART_BOUNDARY . "\r\n" .
                "Content-Disposition: form-data; name=\"file\"; filename=\"" . $param['name'] . "\"\r\n" .
                "Content-Type: {$param['mimeType']}\r\n\r\n" .
                $param['file']['dataDecode'] . "\r\n";
            $content .= "--" . MULTIPART_BOUNDARY . "--\r\n";
        }

        $url = "https://api.trello.com/1/cards/{$param['id']}/attachments?key={$this->client_id}&token={$access_token}&" . http_build_query($params);
        $ch = curl_init($url);

        file_put_contents($param['name'], $param['file']['dataDecode']);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json', 'Content-Type: multipart/form-data; boundary=' . MULTIPART_BOUNDARY));
        curl_setopt($ch, CURLOPT_POSTFIELDS, isset($param['file']['dataDecode']) ? $content : '');
        $data = curl_exec($ch);
        return json_decode($data, true);
    }

    public function getAttachments($access_token, $param)
    {
        $url = "https://api.trello.com/1/cards/{$param['id']}/attachments?key={$this->client_id}&token=" . $access_token;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        return json_decode($response, true);
    }

    // Label APIs
    public function getLabels($access_token, $param)
    {
        $url = "https://api.trello.com/1/boards/{$param['boardId']}/labels?key={$this->client_id}&token=" . $access_token;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        return json_decode($response, true);
    }

    public function createLabel($access_token, $param)
    {
        $params = [
            'name' => $param['name'],
            'idBoard' => $param['idBoard'],
            'color' => $param['color'],
            'key' => $this->client_id,
            'token' => $access_token,
        ];
        $url = "https://api.trello.com/1/labels";
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Authorization: Bearer " . $access_token, 'Accept: application/json', 'Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params, true));
        $data = curl_exec($ch);
        return json_decode($data, true);
    }

    public function addLabelToCard($access_token, $param)
    {
        $params = [
            'value' => $param['labelId'],
            'key' => $this->client_id,
            'token' => $access_token,
        ];
        $url = "https://api.trello.com/1/cards/{$param['id']}/idLabels";
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Authorization: Bearer " . $access_token, 'Accept: application/json', 'Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params, true));
        $data = curl_exec($ch);
        return json_decode($data, true);
    }

    public function labelColors()
    {
        return explode(", ", "yellow, purple, blue, red, green, orange, black, sky, pink, lime");
    }

    public function createCheckListItemOnCard($access_token, $param)
    {
        $params = [
            'name' => $param['name'],
            'pos' => $param['pos'],
            'key' => $this->client_id,
            'token' => $access_token,
        ];
        $url = "https://api.trello.com/1/cards/{$param['id']}/checklists";
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Authorization: Bearer " . $access_token, 'Accept: application/json', 'Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params, true));
        $data = curl_exec($ch);
        return json_decode($data, true);
    }

}
