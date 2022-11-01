<?php

namespace App\Apps\Triggers;

use App\Apps\GoogleDrive;
use App\Logic\Helpers;
use App\Models\Account;

class GoogleDriveTrigger
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
        $this->mainClass = new GoogleDrive;
        $this->access_token = $this->mainClass->getToken($accountId);
    }

    public function new_folder()
    {
        return [
            'view' => Helpers::translate('Will be fired when a new folder is created. Your drive has been selected. Click \'Check Action\' Button to go on.'),
            'script' => '',
            'message' => Helpers::translate('Connected With SpreadSheets'),
            'status' => 200,
        ];
    }

    public function new_folder_check($data = [])
    {
        $files = $this->mainClass->getFolders($this->access_token);
        return $files[count($files) - 1];
    }


    public function new_folder_changes($zapDatabase = [], $zapData = [])
    {
        $zapDatabase = json_decode($zapDatabase, true)['Files'];
        $zapData = json_decode($zapData, true);
        $data = Helpers::IllitarableArray($zapData['trigger']['Data']);
        $value = Helpers::evaluteData($data);
        $files = $this->mainClass->getFolders($this->access_token);
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
        return $files;
    }

    public function new_folder_update_database($data = null)
    {
        $files = $this->mainClass->getFolders($this->access_token);
        return ['Files' => $files];
    }

// New Files
    public function new_file()
    {

        return [
            'view' => Helpers::translate('Will be fired when a new new file is created. Your drive has been selected. Click \'Check Action\' Button to go on.'),
            'script' => '',
            'message' => Helpers::translate('Connected With Google Drive'),
            'status' => 200,
        ];
    }

    public function new_file_check($data = [])
    {
        $files = $this->mainClass->getFiles($this->access_token, []);
        $file = $this->mainClass->idToFile($this->access_token, $files[0]['id']);
        return $file;
    }


    public function new_file_changes($zapDatabase = [], $zapData = [])
    {
        $zapDatabase = json_decode($zapDatabase, true)['Files'];

        $fileData = [];

        $files = $this->mainClass->getFiles($this->access_token, []);
        $i = 5;
        foreach ($files as $file) {
            if ($file['id'] != $zapDatabase && $i > 0) {
                $fileData[] = $this->mainClass->idToFile($this->access_token, $file['id']);
            } else {
                break;
            }
            $i--;
        }
        return $fileData;
    }

    public function new_file_update_database($data = null)
    {
        $data = Helpers::IllitarableArray($data);
        $value = Helpers::evaluteData($data);
        $folders = $value['string']['folder'][0];
        $files = $this->mainClass->getFiles($this->access_token, ['folder' => $folders]);
        return ['Files' => $files[0]['id']];
    }

// New File In folder
    public function new_file_in_folder()
    {
        $files = $this->mainClass->getFolders($this->access_token);

        foreach ($files as $file) {
            $form['Custom']['string'][] = [
                'id' => 'folder/24110/' . $file['id'],
                'name' => $file['name']
            ];
        }
        $id = rand(0, 4548575451);
        $view[] = view('App.Actions.Fields.Input', [
            'form' => $form,
            'id' => $id,
            'label' => Helpers::translate('Select Folder'),
            'required' => true,
            'multiple' => false
        ])->render();
        $view = Helpers::rap_with_form($view, [], 'triggerForm');
        $scripts[] = view('App.Actions.Fields.Script', [
            'form' => $form,
            'id' => $id,
            'labelId' => 'folder',
            'labelName' => Helpers::translate('Folder'),
        ])->render();

        return [
            'view' => $view,
            'script' => $scripts,
            'message' => Helpers::translate('Connected With Google Drive'),
            'status' => 200,
        ];
    }

    public function new_file_in_folder_check($data = [])
    {
        $data = Helpers::IllitarableArray($data);
        $value = Helpers::evaluteData($data);
        $folders = $value['string']['folder'][0];
        $files = $this->mainClass->getFiles($this->access_token, ['folder' => $folders]);
        $file = $this->mainClass->idToFile($this->access_token, $files[0]['id']);
        return $file;
    }


    public function new_file_in_folder_changes($zapDatabase = [], $zapData = [])
    {
        $zapDatabase = json_decode($zapDatabase, true)['Files'];
        $zapData = json_decode($zapData, true);
        $data = Helpers::IllitarableArray($zapData['trigger']['Data']);
        $value = Helpers::evaluteData($data);

        $fileData = [];

        $folders = $value['string']['folder'][0];
        $files = $this->mainClass->getFiles($this->access_token, ['folder' => $folders]);
        $i = 5;
        foreach ($files as $file) {
            if ($file['id'] != $zapDatabase && $i > 0) {
                $fileData[] = $this->mainClass->idToFile($this->access_token, $file['id']);
            } else {
                break;
            }
            $i--;
        }
        return $fileData;
    }

    public function new_file_in_folder_update_database($data = null)
    {
        $data = Helpers::IllitarableArray($data);
        $value = Helpers::evaluteData($data);
        $folders = $value['string']['folder'][0];
        $files = $this->mainClass->getFiles($this->access_token, ['folder' => $folders]);
        return ['Files' => $files[0]['id']];
    }


