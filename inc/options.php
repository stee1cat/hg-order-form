<?php
    /**
     * @var $this HGOrderForm
     */
?>

<div class="wrap">
    <h2>Настройки формы заказа</h2>
    <form method="post" action="options.php">
<?php
    settings_fields(self::$pluginName."-options");
    do_settings_sections(self::$pluginName."-options");
?>
        <table class="form-table">
            <tr valign="top">
                <th scope="row">Заголовок формы:</th>
                <td><input type="text" name="<?php echo self::$pluginName; ?>_title" value="<?php echo $this->getOption("title"); ?>" /></td>
            </tr>
            <tr valign="top">
                <th scope="row">E-mail получателя:</th>
                <td><input type="text" name="<?php echo self::$pluginName; ?>_recipient" value="<?php echo $this->getOption("recipient"); ?>" /></td>
            </tr>
            <tr valign="top">
                <th scope="row">Максимальное число файлов во вложении:</th>
                <td>
                    <select name="<?php echo self::$pluginName; ?>_maxfiles">
<?php
    $maxFiles = $this->getOption("maxfiles");
    foreach (range(0, 6) as $number) {
?>
                        <option value="<?php echo $number; ?>" <?php selected($maxFiles, $number); ?>><?php echo $number; ?></option>
<?php
    }
?>
                    </select>
                </td>
            </tr>
        </table>
        <?php submit_button(); ?>
    </form>
    <p>Доступны коды <b>[<?php echo self::$shortcode; ?>]</b>, <b>[<?php echo self::$shortcodePopup; ?>]</b> для вставки на страницу.</p>
</div>
