(function ($) {

    $(document).ready(function () {

        var form = (function () {
                var pluginNS = "hg-form", maxFiles, ajaxUrl, deleteFile, setProgress, addFile, isPopup,
                    getFileList, initForm, form, init, setMessage, submitForm, countFiles, getInfoString;
                setMessage = function (message, file) {
                    $("input, .progress", file).remove();
                    if (typeof message === "string") {
                        message = $("<span>", {
                            text: message
                        });
                    }
                    file.append(message);
                };
                getFileList = function () {
                    $.get(form.data("upload"), function (response) {
                        $.each(response.attachments, function (index, item) {
                            var wrapper = $(".file-wrapper", form).eq(0).clone()
                            $(".close", wrapper).attr("rel", item.deleteUrl);
                            setMessage(getInfoString(item), wrapper);
                            $("fieldset.files", form).append(wrapper);
                            wrapper.show();
                        });
                    }, "json");
                };
                addFile = function (e) {
                    if (countFiles() < maxFiles) {
                        var wrapper = $(".file-wrapper", form).eq(0).clone(),
                            input = $("input[name='attachments']", wrapper);
                        $("fieldset.files", form).append(wrapper);
                        wrapper.show();
                        input.fileupload({
                            url: form.data("upload"),
                            dataType: 'json',
                            replaceFileInput: false,
                            start: function (e, data) {
                                $(".progress", wrapper).show();
                            },
                            progress: function (e, data) {
                                var progress = parseInt(data.loaded / data.total * 100, 10);
                                setProgress(progress, $(".progress", wrapper));
                            },
                            done: function (e, data) {
                                $.each(data.result.attachments, function (index, item) {
                                    var result;
                                    if (!item.error) {
                                        $(".close", wrapper).attr("rel", item.deleteUrl);
                                        result = getInfoString(item);
                                    }
                                    else {
                                        result = $("<span>", {
                                            className: "error",
                                            html: item.error + ': <span class="filename">' + item.name + "</span>"
                                        });
                                    }
                                    setMessage(result, wrapper);
                                });
                            }
                        });
                    }
                    else {
                        $(this).prop("disabled", true);
                    }
                    e.preventDefault();
                };
                getInfoString = function (item) {
                    var size = (item.size / 1024).toFixed(2);
                    return $("<span>", {
                        html: "Файл: <b>" + item.name + "</b> (" + size + " KB)"
                    });
                };
                setProgress = function (percent, element) {
                    var barWidth = percent * element.width() / 100;
                    element.find('div').animate({width: barWidth}, 500).html(percent + "%&nbsp;");
                };
                countFiles = function () {
                    return $(".file-wrapper:visible", form).length;
                };
                deleteFile = function (url) {
                    $.ajax({
                        url: url,
                        dataType: "json",
                        type: "DELETE"
                    });
                };
                submitForm = function (e) {
                    var data = $(this).serializeArray(),
                        submit = $('input[type="submit"]', $(this)),
                        btnText = submit.val();
                    if ($(this).valid()) {
                        data.push({
                            name: "action",
                            value: pluginNS + "_send"
                        });
                        submit.prop("disabled", true);
                        submit.val("Отправка…");
                        $.post(ajaxUrl, data, function (response) {
                            var resultBlock = (isPopup)? $("#" + pluginNS + "-result", $('#fancybox-content')): $("#" + pluginNS + "-result");
                            submit.prop("disabled", false);
                            submit.val(btnText);
                            resultBlock.append($("<p>", {
                                text: response.msg
                            }));
                            form.hide("fast").remove();
                        }, "json");
                    }
                    e.preventDefault();
                };
                init = function () {
                    var buttons = $('.' + pluginNS + '-button');
                    if (buttons.length) {
                        isPopup = true;
                        buttons.on('click', function (e) {
                            $.fancybox($('#' + pluginNS + '-popup').html(), {
                                onComplete: initForm
                            });
                            e.preventDefault();
                        });
                    }
                    else {
                        initForm();
                    }
                };
                initForm = function () {
                    form = (isPopup)? $('form', $('#fancybox-content')): $('#' + pluginNS);
                    maxFiles = form.data("maxfiles");
                    ajaxUrl = form.attr('action');
                    if (ajaxUrl === undefined) {
                        console.log("Ajax handler url is empty");
                    }
                    getFileList();
                    $("input[name='phone']", form).mask("+7 (999) 999-99-99", {
                        placeholder: " "
                    });
                    form.validate({
                        rules: {
                            email: {
                                required: true,
                                email: true
                            },
                            description: {
                                required: true
                            }
                        },
                        messages: {
                            email: {
                                required: "Поле e-mail необходимо заполнить",
                                email: "Введён некорректный e-mail"
                            },
                            description: {
                                required: "Необходимо заполнить текст заявки"
                            }
                        }
                    });
                    form.on("submit", submitForm);
                    $(".add-file", form).on("click", addFile);
                    $(".file-wrapper .close", form).live("click", function (e) {
                        deleteFile($(this).attr("rel"));
                        $(this).parents(".file-wrapper").remove();
                        if (countFiles() < maxFiles) {
                            $(".add-file", form).prop("disabled", false);
                        }
                        e.preventDefault();
                    });
                };
                return {
                    init: init
                };
            }) ();

        form.init();

    });

} (jQuery));