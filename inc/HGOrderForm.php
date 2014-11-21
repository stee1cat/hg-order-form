<?php

    /**
     * @author Gennadiy Hatuntsev <e.steelcat@gmail.com>
     * @package hg-order-form
     */

    class HGOrderForm {

        private $uploadDir = "";
        private $pluginDir = "";
        private $pluginUrl = "";
        private $needScript = false;
        private static $pluginName = "hg-form";
        private static $shortcode = "hg-order";

        public function __construct() {
            $this->pluginDir = dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR;
            $this->uploadDir = $this->pluginDir.'files'.DIRECTORY_SEPARATOR;
            $this->pluginUrl = plugin_dir_url(dirname(__FILE__));
            $this->init();
            $this->adminInit();
        }

        /**
         * Shortcode
         *
         * @param $atts
         * @return string
         */
        public function shortcode($atts) {
            $this->needScript = true;
            if (is_array($atts)) {
                extract($atts, EXTR_SKIP);
            }
            require $this->pluginDir."inc".DIRECTORY_SEPARATOR."form.php";
        }

        /**
         * Обработка формы
         *
         */
        public function submit() {
            if ($this->validate()) {
                $result = array(
                    "msg" => "Ваш заказ принят, наш менеджер свяжется с вами в ближайшее время.",
                    "type" => true
                );
                if (!$this->send()) {
                    $result = array(
                        "msg" => "Извините, произошла ошибка при отправке заявки. Побробуйте позднее.",
                        "type" => false
                    );
                }
            }
            else {
                $result = array(
                    "msg" => "Заполнены не все поля.",
                    "type" => false
                );
            }
            $this->removeUserDir();
            echo json_encode($result);
            exit;
        }

        /**
         * Меню в панели управления
         *
         */
        public function menu() {
            add_options_page("Отправка заявки", "Форма заказа", "manage_options", self::$pluginName."-menu", array($this, "options"));
        }

        /**
         * Страница настроек
         *
         */
        public function options() {
            include_once $this->pluginDir."inc".DIRECTORY_SEPARATOR."options.php";
        }

        /**
         * Регистрация настроек
         *
         */
        public function registerSettings() {
            register_setting(self::$pluginName."-options", self::$pluginName."_title", "trim");
            register_setting(self::$pluginName."-options", self::$pluginName."_recipient");
            register_setting(self::$pluginName."-options", self::$pluginName."_maxfiles", "intval");
            register_setting(self::$pluginName."-options", self::$pluginName."_maxsize", "intval");
        }

        /**
         * Подключение JS
         *
         */
        public function enqueueJS() {
            if ($this->needScript) {
                wp_enqueue_script("maskedinput");
                wp_enqueue_script("validate");
                wp_enqueue_script("ui-widget");
                wp_enqueue_script("fileupload");
                wp_enqueue_script(self::$pluginName."script");
            }
        }

        /**
         * Подключение CSS
         *
         */
        public function enqueueCSS() {
            if ($this->needScript) {
                wp_enqueue_style(self::$pluginName."style");
            }
        }

        /**
         * Регистрация JS
         *
         */
        private function registerJS() {
            wp_register_script("maskedinput", $this->pluginUrl."js/vendor/jquery.maskedinput.min.js", array("jquery"), false, true);
            wp_register_script("validate", $this->pluginUrl."js/vendor/jquery.validate.min.js", array("jquery"), false, true);
            wp_register_script("ui-widget", $this->pluginUrl."js/vendor/jquery.ui.widget.min.js", array("jquery"), false, true);
            wp_register_script("fileupload", $this->pluginUrl."js/vendor/jquery.fileupload.min.js", array("jquery", "ui-widget"), false, true);
            wp_register_script(self::$pluginName."script", $this->pluginUrl."js/script.js", array("jquery", "maskedinput", "validate", "fileupload"), false, true);
        }

        /**
         * Регистрация CSS
         *
         */
        private function registerCSS() {
            wp_register_style(self::$pluginName."style", $this->pluginUrl."css/style.css", array());
        }

        /**
         * Инициализация
         *
         */
        private function init() {
            add_action("wp_ajax_".self::$pluginName."_send", array($this, "submit"));
            add_action("wp_ajax_nopriv_".self::$pluginName."_send", array($this, "submit"));
            add_action("wp_footer", array($this, "enqueueJS"));
            add_action("wp_footer", array($this, "enqueueCSS"));
            add_shortcode(self::$shortcode, array($this, "shortcode"));
            $this->registerJS();
            $this->registerCSS();
        }

        /**
         * Инициализация административной части
         *
         */
        private function adminInit() {
            if (is_admin()) {
                add_action("admin_menu", array($this, "menu"));
                add_action("admin_init", array($this, "registerSettings"));
            }
        }

        /**
         * Отправка письма
         *
         * @return bool
         */
        private function send() {
            $result = false;
            $emailTo = $this->getOption("recipient");
            if (is_email($emailTo)) {
                require_once $_SERVER['DOCUMENT_ROOT']."/wp-includes/class-phpmailer.php";
                require_once $_SERVER['DOCUMENT_ROOT']."/wp-includes/class-smtp.php";
                $name = (isset($_POST["name"]))? htmlspecialchars(trim($_POST["name"])): "-";
                $email = (isset($_POST["email"]))? htmlspecialchars(trim($_POST["email"])): "-";
                $phone = (isset($_POST["phone"]))? htmlspecialchars(trim($_POST["phone"])): "-";
                $description = (isset($_POST["description"]))? htmlspecialchars(trim($_POST["description"])): "-";
                $message =
<<<HTML
    <p>Поступила новая заявка:</p>
    <ul>
        <li><b>Имя:</b> {$name}</li>
        <li><b>E-mail:</b> {$email}</li>
        <li><b>Телефон:</b> {$phone}</li>
        <li><b>Комментарий:</b> {$description}</li>
    </ul>
HTML;
                $mail = new PHPMailer();
                $mail->CharSet = "UTF-8";
                $mail->isHTML(true);
                $mail->SetFrom("noreply@".$this->getHost());
                $mail->Subject = get_bloginfo("name")." - новая заявка";
                $mail->AddAddress($emailTo);
                $mail->MsgHTML($message);
                $files = $this->getFiles();
                foreach ($files as $file) {
                    $mail->addAttachment($file);
                }
                $result = $mail->Send();
            }
            return $result;
        }


        /**
         * Проверка данных
         *
         * @return bool
         */
        private function validate() {
            $email = (isset($_POST["email"]))? trim($_POST["email"]): "";
            $description = (isset($_POST["description"]))? trim($_POST["description"]): "";
            $error = false;
            if (!is_email($email)) {
                $error = true;
            }
            if (mb_strlen($description, "UTF-8") < 4) {
                $error = true;
            }
            return !$error;
        }

        /**
         * Возвращает имя класса или ID элемента с префиксом плагина
         *
         * @param string $string
         * @return string
         */
        private function ns($string = "") {
            if ($string) {
                $string = "-".rtrim($string, "-");
            }
            return self::$pluginName.$string;
        }

        /**
         * Возвращает список файлов загруженных пользователем
         *
         * @return array
         */
        private function getFiles() {
            $result = array();
            $directory = $this->getUserDir();
            if (is_dir($directory)) {
                $list = scandir($directory);
                foreach ($list as $file) {
                    if (!in_array($file, array(".", ".."))) {
                        $result[] = $directory.$file;
                    }
                }                
            }
            return array_slice($result, 0, $this->getOption("maxfiles"));
        }

        /**
         * Возвращает абсолютный путь к директории с файлами пользователя
         *
         * @return string
         */
        private function getUserDir() {
            @session_start();
            return $this->uploadDir.session_id().DIRECTORY_SEPARATOR;
        }

        /**
         * Возвращает значение параметра по его имени
         *
         * @param string $name
         * @return mixed
         */
        private function getOption($name) {
            return get_option(self::$pluginName."_".ltrim($name, "_"));
        }

        /**
         * Возвращает имя хоста
         *
         * @return string
         */
        private function getHost() {
            return preg_replace("/^w{3}\./iu", "", $_SERVER["SERVER_NAME"]);
        }

        /**
         * Рекурсивно удаляет файлы из директории пользователя
         *
         * @return bool
         */
        private function removeUserDir() {
            $path = $this->getUserDir();
            if (file_exists($path) && rtrim($path, DIRECTORY_SEPARATOR) != rtrim($this->uploadDir, DIRECTORY_SEPARATOR)) {
                $directory = new RecursiveDirectoryIterator($path);
                $files = new RecursiveIteratorIterator($directory, RecursiveIteratorIterator::CHILD_FIRST);
                foreach ($files as $file) {
                    if ($file->getFilename() === "." || $file->getFilename() === "..") {
                        continue;
                    }
                    $realpath = $file->getRealPath();
                    if ($file->isDir()) {
                        rmdir($realpath);
                    }
                    else {
                        unlink($realpath);
                    }
                }
                rmdir($path);
            }
            return !is_dir($path);
        }

    }
