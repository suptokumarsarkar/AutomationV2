<?php

namespace App\Apps;

use App\Apps\Triggers\GoogleContactTrigger;
use App\Logic\Helpers;
use App\Models\Account;
use App\Models\AppsData;

class GoogleContact
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
        $gmail = AppsData::where("AppId", "GoogleContact")->first();
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
                'id' => 'new_group',
                'name' => Helpers::translate('New Group'),
                'description' => Helpers::translate('Triggers when you create a new group')
            ],
            [
                'id' => 'new_contact',
                'name' => Helpers::translate('New Contact'),
                'description' => Helpers::translate('Triggers when you create a new contact')
            ]
        );
    }

    public function getActions(): array
    {
        return array(
            [
                'id' => 'create_group',
                'name' => Helpers::translate('Create Group'),
                'description' => Helpers::translate('Creates a new group')
            ],
            [
                'id' => 'create_contact',
                'name' => Helpers::translate('Create Contact'),
                'description' => Helpers::translate('Creates a new contact')
            ],
            [
                'id' => 'update_contact',
                'name' => Helpers::translate('Update Contact'),
                'description' => Helpers::translate('Updates an existing contact')
            ],
            [
                'id' => 'upload_contact_photo',
                'name' => Helpers::translate('Upload Contact Photo'),
                'description' => Helpers::translate('Updates an existing contact')
            ],
            [
                'id' => 'add_contact_to_group',
                'name' => Helpers::translate('Add Contact to Group'),
                'description' => Helpers::translate('Updates a group with an existing contact')
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
        $trigger = new GoogleContactTrigger($accountId);
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


    // Group List API
    public function getGroups($access_token)
    {
        $url = "https://people.googleapis.com/v1/contactGroups?groupFields=clientData,groupType,memberCount,metadata,name&pageSize=1000&access_token=" . $access_token;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        return json_decode($response, true)['contactGroups'];
    }

    public function createGroup($access_token, $param)
    {
        $params = [
            'contactGroup' => [
                'name' => $param['name']
            ],
        ];
        $url = "https://people.googleapis.com/v1/contactGroups?access_token=" . $access_token;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Authorization: Bearer " . $access_token, 'Accept: application/json', 'Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params, true));
        $data = curl_exec($ch);

        return json_decode($data, true);
    }

    // People API
    public function getContacts($access_token)
    {
        $url = "https://people.googleapis.com/v1/people/me/connections?pageSize=2000&personFields=addresses,photos,names,birthdays,biographies,coverPhotos,emailAddresses,genders,nicknames,phoneNumbers&access_token=" . $access_token;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        return json_decode($response, true)['connections'];
    }

    public function createContact($access_token, $param)
    {

        $params = [
            "names" => [
                [
                    "givenName" => $param['name'],
                ]
            ],
            "phoneNumbers" => [
                [
                    "value" => $param['number']
                ]
            ],
            "addresses" => [
                [
                    "city" => $param['city'],
                    "country" => $param['country']
                ]
            ],
            "emailAddresses" => [
                [
                    "value" => $param['emailAddresses'],
                ]
            ],
            "biographies" => [
                [
                    "value" => $param['biographies']
                ]
            ],
            "genders" => [
                [
                    "value" => $param['gender']
                ]
            ],
            "organizations" => [
                [
                    "jobDescription" => $param['organizationJobDescription'],
                    "title" => $param['organizationTitle']
                ]
            ]
        ];
        if ($param['birthday'] == true) {
            $params['birthdays'] = [
                [
                    "date" => [
                        "day" => $param['day'],
                        "month" => $param['month'],
                        "year" => $param['year']
                    ]
                ]
            ];
        }


        $url = "https://people.googleapis.com/v1/people:createContact?personFields=addresses,photos,names,birthdays,biographies,coverPhotos,emailAddresses,genders,nicknames,phoneNumbers&access_token=" . $access_token;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Authorization: Bearer " . $access_token, 'Accept: application/json', 'Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params, true));
        $data = curl_exec($ch);

        return json_decode($data, true);
    }

    public function updateContact($access_token, $param)
    {

        $params = [
            "names" => [
                [
                    "givenName" => $param['name'],
                ]
            ],
            "phoneNumbers" => [
                [
                    "value" => $param['number']
                ]
            ],
            "addresses" => [
                [
                    "city" => $param['city'],
                    "country" => $param['country']
                ]
            ],
            "biographies" => [
                [
                    "value" => $param['biographies']
                ]
            ],
            "birthdays" => [
                [
                    "date" => [
                        "day" => $param['day'],
                        "month" => $param['month'],
                        "year" => $param['year']
                    ]
                ]
            ],
            "genders" => [
                [
                    "value" => $param['gender']
                ]
            ],
            "organizations" => [
                [
                    "jobDescription" => $param['organizationJobDescription'],
                    "title" => $param['organizationTitle']
                ]
            ]
        ];


        $url = "https://people.googleapis.com/v1/{$param['resources']}:updateContact?personFields=addresses,photos,names,birthdays,biographies,coverPhotos,emailAddresses,genders,nicknames,phoneNumbers&updatePersonFields=addresses,photos,names,birthdays,biographies,coverPhotos,emailAddresses,genders,nicknames,phoneNumbers&access_token=" . $access_token;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        // don't do ssl checks
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Authorization: Bearer " . $access_token, 'Accept: application/json', 'Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params, true));
        $data = curl_exec($ch);

        return json_decode($data, true);
    }

    public function updateProfilePicture($access_token, $param)
    {
        $params = [
            'sources' => ["READ_SOURCE_TYPE_PROFILE",
                "READ_SOURCE_TYPE_CONTACT"],
            'personFields' => 'photos',
            'photoBytes' => $param['photoEncoded']
        ];
        $url = "https://people.googleapis.com/v1/{$param['resources']}:updateContactPhoto?access_token=" . $access_token;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Authorization: Bearer " . $access_token, 'Accept: application/json', 'Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params, true));
        $data = curl_exec($ch);

        return json_decode($data, true);
    }

}