// New File In folder
    public function update_file()
    {
        $files = $this->mainClass->getFolders($this->access_token);

        foreach ($files as $file) {
            $form['Custom']['string'][] = [
                'id' => 'folder/24110/' . $file['id'],
                'name' => $file['name']
            ];
        }
        $id = rand(0, 4548575451);
        $view[] = view('App.Actions.Fields.Input', [
            'form' => $form,
            'id' => $id,
            'label' => Helpers::translate('Select Folder'),
            'required' => true,
            'multiple' => false
        ])->render();
        $view = Helpers::rap_with_form($view, [], 'triggerForm');
        $scripts[] = view('App.Actions.Fields.Script', [
            'form' => $form,
            'id' => $id,
            'labelId' => 'folder',
            'labelName' => Helpers::translate('Folder'),
        ])->render();

        return [
            'view' => $view,
            'script' => $scripts,
            'message' => Helpers::translate('Connected With Google Drive'),
            'status' => 200,
        ];
    }

    public function update_file_check($data = [])
    {
        $data = Helpers::IllitarableArray($data);
        $value = Helpers::evaluteData($data);
        $folders = $value['string']['folder'][0];
        $files = $this->mainClass->getFiles($this->access_token, ['folder' => $folders]);
        return $this->mainClass->idToFile($this->access_token, $files[0]['id']);
    }


    public function update_file_changes($zapDatabase = [], $zapData = [])
    {
        $zapDatabase = json_decode($zapDatabase, true)['Files'];
        $zapData = json_decode($zapData, true);
        $data = Helpers::IllitarableArray($zapData['trigger']['Data']);
        $value = Helpers::evaluteData($data);

        $fileData = [];

        $folders = $value['string']['folder'][0];
        $files = $this->mainClass->getFiles($this->access_token, ['folder' => $folders]);
        foreach ($zapDatabase as $key => $database) {
            $zapDatabase[$database['id']] = $database;
            unset($zapDatabase[$key]);
        }

        foreach ($files as $file) {
            if (!array_key_exists($file['id'], $zapDatabase)) {
                $fileDetails = $this->mainClass->idToFile($this->access_token, $file['id']);
                $fileData[] = $fileDetails;
            } elseif (isset($zapDatabase[$file['id']]['md5Checksum']) && $zapDatabase[$file['id']]['md5Checksum'] != $file['md5Checksum']) {
                $fileDetails = $this->mainClass->idToFile($this->access_token, $file['id']);
                $fileData[] = $fileDetails;
            } elseif (!isset($zapDatabase[$file['id']]['md5Checksum'])) {
                if ($zapDatabase[$file['id']]['modifiedTime'] != $file['modifiedTime']) {
                    $fileDetails = $this->mainClass->idToFile($this->access_token, $file['id']);
                    $fileData[] = $fileDetails;
                }
            }
        }
        return $fileData;
    }

    public function update_file_update_database($data = null)
    {
        $data = Helpers::IllitarableArray($data);
        $value = Helpers::evaluteData($data);
        $folders = $value['string']['folder'][0];
        $files = $this->mainClass->getFiles($this->access_token, ['folder' => $folders]);
        return ['Files' => $files];
    }


}
