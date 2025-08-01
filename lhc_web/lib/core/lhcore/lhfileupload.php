<?php

class erLhcoreClassFileUpload extends UploadHandler
{

    public $uploadedFile = false;

    protected function get_file_name($name, $type = null, $index = null, $content_range = null)
    {
        $name = sha1($name . erLhcoreClassModelForgotPassword::randomPassword(40) . time());
        return md5($this->get_unique_filename(
            $this->trim_file_name($name, $type, $index, $content_range),
            $type,
            $index,
            $content_range
        ));
    }

    protected function generate_response($content, $print_response = true)
    {
        parent::generate_response($content, false);
    }

    protected function handle_file_upload_parent($uploaded_file, $name, $size, $type, $error, $index, $content_range)
    {
        return parent::handle_file_upload(
            $uploaded_file, $name, $size, $type, $error, $index, $content_range
        );
    }

    protected function handle_file_upload($uploaded_file, $name, $size, $type, $error, $index = null, $content_range = null)
    {

        $matches = array();
        if (strpos($name, '.') === false && preg_match('/^image\/(gif|jpe?g|png)/', $type, $matches)) {
            $name = $uploadFileName = 'clipboard.' . $matches[1];
        } else {
            $uploadFileName = $name;
        }

        $file = parent::handle_file_upload(
            $uploaded_file, $name, $size, $type, $error, $index, $content_range
        );

        if (!preg_match($this->options['accept_file_types_lhc'], $uploadFileName)) {
            $file->error = $this->get_error_message('accept_file_types');
        }

        if (empty($file->error) && isset($this->options['antivirus']) && $this->options['antivirus'] !== false && is_object($this->options['antivirus']) && !$this->options['antivirus']->scan(realpath($this->options['upload_dir'] . $file->name))) {
            unlink($this->options['upload_dir'] . $file->name);
            erLhcoreClassFileUpload::removeRecursiveIfEmpty('var/', str_replace('var/', '', $this->options['upload_dir']));
            $file->error = 'Virus found in file!';
        }

        if (empty($file->error)) {
            $fileUpload = new erLhcoreClassModelChatFile();
            $fileUpload->size = $file->size;
            $fileUpload->type = $file->type;
            $fileUpload->name = $file->name;
            $fileUpload->date = time();
            $fileUpload->user_id = isset($this->options['user_id']) ? $this->options['user_id'] : 0;
            $fileUpload->upload_name = $name;
            $fileUpload->file_path = $this->options['upload_dir'];

            if (isset($this->options['chat']) && $this->options['chat'] instanceof erLhcoreClassModelChat) {
                $fileUpload->chat_id = $this->options['chat']->id;
            } elseif (isset($this->options['online_user']) && $this->options['online_user'] instanceof erLhcoreClassModelChatOnlineUser) {
                $fileUpload->online_user_id = $this->options['online_user']->id;
            }

            $matches = array();
            if (strpos($name, '.') === false && preg_match('/^image\/(gif|jpe?g|png)/', $fileUpload->type, $matches)) {
                $fileUpload->extension = strtolower($matches[1]);
            } else {
                $partsFile = explode('.', $fileUpload->upload_name);
                $fileUpload->extension = strtolower(end($partsFile));
            }

            if ($fileUpload->extension == 'svg') {
                erLhcoreClassFileUploadAdmin::cleanSVG($fileUpload->file_path_server);
                $file->size = $fileUpload->size = filesize($fileUpload->file_path_server);
            }

            if (isset($this->options['remove_meta']) && $this->options['remove_meta'] == true && in_array($fileUpload->extension, array('jfif','jpg', 'jpeg', 'png', 'gif'))) {
                erLhcoreClassFileUploadAdmin::removeExif($fileUpload->file_path_server, $fileUpload->file_path_server . '_exif');
                unlink($fileUpload->file_path_server);
                rename($fileUpload->file_path_server . '_exif', $fileUpload->file_path_server);
                $fileUpload->size = filesize($fileUpload->file_path_server);
            }

            if (isset($this->options['max_res']) && $this->options['max_res'] > 0 && in_array($fileUpload->extension, array('jfif','jpg', 'jpeg', 'png', 'gif'))) {
                $imageSize = getimagesize($fileUpload->file_path_server);
                if ($imageSize !== false && ($imageSize[0] > $this->options['max_res'] || $imageSize[1] > $this->options['max_res'])) {
                    $conversionSettings[] = new ezcImageHandlerSettings( 'gd','erLhcoreClassGalleryGDHandler' );
                    $converter = new ezcImageConverter(
                        new ezcImageConverterSettings(
                            $conversionSettings
                        )
                    );
                    $converter->createTransformation(
                        'fitimage',
                        array(
                            new ezcImageFilter(
                                'scale',
                                array(
                                    'width'     => $this->options['max_res'],
                                    'height'    => $this->options['max_res']
                                )
                            ),
                        ),
                        array(
                            'image/jpeg'
                        ),
                        new ezcImageSaveOptions(array('quality' => (int)95))
                    );
                    $converter->transform('fitimage', $fileUpload->file_path_server, $fileUpload->file_path_server);
                    $fileUpload->size = filesize($fileUpload->file_path_server);
                    $fileUpload->type = 'image/jpeg';
                    $fileUpload->extension = 'jpg';
                }
            }

            // Set resolution instantly
            if (in_array($fileUpload->extension, array('jfif','jpg', 'jpeg', 'png', 'gif'))) {
                $imageSize = getimagesize($fileUpload->file_path_server);
                if ($imageSize !== false && ($imageSize[0] > 10 || $imageSize[1] > 10)) {
                    $fileUpload->width = (int)$imageSize[0];
                    $fileUpload->height = (int)$imageSize[1];
                }
            }

            $fileUpload->saveThis();

            $file->id = $fileUpload->id;

            if (isset($this->options['chat']) && $this->options['chat'] instanceof erLhcoreClassModelChat) {
                // Chat assign
                $chat = $this->options['chat'];

                // Format message only if preview is not enabled
                if (!isset($this->options['file_preview']) || $this->options['file_preview'] != true || $fileUpload->extension == 'mp3') {
                    $msg = new erLhcoreClassModelmsg();
                    $msg->msg = '[file=' . $file->id . '_' . $fileUpload->security_hash . ']';
                    $msg->chat_id = $chat->id;
                    $msg->user_id = isset($this->options['user_id']) ? $this->options['user_id'] : 0;

                    // We save instantly as message only visitors files
                    if ($msg->user_id == 0 || (isset($this->options['as_form']) && $this->options['as_form'] == true)) {

                        $chat->last_user_msg_time = $msg->time = time();

                        erLhcoreClassChat::getSession()->save($msg);

                        // Set last message ID
                        if ($chat->last_msg_id < $msg->id) {
                            $chat->last_msg_id = $msg->id;
                        }

                        if ($msg->user_id == 0) {
                            if ($chat->gbot_id > 0 && (!isset($chat->chat_variables_array['gbot_disabled']) || $chat->chat_variables_array['gbot_disabled'] == 0)) {
                                erLhcoreClassGenericBotWorkflow::userMessageAdded($chat, $msg);
                            }
                            $chat->has_unread_messages = 1;
                        }

                        $chat->updateThis(array('update' => array('last_user_msg_time','last_msg_id','has_unread_messages')));

                        erLhcoreClassChatEventDispatcher::getInstance()->dispatch('chat.addmsguser',array('files' => [$fileUpload], 'msg' => & $msg, 'chat' => & $chat));
                    }
                } else {
                    $fileUpload->tmp = 1;
                    $fileUpload->updateThis(array('update' => array('tmp')));
                }
            }

            $this->uploadedFile = $fileUpload;
        } else {
            $this->uploadedFile = $file;
        }

        return $file;
    }

