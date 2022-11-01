<?php

namespace App\Apps\Actions;

use App\Apps\GoogleDrive;
use App\Http\Controllers\Api\Apps\Manager;
use App\Logic\Helpers;
use App\Models\Account;

class GoogleDriveActionFields
{
    private $account;
    /**
     * @var mixed
     */
    private $userId;
    /**
     * @var GoogleDrive
     */
    private $mainClass;
    /**
     * @var mixed
     */
    private $access_token;

    public function upload_file($data)
    {
        $form = [];


        // Labels
        $actionAccount = $data['action']['account_id'];
        $drive = new GoogleDrive;
        $accessToken = $drive->getToken($actionAccount);
        $userId = $drive->getUserId($actionAccount);
        $allLabels = $drive->getFolders($accessToken);

        // Custom Data
        foreach ($allLabels as $key => $value) {
            $form['Custom']['string'][] = [
                'id' => "folder/24110/" . $value['id'],
                'name' => $value['name']
            ];
        }
        $id = rand(0, 4548575451);


        $view = view('App.Actions.Fields.Input', [
            'form' => $form,
            'id' => $id,
            'label' => Helpers::translate('Select Folder To Upload'),
            'labelId' => "folder",
            'required' => false
        ])->render();
        $views[] = $view;

        $scripts[] = view('App.Actions.Fields.Script', [
            'form' => $form,
            'id' => $id,
            'labelId' => "name",
            'labelName' => "Folder",
        ])->render();

//BCC


        $dataV = json_decode(json_encode($data, true), true);
        $manager = new Manager;
        $api_fields = $manager->getTriggerValue($dataV);
        $form = [];
        if (isset($api_fields['file'])) {
            foreach ($api_fields['file'] as $key1 => $value1) {
                $form['Api']['api'][] = [
                    'id' => "Attachment/24110/" . $key1,
                    'name' => $key1
                ];
            }
        }
        // Labels
        $id = rand(0, 4548575451);


        $view = view('App.Actions.Fields.Input', [
            'form' => $form,
            'id' => $id,
            'label' => "Attachment / File",
            'labelId' => "Attachment",
        ])->render();
        $views[] = $view;

        $scripts[] = view('App.Actions.Fields.Script', [
            'form' => $form,
            'id' => $id,
            'labelId' => "Attachment",
            'labelName' => "Attachment / File",
        ])->render();


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
            'label' => "File Name",
            'labelId' => "name",
        ])->render();
        $views[] = $view;

        $scripts[] = view('App.Actions.Fields.Script', [
            'form' => $form,
            'id' => $id,
            'labelId' => "name",
            'labelName' => "File Name",
        ])->render();


        $view = Helpers::rap_with_form($views, $data);


        return [
            'view' => $view,
            'script' => $scripts,
            'message' => Helpers::translate('Connected With Data Fields'),
            'status' => 200,
        ];

    }

    public function upload_file_post($accountId, $mainData, $data)
    {
        $this->account = Account::find($accountId);
        $this->userId = json_decode($this->account['data'], true)['sub'];
        $this->mainClass = new GoogleDrive;
        $this->access_token = $this->mainClass->getToken($accountId);
        // Pass Trigger Access Token
        $class = 'App\\Apps\\' . $data['trigger']['AppId'];
        $classData = new $class;
        $mainData['TriggerAccessToken'] = $classData->fileManagerInstance($data['trigger']['account_id']);
        if (isset($mainData['Attachment'])) {
            foreach ($mainData['Attachment'] as $attachment) {
                $class = 'App\\Apps\\' . $attachment['FileHandler'];
                $fileManager = new $class;
                $file = $fileManager->setFile($attachment, $mainData['TriggerAccessToken']);
                foreach ($mainData['folder'] as $folder) {
                    if (is_array($mainData['name'])) {
                        $name = implode(" ", $mainData['name']);
                    } else {
                        $name = $mainData['name'];
                    }
                    $this->mainClass->uploadFileMetadata($this->access_token, [
                        'mimeType' => $file['mimeType'],
                        'parent' => $folder,
                        'name' => $name,
                        'content' => $file['dataDecode'],
                    ]);
                }
            }
            return json_encode([
                'status' => 200,
                'message' => Helpers::translate('Successfully Applied First Nit')
            ]);
        } else {
            return json_encode([
                'status' => 400,
                'message' => Helpers::translate('Failed to Process Name for Label Creation.')
            ]);
        }
    }

    public function create_file_from_text($data)
    {
        $form = [];


        // Labels
        $actionAccount = $data['action']['account_id'];
        $drive = new GoogleDrive;
        $accessToken = $drive->getToken($actionAccount);
        $userId = $drive->getUserId($actionAccount);
        $allLabels = $drive->getFolders($accessToken);

        // Custom Data
        foreach ($allLabels as $key => $value) {
            $form['Custom']['string'][] = [
                'id' => "folder/24110/" . $value['id'],
                'name' => $value['name']
            ];
        }
        $id = rand(0, 4548575451);


        $view = view('App.Actions.Fields.Input', [
            'form' => $form,
            'id' => $id,
            'label' => Helpers::translate('Select Folder To Upload'),
            'labelId' => "folder",
            'required' => false
        ])->render();
        $views[] = $view;

        $scripts[] = view('App.Actions.Fields.Script', [
            'form' => $form,
            'id' => $id,
            'labelId' => "name",
            'labelName' => "Folder",
        ])->render();

//BCC


        $dataV = json_decode(json_encode($data, true), true);
        $manager = new Manager;
        $api_fields = $manager->getTriggerValue($dataV);
        $form = [];
        if (isset($api_fields['string'])) {
            foreach ($api_fields['string'] as $key1 => $value1) {
                $form['Api']['api'][] = [
                    'id' => "Attachment/24110/" . $key1,
                    'name' => $key1
                ];
            }
        }
        // Labels
        $id = rand(0, 4548575451);


        $view = view('App.Actions.Fields.Input', [
            'form' => $form,
            'id' => $id,
            'label' => "File Content",
            'labelId' => "Content",
        ])->render();
        $views[] = $view;

        $scripts[] = view('App.Actions.Fields.Script', [
            'form' => $form,
            'id' => $id,
            'labelId' => "Content",
            'labelName' => "File Content",
        ])->render();


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
            'label' => "File Name",
            'labelId' => "name",
        ])->render();
        $views[] = $view;

        $scripts[] = view('App.Actions.Fields.Script', [
            'form' => $form,
            'id' => $id,
            'labelId' => "name",
            'labelName' => "File Name",
        ])->render();


        $view = Helpers::rap_with_form($views, $data);


        return [
            'view' => $view,
            'script' => $scripts,
            'message' => Helpers::translate('Connected With Data Fields'),
            'status' => 200,
        ];

    }

    public function create_file_from_text_post($accountId, $mainData, $data)
    {
        $this->account = Account::find($accountId);
        $this->userId = json_decode($this->account['data'], true)['sub'];
        $this->mainClass = new GoogleDrive;
        $this->access_token = $this->mainClass->getToken($accountId);
        if (isset($mainData['Attachment'])) {
            if (is_array($mainData['Attachment'])) {
                $attachment = implode(" ", $mainData['Attachment']);
            } else {
                $attachment = $mainData['Attachment'];
            }
            foreach ($mainData['folder'] as $folder) {
                if (is_array($mainData['name'])) {
                    $name = implode(" ", $mainData['name']);
                } else {
                    $name = $mainData['name'];
                }
                $this->mainClass->uploadFileMetadata($this->access_token, [
                    'mimeType' => 'text/plain',
                    'parent' => $folder,
                    'name' => $name,
                    'content' => $attachment,
                ]);
            }
            return json_encode([
                'status' => 200,
                'message' => Helpers::translate('Successfully Applied First Nit')
            ]);
        } else {
            return json_encode([
                'status' => 400,
                'message' => Helpers::translate('Failed to Process Name for Label Creation.')
            ]);

        }
    }


    public function copy_file($data)
    {
        $form = [];


        // Labels
        $actionAccount = $data['action']['account_id'];
        $drive = new GoogleDrive;
        $accessToken = $drive->getToken($actionAccount);
        $userId = $drive->getUserId($actionAccount);
        $allLabels = $drive->getFolders($accessToken);

        // Custom Data
        foreach ($allLabels as $key => $value) {
            $form['Custom']['string'][] = [
                'id' => "folder/24110/" . $value['id'],
                'name' => $value['name']
            ];
        }
        $id = rand(0, 4548575451);


        $view = view('App.Actions.Fields.Input', [
            'form' => $form,
            'id' => $id,
            'label' => Helpers::translate('Copy To'),
            'labelId' => "folder",
            'required' => false
        ])->render();
        $views[] = $view;

        $scripts[] = view('App.Actions.Fields.Script', [
            'form' => $form,
            'id' => $id,
            'labelId' => "folder",
            'labelName' => "Copy To",
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
                    'id' => "FileId/24110/" . $key1,
                    'name' => $key1
                ];
            }
        }
        // Labels
        $id = rand(0, 4548575451);


        $view = view('App.Actions.Fields.Input', [
            'form' => $form,
            'id' => $id,
            'label' => "File Id",
            'labelId' => "FileId",
        ])->render();
        $views[] = $view;

        $scripts[] = view('App.Actions.Fields.Script', [
            'form' => $form,
            'id' => $id,
            'labelId' => "FileId",
            'labelName' => "File Id",
        ])->render();


        $view = Helpers::rap_with_form($views, $data);


        return [
            'view' => $view,
            'script' => $scripts,
            'message' => Helpers::translate('Connected With Data Fields'),
            'status' => 200,
        ];

    }

    public function copy_file_post($accountId, $mainData, $data)
    {
        $this->account = Account::find($accountId);
        $this->userId = json_decode($this->account['data'], true)['sub'];
        $this->mainClass = new GoogleDrive;
        $this->access_token = $this->mainClass->getToken($accountId);
        if (isset($mainData['FileId'])) {
            if (!is_array($mainData['FileId'])) {
                $fileIds = [$mainData['FileId']];
            } else {
                $fileIds = $mainData['FileId'];
            }
            foreach ($fileIds as $fileId) {
                foreach ($mainData['folder'] as $folder) {
                    $this->mainClass->copyFile($this->access_token, [
                        'fileId' => $fileId,
                        'parent' => $folder,
                    ]);
                }

            }
            return json_encode([
                'status' => 200,
                'message' => Helpers::translate('Successfully Applied First Nit')
            ]);
        } else {
            return json_encode([
                'status' => 400,
                'message' => Helpers::translate('Failed to Process Name for Label Creation.')
            ]);

        }
    }

    public function create_shortcut($data)
    {
        $form = [];


        // Labels
        $actionAccount = $data['action']['account_id'];
        $drive = new GoogleDrive;
        $accessToken = $drive->getToken($actionAccount);
        $userId = $drive->getUserId($actionAccount);
        $allLabels = $drive->getFolders($accessToken);

        // Custom Data
        foreach ($allLabels as $key => $value) {
            $form['Custom']['string'][] = [
                'id' => "folder/24110/" . $value['id'],
                'name' => $value['name']
            ];
        }
        $id = rand(0, 4548575451);


        $view = view('App.Actions.Fields.Input', [
            'form' => $form,
            'id' => $id,
            'label' => Helpers::translate('Create at'),
            'labelId' => "folder",
            'required' => false
        ])->render();
        $views[] = $view;

        $scripts[] = view('App.Actions.Fields.Script', [
            'form' => $form,
            'id' => $id,
            'labelId' => "folder",
            'labelName' => "Create At",
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
                    'id' => "FileId/24110/" . $key1,
                    'name' => $key1
                ];
            }
        }
        // Labels
        $id = rand(0, 4548575451);


        $view = view('App.Actions.Fields.Input', [
            'form' => $form,
            'id' => $id,
            'label' => "File Id",
            'labelId' => "FileId",
        ])->render();
        $views[] = $view;

        $scripts[] = view('App.Actions.Fields.Script', [
            'form' => $form,
            'id' => $id,
            'labelId' => "FileId",
            'labelName' => "File Id",
        ])->render();


        $form = [];
        $form['Custom']['custom'][] = [
            'id' => 'custom',
            'name' => Helpers::translate('Add Custom')
        ];
        if (isset($api_fields['string'])) {
            foreach ($api_fields['string'] as $key1 => $value1) {
                $form['Api']['api'][] = [
                    'id' => "Name/24110/" . $key1,
                    'name' => $key1
                ];
            }
        }
        // Labels
        $id = rand(0, 4548575451);


        $view = view('App.Actions.Fields.Input', [
            'form' => $form,
            'id' => $id,
            'label' => "Shortcut Name",
            'labelId' => "Name",
        ])->render();
        $views[] = $view;

        $scripts[] = view('App.Actions.Fields.Script', [
            'form' => $form,
            'id' => $id,
            'labelId' => "Name",
            'labelName' => "Shortcut Name",
        ])->render();


        $view = Helpers::rap_with_form($views, $data);


        return [
            'view' => $view,
            'script' => $scripts,
            'message' => Helpers::translate('Connected With Data Fields'),
            'status' => 200,
        ];

    }

    public function create_shortcut_post($accountId, $mainData, $data)
    {
        $this->account = Account::find($accountId);
        $this->userId = json_decode($this->account['data'], true)['sub'];
        $this->mainClass = new GoogleDrive;
        $this->access_token = $this->mainClass->getToken($accountId);
        if (isset($mainData['FileId'])) {
            if (!is_array($mainData['FileId'])) {
                $fileIds = [$mainData['FileId']];
            } else {
                $fileIds = $mainData['FileId'];
            }

            if (is_array($mainData['Name'])) {
                $name = implode(" ", $mainData['Name']);
            } else {
                $name = $mainData['Name'];
            }
            foreach ($fileIds as $fileId) {
                foreach ($mainData['folder'] as $folder) {
                    $this->mainClass->createFile($this->access_token, [
                        'shortcutId' => $fileId,
                        'name' => $name,
                        'parents' => [$folder],
                        'mimeType' => 'application/vnd.google-apps.shortcut',
                    ]);
                }

            }
            return json_encode([
                'status' => 200,
                'message' => Helpers::translate('Successfully Applied First Nit')
            ]);
        } else {
            return json_encode([
                'status' => 400,
                'message' => Helpers::translate('Failed to Process Name for Label Creation.')
            ]);

        }
    }


    public function move_file($data)
    {
        $form = [];


        // Labels
        $actionAccount = $data['action']['account_id'];
        $drive = new GoogleDrive;
        $accessToken = $drive->getToken($actionAccount);
        $userId = $drive->getUserId($actionAccount);
        $allLabels = $drive->getFolders($accessToken);

        // Custom Data
        foreach ($allLabels as $key => $value) {
            $form['Custom']['string'][] = [
                'id' => "folder/24110/" . $value['id'],
                'name' => $value['name']
            ];
        }
        $id = rand(0, 4548575451);


        $view = view('App.Actions.Fields.Input', [
            'form' => $form,
            'id' => $id,
            'label' => Helpers::translate('Move to'),
            'labelId' => "folder",
            'required' => false
        ])->render();
        $views[] = $view;

        $scripts[] = view('App.Actions.Fields.Script', [
            'form' => $form,
            'id' => $id,
            'labelId' => "folder",
            'labelName' => "Move to",
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
                    'id' => "FileId/24110/" . $key1,
                    'name' => $key1
                ];
            }
        }
        // Labels
        $id = rand(0, 4548575451);


        $view = view('App.Actions.Fields.Input', [
            'form' => $form,
            'id' => $id,
            'label' => "File Id",
            'labelId' => "FileId",
        ])->render();
        $views[] = $view;

        $scripts[] = view('App.Actions.Fields.Script', [
            'form' => $form,
            'id' => $id,
            'labelId' => "FileId",
            'labelName' => "File Id",
        ])->render();


        $view = Helpers::rap_with_form($views, $data);


        return [
            'view' => $view,
            'script' => $scripts,
            'message' => Helpers::translate('Connected With Data Fields'),
            'status' => 200,
        ];

    }

    public function move_file_post($accountId, $mainData, $data)
    {
        $this->account = Account::find($accountId);
        $this->userId = json_decode($this->account['data'], true)['sub'];
        $this->mainClass = new GoogleDrive;
        $this->access_token = $this->mainClass->getToken($accountId);
        if (isset($mainData['FileId'])) {
            if (!is_array($mainData['FileId'])) {
                $fileIds = [$mainData['FileId']];
            } else {
                $fileIds = $mainData['FileId'];
            }

            foreach ($fileIds as $fileId) {
                foreach ($mainData['folder'] as $folder) {
                    $dataFile = $this->mainClass->getFile($this->access_token, $fileId);
                    $this->mainClass->updateFile($this->access_token, [
                        'parent' => $folder,
                        'name' => $dataFile['name'],
                    ], $fileId);
                }

            }
            return json_encode([
                'status' => 200,
                'message' => Helpers::translate('Successfully Applied First Nit')
            ]);
        } else {
            return json_encode([
                'status' => 400,
                'message' => Helpers::translate('Failed to Process Name for Label Creation.')
            ]);

        }
    }


    public function create_folder($data)
    {
        $form = [];


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
                    'id' => "folder/24110/" . $key1,
                    'name' => $key1
                ];
            }
        }
        // Labels
        $id = rand(0, 4548575451);


        $view = view('App.Actions.Fields.Input', [
            'form' => $form,
            'id' => $id,
            'label' => "Folder Name",
            'labelId' => "Folder",
        ])->render();
        $views[] = $view;

        $scripts[] = view('App.Actions.Fields.Script', [
            'form' => $form,
            'id' => $id,
            'labelId' => "Folder",
            'labelName' => "Folder Name",
        ])->render();


        $view = Helpers::rap_with_form($views, $data);


        return [
            'view' => $view,
            'script' => $scripts,
            'message' => Helpers::translate('Connected With Data Fields'),
            'status' => 200,
        ];

    }

    public function create_folder_post($accountId, $mainData, $data)
    {
        $this->account = Account::find($accountId);
        $this->userId = json_decode($this->account['data'], true)['sub'];
        $this->mainClass = new GoogleDrive;
        $this->access_token = $this->mainClass->getToken($accountId);
        if (isset($mainData['folder'])) {
            if (is_array($mainData['folder'])) {
                $attachment = implode(" ", $mainData['folder']);
            } else {
                $attachment = $mainData['folder'];
            }
            $this->mainClass->createFile($this->access_token, [
                'name' => $attachment,
                'mimeType' => 'application/vnd.google-apps.folder'
            ]);
            return json_encode([
                'status' => 200,
                'message' => Helpers::translate('Successfully Applied First Nit')
            ]);
        } else {
            return json_encode([
                'status' => 400,
                'message' => Helpers::translate('Failed to Process Name for Label Creation.')
            ]);
        }
    }
}
