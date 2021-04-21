<?php
require_once __DIR__ . DIRECTORY_SEPARATOR . "data_helpers" . DIRECTORY_SEPARATOR . "DataGetter.php";
require_once __DIR__ . DIRECTORY_SEPARATOR . "db" . DIRECTORY_SEPARATOR . "connect.php";
require_once __DIR__ . DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "autoload.php";

use DataHelpers\DataGetter;
use \RedBeanPHP\R as R;

$ligs = DataGetter::get_ligs();
$brokers = DataGetter::get_brokers()
?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Парсер oddsportal</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css"
          integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
</head>
<body>
<div class="container" style="margin-top: 50px">
    <div class="form-group">
        <label for="exampleInputEmail1">Выбрать лигу</label>
        <div class="row">
            <div class="col-md-6">
                <select class="form-control" id="ligsSelect">
                    <?php
                    foreach ($ligs as $lig):
                        ?>
                        <option value="<?= $lig["url"] ?>"><?= $lig["name"]; ?> (<?= $lig["country"]; ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6">
                <button class="btn btn-secondary" onclick="getLigs();" id="buttonGetLigs">Перепарсить лиги</button>
            </div>
        </div>
    </div>
    <div class="form-group">
        <label for="exampleInputEmail1">Или спарсить по ссылке</label>
        <input class="form-control" type="text" id="urlInput">
    </div>
    <div class="row">
        <?php for ($i = 1; $i <= 5; $i++): ?>
            <div class="form-group col-md-6" style="margin-top: 20px">
                <label for="exampleInputEmail1">Букмекер <?= $i ?></label><br>
                <select class="form-control" id="brokersSelect<?= $i ?>">
                    <option disabled selected>Выберите букмекера</option>
                    <?php
                    foreach ($brokers as $broker):
                        ?>
                        <option><?= $broker; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        <?php endfor; ?>
        <div class="form-group col-md-6" style="padding-top: 52px">
            <button class="btn btn-secondary" data-toggle="modal" data-target="#brokerModal">Перепарсить букмекеров
            </button>
        </div>
    </div>

    <div style="margin: 50px 0" id="stats">
        <?php
        if (R::count("record", "status=0") > 0):
            ?>
            <h3>Скоро появится статистика по текущему парсингу</h3>
        <?php endif; ?>
    </div>

    <div class="form-group" style="display:flex; justify-content:flex-end; margin-top: 50px">

        <?php
        if (R::count("record", "status=0") > 0) $show_process = "block";
        else $show_process = "none";
        ?>
        <div class="text-right" style="margin-right: 10px; display: none">
            <button class="btn btn-warning" onclick="parseAdd();" id="buttonParseAdd">Добавить потоки</button>
        </div>
        <div class="text-right" style="margin-right: 10px; display: none">
            <button class="btn btn-danger" onclick="stop();" id="buttonStop">Остановить парсинг</button>
        </div>
        <div class="text-right" style="margin-right: 10px">
            <button class="btn btn-warning" onclick="parseOld();" id="buttonParseOld">Продолжить текущий парсинг (или
                перезапустить потоки)
            </button>
        </div>
        <div class="text-right">
            <button class="btn btn-success" onclick="parseNew();" id="buttonParseNew">Начать новый парсинг</button>
        </div>
    </div>
    <div class="form-group" style="display:flex; justify-content:flex-end; margin-top: 20px">
        <div class="text-right">
            <button class="btn btn-link" onclick="makeResult()" id="makeResult">Создать xlsx из загруженных</button>
        </div>
        <?php
        $result_path = \Helpers\FileHandler::get_output_dir() . DIRECTORY_SEPARATOR . "export.xlsx";
        if (R::count("record", "status=0") == 0 && file_exists($result_path)) $show_result = "block";
        else $show_result = "none";
        ?>
        <div class="text-right" style="margin-right: 10px; display: <?= $show_result ?>">
            <a class="btn btn-primary" href="/output/export.xlsx" target="_blank" id="resultLink">Скачать результат</a>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="brokerModal" tabindex="-1" role="dialog" aria-labelledby="brokerModal" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLongTitle">Перепарсить букмекеров</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="exampleInputEmail1">Ссылка на матч, откуда нужно взять букмекеров</label>
                    <input class="form-control" type="text" id="urlBrokerInput">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-primary" onclick="getBrokers();" id="buttonGetBrokers">Спарсить
                </button>
            </div>
        </div>
    </div>
</div>

<script
        src="https://code.jquery.com/jquery-3.4.1.min.js"
        integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo="
        crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"
        integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1"
        crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"
        integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM"
        crossorigin="anonymous"></script>

<script>
    var loader = "<div class=\"spinner-border spinner-border-sm\" role=\"status\">\n <span class=\"sr-only\">Loading...</span>\n</div>";

    function getLigs() {
        var button_text = $("#buttonGetLigs").text();
        $("#buttonGetLigs").html(loader);
        $.ajax({
            url: "/route/parse-ligs.php",
            success: function (result) {
                $("#buttonGetLigs").html(button_text);
                result = JSON.parse(result);
                if (result.result == "success") {
                    $("#ligsSelect").html(create_ul_ligs(result.ligs));
                    alert("Успешно");
                }
                else {
                    alert("Во время парсинга произошла ошибка");
                }


            }
        });
    }

    function create_ul_ligs(ligs) {
        var ul = "";
        for (var i = 0; i < ligs.length; i++) ul += "<option>" + ligs[i]["name"] + " (" + ligs[i]["country"] + ")</option>"
        return ul;
    }

    function getBrokers() {
        var url = $("#urlBrokerInput").val();
        if (url == "") {
            alert("Нужно задать ссылку на парсинг");
            return false;
        }
        var button_text = $("#buttonGetBrokers").text();
        $("#buttonGetBrokers").html(loader);
        $.ajax({
            url: "/route/parse-brokers.php?url=" + encodeURIComponent(url),
            success: function (result) {
                $("#buttonGetBrokers").html(button_text);
                result = JSON.parse(result);
                if (result.result == "success") {
                    for (var i = 1; i <= 5; i++) {
                        $("#brokersSelect" + i).html(create_ul_brokers(result.brokers));
                    }

                    alert("Успешно");
                    $('#brokerModal').modal('hide')
                }
                else {
                    alert("Во время парсинга произошла ошибка");
                }


            }
        });
    }

    function create_ul_brokers(brokers) {
        var ul = "<option disabled selected>Выберите букмекера</option>";
        for (var i = 0; i < brokers.length; i++) ul += "<option>" + brokers[i] + "</option>"
        return ul;
    }

    function parseOld() {
        var button_text = $("#buttonParseOld").text();
        $("#buttonParseOld").html(loader);
        $.ajax({
            url: "/route/parse.php?old=1",
            success: function (result) {
                $("#buttonParseOld").html(button_text);
                result = JSON.parse(result);
                if (result.result == "success") {
                    alert("Парсинг продолжен.");
                }
                else {
                    alert("Во время парсинга произошла ошибка");
                }

            }
        });
    }

    function parseAdd() {
        var button_text = $("#buttonParseAdd").text();
        $("#buttonParseAdd").html(loader);
        $.ajax({
            url: "/route/parse-add.php",
            success: function (result) {
                $("#buttonParseAdd").html(button_text);
                result = JSON.parse(result);
                if (result.result == "success") {
                    alert("Дополнительные потоки включены.");
                }
                else {
                    alert("Во время включения произошла ошибка");
                }

            }
        });
    }

    function parseNew() {
        $("#resultLink").parent().hide();
        $("#buttonStop").parent().hide();
        $("#buttonParseOld").parent().hide();
        var url = $("#urlInput").val();
        var otherYears = 0;
        if (url == "") {
            url = $("#ligsSelect option:selected").val();
            otherYears = 1;
        }
        var brokers = {};
        for (var i = 1; i <= 5; i++) {
            var option_name = $("#brokersSelect" + i + " option:selected").text();
            if (option_name !== "Выберите букмекера") brokers[option_name] = i;

        }
        brokers = JSON.stringify(brokers);
        var button_text = $("#buttonParseNew").text();
        $("#buttonParseNew").html(loader);
        $.ajax({
            url: "/route/parse.php?url=" + encodeURIComponent(url) + "&brokers=" + encodeURIComponent(brokers) + "&years=" + otherYears + "&old=0",
            success: function (result) {
                $("#buttonParseNew").html(button_text);
                result = JSON.parse(result);
                if (result.result == "success") {
                    alert("Парсинг начат.");
                    $("#buttonStop").parent().show();
                    $("#buttonParseOld").parent().show();
                }
                else {
                    alert("Во время парсинга произошла ошибка");
                }

            }
        });
    }

    function make_export() {
        $.ajax({
            url: "/route/export.php",
            success: function (result) {
                result = JSON.parse(result);
                if (result.result == "success") {
                    $("#resultLink").parent().show();
                }
                else {
                    alert("Во время создания файла результатов произошла ошибка");
                }
            }
        });
    }

    function get_stats() {
        $.ajax({
            url: "/route/stats.php",
            success: function (result) {
                result = JSON.parse(result);
                var stats = "";
                if (!result.process) {
                    stats += "<h3>Парсинг не запущен</h3>";
                    $("#buttonStop").parent().hide();
                }
                else if (result.processCount == 0) {
                    stats += "<h3>Парсинг остановлен</h3>";
                    $("#buttonStop").parent().hide();
                    $("#buttonParseAdd").parent().hide();
                }
                else {
                    if (result.now == result.all + result.error) {
                        stats += "<h3>Парсинг окончен</h3>";
                        $("#buttonStop").parent().hide();
                        $("#buttonParseOld").parent().hide();
                        $("#buttonParseAdd").parent().hide();
                    }
                    else {
                        stats += "<h3>Парсинг запущен</h3>";
                        $("#buttonStop").parent().show();
                        $("#buttonParseOld").parent().show();
                        $("#buttonParseAdd").parent().show();
                    }


                    if (result.now == result.all + result.error) {
                        make_export();
                    }
                }
                stats += "<p><b>Потоков работает</b>: " + result.processCount + "</p>";
                stats += "<p><b>Всего страниц</b>: " + result.all + "</p>";
                stats += "<p><b>Успешно обработано</b>: " + result.now + "</p>";
                var width_p = Math.round(result.now * 100 / result.all);
                var width_error = Math.round(result.error * 100 / result.all);
                stats += "<div class=\"progress\"> \
                    <div class=\"progress-bar progress-bar-striped bg-warning\" style=\"width: " + width_error + "%\" role=\"progressbar\"aria-valuenow=\"" + result.error + "\" aria-valuemin=\"0\" aria-valuemax=\"" + result.all + "\"></div>\
                    <div class=\"progress-bar progress-bar-striped bg-success\" style=\"width: " + width_p + "%\" role=\"progressbar\"aria-valuenow=\"" + result.now + "\" aria-valuemin=\"0\" aria-valuemax=\"" + result.all + "\"></div></div>";
                $("#stats").html(stats);
            }
        });
    }

    function stop() {
        var button_text = $("#buttonStop").text();
        $("#buttonStop").html(loader);
        $.ajax({
            url: "/route/stop.php",
            success: function (result) {
                $("#buttonStop").html(button_text);
                result = JSON.parse(result);
                if (result.result == "success") {
                    alert("Парсинг остановлен.");
                }
                else {
                    alert("Во время выполнения команды произошла ошибка");
                }

            }
        });
    }

    function makeResult() {
        var button_text = $("#makeResult").text();
        $("#makeResult").html(loader);
        $.ajax({
            url: "route/export-force.php",
            success: function (result) {
                $("#makeResult").html(button_text);
                result = JSON.parse(result);
                if (result.result == "success") {
                    alert("Документ готов.");
                    $("#resultLink").parent().show();
                }
                else {
                    alert("Во время выполнения команды произошла ошибка");
                }

            }
        });
    }

    var timerId = setInterval(get_stats, 5000);
</script>
</body>
</html>