    public function delete($print_response = true)
    {
        return false;
    }

    public static function mkdirRecursive($path, $chown = false, $wwwUser = 'apache', $wwwUserGroup = 'apache')
    {
        $partsPath = explode('/', $path);
        $pathCurrent = '';

        foreach ($partsPath as $key => $path) {
            $pathCurrent .= $path . '/';
            if (!is_dir($pathCurrent)) {
                mkdir($pathCurrent, 0755);
                if ($chown == true) {
                    chown($pathCurrent, $wwwUser);
                    chgrp($pathCurrent, $wwwUserGroup);
                }
            }
        }
    }

    public static function hasFiles($sourceDir)
    {
        if (!is_dir($sourceDir)) {
            return true;
        }

        $elements = array();
        $d = @dir($sourceDir);
        if (!$d) {
            return true;
        }

        while (($entry = $d->read()) !== false) {
            if ($entry == '.' || $entry == '..') {
                continue;
            }

            return true;
        }

        return false;
    }

    public static function removeRecursiveIfEmpty($basePath, $removePath)
    {
        $removePath = trim($removePath, '/');
        $partsRemove = explode('/', $removePath);

        $pathElementsCount = count($partsRemove);
        foreach ($partsRemove as $part) {
            // We found some files/folders, so we have to exit
            if (self::hasFiles($basePath . implode('/', $partsRemove)) === true) {
                return;
            } else {
                //Folder is empty, delete this folder
                @rmdir($basePath . implode('/', $partsRemove));
            }
            array_pop($partsRemove);
        }
    }
}

?>