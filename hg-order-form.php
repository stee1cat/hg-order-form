<?php

/*
    Plugin Name: Order Form
    Description: Форма для отправки заказа
    Author: Gennadiy Hatuntsev <e.steelcat@gmail.com>
    Version: 1.0
    Author URI: http://fuget.ru/
*/

    $pluginDir = dirname(__FILE__).DIRECTORY_SEPARATOR;
    require_once $pluginDir.DIRECTORY_SEPARATOR."inc".DIRECTORY_SEPARATOR."HGOrderForm.php";

    $form = new HGOrderForm();
