<?php

    /**
     * @author Gennadiy Hatuntsev <e.steelcat@gmail.com>
     * @package hg-order-form
     */

    require_once $_SERVER["DOCUMENT_ROOT"].DIRECTORY_SEPARATOR."wp-load.php";
    require_once "inc".DIRECTORY_SEPARATOR."UploadHandler.php";

    $max = intval(get_option("hg-form_maxfiles"));
    $size = intval(get_option("hg-form_maxsize"));

    $options = array(
        "script_url" => $_SERVER["SCRIPT_NAME"],
        "upload_dir" => dirname(__FILE__).DIRECTORY_SEPARATOR.'files'.DIRECTORY_SEPARATOR,
        "param_name" => "attachments",
        "user_dirs" => true,
        "access_control_allow_methods" => array(
            "POST", "DELETE"
        ),
        "image_versions" => array(),
        "max_file_size" => $size * 1024 * 1024,
        "max_number_of_files" => $max
    );


    $messages = array(
        'max_file_size' => 'Файл имеет большой размер',
        'max_number_of_files' => 'Прикрепить можно не более '.$max." "._n('файл', 'файлов', $max)
    );

    $upload = new UploadHandler($options, true, $messages);