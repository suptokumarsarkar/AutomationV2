<?php

namespace App\Apps\Triggers;

use App\Apps\GoogleDrive;
use App\Apps\Youtube;
use App\Logic\Helpers;
use App\Models\Account;

class YoutubeTrigger
{
    private $account;
    /**
     * @var Youtube
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
        $this->mainClass = new Youtube;
        $this->access_token = $this->mainClass->getToken($accountId);
    }


// New File In folder
    public function new_video()
    {
        $files = $this->mainClass->getFolders($this->access_token);
        $form['Custom']['custom'][] = [
            'id' => 'custom',
            'name' => Helpers::translate('Add Custom')
        ];
        $id = rand(0, 4548575451);
        $view[] = view('App.Actions.Fields.Input', [
            'form' => $form,
            'id' => $id,
            'label' => Helpers::translate('Channel Id'),
            'labelId' => 'channel',
            'required' => true,
        ])->render();
        $view = Helpers::rap_with_form($view, [], 'triggerForm');
        $scripts[] = view('App.Actions.Fields.Script', [
            'form' => $form,
            'id' => $id,
            'labelId' => 'channel',
            'labelName' => Helpers::translate('Channel Id'),
        ])->render();

        return [
            'view' => $view,
            'script' => $scripts,
            'message' => Helpers::translate('Connected With Youtube'),
            'status' => 200,
        ];
    }

    public function new_video_check($data = [])
    {
        $data = Helpers::IllitarableArray($data);
        $value = Helpers::evaluteData($data);
        $folders = $value['string']['channel'][0];
        $files = $this->mainClass->getVideos($this->access_token, ['channel' => $folders]);
        $data['string'] = $this->mainClass->videoContent($files[count($files) - 1]);
        return $data;
    }


    public function new_video_changes($zapDatabase = [], $zapData = [])
    {
        $zapDatabase = json_decode($zapDatabase, true)['Files'];
        $zapData = json_decode($zapData, true);
        $data = Helpers::IllitarableArray($zapData['trigger']['Data']);
        $value = Helpers::evaluteData($data);

        $fileData = [];

        $folders = $value['string']['channel'][0];
        $files = $this->mainClass->getVideos($this->access_token, ['channel' => $folders]);
        $i = 5;
        foreach ($files as $file) {
            if ($file['id']['videoId'] != $zapDatabase['id'] && $i > 0) {
                $fileData[] = $this->mainClass->videoContent($file);
            } else {
                break;
            }
            $i--;
        }
        return $fileData;
    }

    public function new_video_update_database($data = null)
    {
        $data = Helpers::IllitarableArray($data);
        $value = Helpers::evaluteData($data);

        $folders = $value['string']['channel'][0];
        $files = $this->mainClass->getVideos($this->access_token, ['channel' => $folders]);
        $data = [];
        foreach($files as $file)
        {
            $data['id'] = $file['id']['videoId'];
        }
        return ['Files' => $data];
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
