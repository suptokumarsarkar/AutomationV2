<?php

namespace App\Apps\Triggers;

use App\Apps\GoogleDrive;
use App\Apps\GoogleTask;
use App\Logic\Helpers;
use App\Models\Account;

class GoogleTaskTrigger
{
    private $account;
    /**
     * @var GoogleTask
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
        $this->mainClass = new GoogleTask;
        $this->access_token = $this->mainClass->getToken($accountId);
    }

    public function new_task_list()
    {
        return [
            'view' => Helpers::translate('Will be fired when a new task list is created. Your account has been selected. Click \'Check Action\' Button to go on.'),
            'script' => '',
            'message' => Helpers::translate('Connected With Tasks'),
            'status' => 200,
        ];
    }

    public function new_task_list_check($data = [])
    {
        $files = $this->mainClass->getTaskLists($this->access_token);
        $dataString['string'] = $files[count($files) - 1];
        return $dataString;
    }


    public function new_task_list_changes($zapDatabase = [], $zapData = [])
    {
        $zapDatabase = json_decode($zapDatabase, true)['Files'];
        $zapData = json_decode($zapData, true);
        $data = Helpers::IllitarableArray($zapData['trigger']['Data']);
        $value = Helpers::evaluteData($data);
        $files = $this->mainClass->getTaskLists($this->access_token);
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

    public function new_task_list_update_database($data = null)
    {
        $files = $this->mainClass->getTaskLists($this->access_token);
        return ['Files' => $files];
    }

//    New Completed Task
    public function new_completed_task(): array
    {
        $files = $this->mainClass->getTaskLists($this->access_token);
        foreach ($files as $file) {
            $form['Custom']['string'][] = [
                'id' => 'taskListId/24110/' . $file['id'],
                'name' => $file['title']
            ];
        }
        $id = rand(0, 4548575451);
        $view[] = view('App.Actions.Fields.Input', [
            'form' => $form,
            'id' => $id,
            'label' => Helpers::translate('Select Task List'),
            'required' => true,
            'multiple' => false
        ])->render();
        $view = Helpers::rap_with_form($view, [], 'triggerForm');
        $scripts[] = view('App.Actions.Fields.Script', [
            'form' => $form,
            'id' => $id,
            'labelId' => 'taskListId',
            'labelName' => Helpers::translate('Task List'),
        ])->render();

        return [
            'view' => $view,
            'script' => $scripts,
            'message' => Helpers::translate('Connected With Tasks'),
            'status' => 200,
        ];
    }

    public function new_completed_task_check($data = []): array
    {
        $data = Helpers::IllitarableArray($data);
        $value = Helpers::evaluteData($data);
        $param = [
            'taskListId' => $value['string']['taskListId'][0]
        ];
        $files = $this->mainClass->getTasks($this->access_token, $param);
        $filesData = [];
        foreach ($files as $key => $file) {
            if ($file['status'] == 'completed') {
                $filesData[] = $file;
            }
        }
        $dataString['string'] = $filesData[count($filesData) - 1];
        return $dataString;
    }


    public function new_completed_task_changes($zapDatabase = [], $zapData = []): array
    {
        $zapDatabase = json_decode($zapDatabase, true)['Files'];
        $zapData = json_decode($zapData, true);
        $data = Helpers::IllitarableArray($zapData['trigger']['Data']);
        $value = Helpers::evaluteData($data);
        $param = [
            'taskListId' => $value['string']['taskListId'][0]
        ];
        $filesData = $this->mainClass->getTasks($this->access_token, $param);
        $files = [];
        foreach ($filesData as $key => $file) {
            if ($file['status'] == 'completed') {
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

    public function new_completed_task_update_database($data = null): array
    {
        $data = Helpers::IllitarableArray($data);
        $value = Helpers::evaluteData($data);
        $param = [
            'taskListId' => $value['string']['taskListId'][0]
        ];
        $filesData = $this->mainClass->getTasks($this->access_token, $param);
        $files = [];
        foreach ($filesData as $key => $file) {
            if ($file['status'] == 'completed') {
                $files[] = $file;
            }
        }
        return ['Files' => $files];
    }


//    New Task
    public function new_task(): array
    {
        $files = $this->mainClass->getTaskLists($this->access_token);
        foreach ($files as $file) {
            $form['Custom']['string'][] = [
                'id' => 'taskListId/24110/' . $file['id'],
                'name' => $file['title']
            ];
        }
        $id = rand(0, 4548575451);
        $view[] = view('App.Actions.Fields.Input', [
            'form' => $form,
            'id' => $id,
            'label' => Helpers::translate('Select Task List'),
            'required' => true,
            'multiple' => false
        ])->render();
        $view = Helpers::rap_with_form($view, [], 'triggerForm');
        $scripts[] = view('App.Actions.Fields.Script', [
            'form' => $form,
            'id' => $id,
            'labelId' => 'taskListId',
            'labelName' => Helpers::translate('Task List'),
        ])->render();

        return [
            'view' => $view,
            'script' => $scripts,
            'message' => Helpers::translate('Connected With Tasks'),
            'status' => 200,
        ];
    }

    public function new_task_check($data = []): array
    {
        $data = Helpers::IllitarableArray($data);
        $value = Helpers::evaluteData($data);
        $param = [
            'taskListId' => $value['string']['taskListId'][0]
        ];
        $filesData = $this->mainClass->getTasks($this->access_token, $param);
        $dataString['string'] = $filesData[count($filesData) - 1];
        return $dataString;
    }


    public function new_task_changes($zapDatabase = [], $zapData = []): array
    {
        $zapDatabase = json_decode($zapDatabase, true)['Files'];
        $zapData = json_decode($zapData, true);
        $data = Helpers::IllitarableArray($zapData['trigger']['Data']);
        $value = Helpers::evaluteData($data);
        $param = [
            'taskListId' => $value['string']['taskListId'][0]
        ];
        $files = $this->mainClass->getTasks($this->access_token, $param);

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

    public function new_task_update_database($data = null): array
    {
        $data = Helpers::IllitarableArray($data);
        $value = Helpers::evaluteData($data);
        $param = [
            'taskListId' => $value['string']['taskListId'][0]
        ];
        $files = $this->mainClass->getTasks($this->access_token, $param);

        return ['Files' => $files];
    }


}
