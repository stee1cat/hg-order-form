<div id="<?php echo $this->ns("wrapper"); ?>">
<?php
    /**
     * @var $this HGOrderForm
     */

    if ($title = $this->getOption("title")) {
?>
    <span class="<?php echo $this->ns("title"); ?>"><?php echo $title; ?></span>
<?php
    }
    $maxFiles = $this->getOption("maxfiles");
?>
    <form id="<?php echo $this->ns(); ?>" action="<?php echo admin_url("admin-ajax.php"); ?>" method="post"
          enctype="multipart/form-data"
          data-upload="<?php echo $this->pluginUrl; ?>upload.php"
          data-maxfiles="<?php echo $maxFiles; ?>">
        <ul>
            <li>
                <i>Поля отмеченные (*) &mdash; обязательны для заполнения.</i>
            </li>
            <li>
                <label>Ваше имя:</label>
                <input type="text" name="name" value="">
            </li>
            <li>
                <label>Ваш e-mail *:</label>
                <input id="<?php echo $this->ns("email"); ?>" type="text" name="email" value="">
            </li>
            <li>
                <label>Ваш номер телефона:</label>
                <input id="<?php echo $this->ns("phone"); ?>" type="text" name="phone" value="">
            </li>
            <li>
                <label>Комментарии и пожелания *:</label>
                <textarea id="<?php echo $this->ns("description"); ?>" name="description" rows="4"></textarea>
            </li>
<?php
    if ($maxFiles) {
?>
            <li>
                <fieldset class="files">
                    <legend>Прикрепить файлы:</legend>
                    <p><i>Вы можете добавить не более <?php echo $maxFiles." "._n('файла', 'файлов', $maxFiles); ?>. Размер каждого файла не должен привышать 2 МБ.</i></p>
                    <div class="file-actions">
                        <button class="add-file">Добавить файл</button>
                    </div>
                    <div class="file-wrapper" style="display: none;">
                        <span class="close" title="Удалить">x</span>
                        <input class="<?php echo $this->ns("file"); ?>" type="file" name="attachments">
                        <div class="progress" style="display: none;">
                            <div></div>
                        </div>
                    </div>
                </fieldset>
            </li>
<?php
    }
?>
            <li>
                <input type="submit" value="Отправить">
            </li>
        </ul>
    </form>
    <div id="<?php echo $this->ns("result"); ?>"></div>
</div>