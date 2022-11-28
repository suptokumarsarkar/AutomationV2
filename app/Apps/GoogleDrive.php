<?php

namespace App\Apps;

use App\Apps\Triggers\GoogleDriveTrigger;
use App\Logic\Helpers;
use App\Models\Account;
use App\Models\AppsData;

class GoogleDrive
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
        $gmail = AppsData::where("AppId", "GoogleDrive")->first();
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
                'id' => 'new_folder',
                'name' => Helpers::translate('New Folder'),
                'description' => Helpers::translate('Triggers when you create a new folder')
            ],
            [
                'id' => 'new_file',
                'name' => Helpers::translate('New File'),
                'description' => Helpers::translate('Triggers when you add a new file')
            ],
            [
                'id' => 'new_file_in_folder',
                'name' => Helpers::translate('New File In Folder'),
                'description' => Helpers::translate('Triggers when you create a new file inside a specific folder (not it\'s sub folders)')
            ],
            [
                'id' => 'update_file',
                'name' => Helpers::translate('Update File'),
                'description' => Helpers::translate('Triggers when you a file is updated inside a specific folder (not it\'s sub folders)')
            ]
        );
    }

    public function getActions(): array
    {
        return array(
            [
                'id' => 'copy_file',
                'name' => Helpers::translate('Copy File'),
                'description' => Helpers::translate('Copy a file')
            ],
            [
                'id' => 'create_shortcut',
                'name' => Helpers::translate('Create Shortcut'),
                'description' => Helpers::translate('Creates a shortcut of a file')
            ],
            [
                'id' => 'create_folder',
                'name' => Helpers::translate('Create Folder'),
                'description' => Helpers::translate('Creates a new folder')
            ],
            [
                'id' => 'move_file',
                'name' => Helpers::translate('Move File'),
                'description' => Helpers::translate('Changes the files parent paths')
            ],
            [
                'id' => 'upload_file',
                'name' => Helpers::translate('Upload file'),
                'description' => Helpers::translate('Uploads file into google drive.')
            ],
            [
                'id' => 'create_file_from_text',
                'name' => Helpers::translate('Create File From Text'),
                'description' => Helpers::translate('Creates a text file under google drive')
            ],
            /*[
                'id' => 'replace_file',
                'name' => Helpers::translate('Replace File'),
                'description' => Helpers::translate('Replaces a file content and metadata')
            ]*/
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
        $trigger = new GoogleDriveTrigger($accountId);
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

    // Real APIs
    public function getFiles($access_token, $params)
    {
        $external = 'orderBy=createdTime+desc&fields=*';
        if (isset($params['folder'])) {
            $external .= '&q="' . $params['folder'] . '"+in+parents';
        }
        $url = "https://www.googleapis.com/drive/v3/files?" . $external . '&access_token=' . $access_token;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        return json_decode($response, 1)['files'];
    }

    public function getFile($access_token, $id)
    {
        $url = "https://www.googleapis.com/drive/v3/files/{$id}?fields=*&access_token=" . $access_token;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);

        return json_decode($response, true);
    }

    public function getFileContent($access_token, $id)
    {
        $url = "https://www.googleapis.com/drive/v3/files/{$id}?alt=media";
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Authorization: Bearer " . $access_token, 'Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        return curl_exec($ch);
    }

    public function getFolders($access_token)
    {

        $url = "https://www.googleapis.com/drive/v3/files?access_token=" . $access_token . '&q=' . urlencode("mimeType='application/vnd.google-apps.folder'");

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);

        return json_decode($response, 1)['files'];
    }

    public function uploadFileMetadata($access_token, $param)
    {
        $file = $this->uploadFile($access_token, $param);
        if (isset($file['id'])) {
            return $this->updateFile($access_token, $param, $file['id']);
        }
    }

    public function uploadFile($access_token, $param)
    {
        $params = [
            'parents' => $param['parent'],
            'name' => rand(),
            'mimeType' => $param['mimeType'],
            'uploadType' => 'multipart',
            'fields' => 'id'
        ];
        $data = $param['content'];
        $url = "https://www.googleapis.com/upload/drive/v3/files/?access_token=" . $access_token . "&" . http_build_query($params);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Authorization: Bearer " . $access_token, 'Accept: application/json', 'Content-Type: ' . $param['mimeType']));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $data = curl_exec($ch);

        return json_decode($data, true);
    }

    public function createFile($access_token, $param)
    {

        $params = [
            'name' => $param['name'] ?? rand(),
            'mimeType' => $param['mimeType'] ?? 'text/plain',


        ];
        if (isset($param['shortcutId'])) {
            $params['shortcutDetails'] =
                [
                    'targetId' => $param['shortcutId']
                ];
        }
        if (isset($param['parents'])) {
            $params['parents'] = $param['parents'];
        }
        $url = "https://www.googleapis.com/drive/v3/files?access_token=" . $access_token;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Authorization: Bearer " . $access_token, 'Accept: application/json', 'Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
        $data = curl_exec($ch);

        return json_decode($data, true);
    }

    public function copyFile($access_token, array $param)
    {
        $params = [
            'parent' => [$param['parent']],
        ];

        $url = "https://www.googleapis.com/drive/v3/files/{$param['fileId']}/copy?access_token=" . $access_token;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        // don't do ssl checks
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Authorization: Bearer " . $access_token, 'Accept: application/json', 'Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
        $data = curl_exec($ch);

        return json_decode($data, true);
    }

    public function updateFile($access_token, $param, $id)
    {

        $params = [
            'name' => $param['name'],
        ];

        $queryParam = [
            'addParents' => $param['parent']
        ];

        $url = "https://www.googleapis.com/drive/v3/files/{$id}?access_token=" . $access_token . "&" . http_build_query($queryParam);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        // don't do ssl checks
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Authorization: Bearer " . $access_token, 'Accept: application/json', 'Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
        $data = curl_exec($ch);

        return json_decode($data, true);
    }

    // File Management
    public function idToFile($access_token, $id)
    {
        $file = [];
        $fileMetaData = $this->getFile($access_token, $id);
        $fileContent = $this->getFileContent($access_token, $id);
        $file['string']['Id'] = $fileMetaData['id'];
        $file['string']['Name'] = $fileMetaData['name'];
        $file['string']['mimeType'] = $fileMetaData['mimeType'];
        $file['string']['Extension'] = $this->mimeToExt($fileMetaData['mimeType'], ($fileMetaData['fileExtension'] ?? 'txt'));
        $file['string']['Parents'] = $fileMetaData['parents'];

        $file['string']['Created Time'] = $fileMetaData['createdTime'];
        if (function_exists('mb_strlen')) {
            $size = mb_strlen($fileContent, '8bit');
        } else {
            $size = strlen($fileContent);
        }
        $file['string']['Size'] = $size;
        $file['file']['Attachment'][] = [
            'id' => $id,
            'name' => $fileMetaData['name'],
            'mimeType' => $fileMetaData['mimeType'],
            'Extension' => $this->mimeToExt($fileMetaData['mimeType'], ($fileMetaData['fileExtension'] ?? 'txt')),
            'size' => $size,
            'FileHandler' => 'GoogleDrive'
        ];
        return $file;
    }

    public function fileManagerInstance($accountId)
    {
        return [$this->getToken($accountId)];
    }

    public function setFile($files, $access)
    {
        if (isset($files['id'])) {
            $data = base64_encode($this->getFileContent($access[0], $files['id']));
            $file['size'] = $files['size'];
            $file['filename'] = $files['name'];
            $file['id'] = $files['id'];
            $file['mimeType'] = $files['mimeType'];
            $file['extension'] = $files['Extension'];
            $file['data'] = $data;
            $file['dataDecode'] = base64_decode($data);
        }
        return $file;
    }

    public function mimeToExt($mime, $or)
    {
        $mime_types = array(
            "xls" => 'application/vnd.ms-excel',
            "xlsx" => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            "xml" => 'text/xml',
            "ods" => 'application/vnd.oasis.opendocument.spreadsheet',
            "csv" => 'text/plain',
            "tmpl" => 'text/plain',
            "pdf" => 'application/pdf',
            "php" => 'application/x-httpd-php',
            "jpg" => 'image/jpeg',
            "png" => 'image/png',
            "gif" => 'image/gif',
            "bmp" => 'image/bmp',
            "txt" => 'text/plain',
            "doc" => 'application/msword',
            "js" => 'text/js',
            "swf" => 'application/x-shockwave-flash',
            "mp3" => 'audio/mpeg',
            "zip" => 'application/zip',
            "rar" => 'application/rar',
            "tar" => 'application/tar',
            "arj" => 'application/arj',
            "cab" => 'application/cab',
            "html" => 'text/html',
            "htm" => 'text/html',
            "default" => 'application/octet-stream',
            "folder" => 'application/vnd.google-apps.folder'
        );
        foreach ($mime_types as $key => $mimes) {
            if ($mimes == $mime) {
                return $key;
            }
        }

        return $or;
    }

}
