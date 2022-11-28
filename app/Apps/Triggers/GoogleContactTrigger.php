<?php

namespace App\Apps\Triggers;

use App\Apps\GoogleContact;
use App\Http\Controllers\Api\Apps\Manager;
use App\Logic\Helpers;
use App\Models\Account;

class GoogleContactTrigger
{
    private $account;
    /**
     * @var GoogleContact
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
        $this->mainClass = new GoogleContact;
        $this->access_token = $this->mainClass->getToken($accountId);
    }

    public function new_group()
    {
        return [
            'view' => Helpers::maxNote(Helpers::translate('Will be fired when you create a new group')),
            'script' => '',
            'message' => Helpers::translate('Connected With Contacts'),
            'status' => 200,
        ];
    }

    public function new_group_check($data = [])
    {
        $files = $this->mainClass->getGroups($this->access_token);
        $dataString['string'] = $files[count($files) - 1];
        return $dataString;
    }


    public function new_group_changes($zapDatabase = [], $zapData = [])
    {
        $zapDatabase = json_decode($zapDatabase, true)['Files'];
        $zapData = json_decode($zapData, true);
        $data = Helpers::IllitarableArray($zapData['trigger']['Data']);
        $value = Helpers::evaluteData($data);
        $files = $this->mainClass->getGroups($this->access_token);
        $oldFiles = [];
        $dataFiles = [];
        foreach ($files as $key => $file) {
            $dataFiles[$file['resourceName']] = $file;
        }
        foreach ($zapDatabase as $key => $file) {
            $oldFiles[] = $file['resourceName'];
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

    public function new_group_update_database($data = null)
    {
        $files = $this->mainClass->getGroups($this->access_token);
        return ['Files' => $files];
    }


//    New Contact
    public function new_contact()
    {
        return [
            'view' => Helpers::maxNote(Helpers::translate('Will be fired when you create a new contact')),
            'script' => '',
            'message' => Helpers::translate('Connected With Contacts'),
            'status' => 200,
        ];
    }

    public function new_contact_check($data = [])
    {
        $files = $this->mainClass->getContacts($this->access_token);
        $file = $files[count($files) - 1];
        $manager = new Manager();
        $dataV = $manager->freshArrayParents($file);
        $dataString['string'] = $dataV;
        return $dataString;
    }


    public function new_contact_changes($zapDatabase = [], $zapData = [])
    {
        $zapDatabase = json_decode($zapDatabase, true)['Files'];
        $zapData = json_decode($zapData, true);
        $data = Helpers::IllitarableArray($zapData['trigger']['Data']);
        $value = Helpers::evaluteData($data);
        $files = $this->mainClass->getContacts($this->access_token);
        $oldFiles = [];
        $dataFiles = [];
        foreach ($files as $key => $file) {
            $dataFiles[$file['resourceName']] = $file;
        }
        foreach ($zapDatabase as $key => $file) {
            $oldFiles[] = $file['resourceName'];
        }
        $files = [];
        foreach ($dataFiles as $key => $latest) {
            if (!in_array($key, $oldFiles)) {
                $manager = new Manager();
                $dataV = $manager->freshArrayParents($latest);
                $files[] = $dataV;
            }
        }
        $dataString['string'] = $files;
        return $dataString;
    }

    public function new_contact_update_database($data = null)
    {
        $files = $this->mainClass->getContacts($this->access_token);
        $dataMaintain = [];
        foreach ($files as $file){
            $dataMaintain[] = [
                'resourceName' => $file['resourceName']
            ];
        }
        return ['Files' => $dataMaintain];
    }


}
