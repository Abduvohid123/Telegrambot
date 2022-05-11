<?php


require_once __DIR__ . '/vendor/autoload.php';
include 'Database.php';
$baza = new Database();

$connect = $baza->conn;

$botToken = "5116267217:AAGAiLf8oXEWD98JncIuvnCVXpn1gNupJQw";

// https://api.telegram.org/bot5116267217:AAGAiLf8oXEWD98JncIuvnCVXpn1gNupJQw/setWebhook?url=https://806c-185-139-138-86.ngrok.io/bot/TelegramBot/quron_bot/index.php

/**
 * @var $bot \TelegramBot\Api\Client | \TelegramBot\Api\BotApi
 */
$bot = new \TelegramBot\Api\Client($botToken);

include 'funksiyalar.php';

$bot->command('start', static function (\TelegramBot\Api\Types\Message $message) use ($bot) {
    try {
        $chatId = $message->getChat()->getId();
        $firstname = $message->getChat()->getFirstName();
        $count = $GLOBALS['connect']->query("select * from users where chat_id='$chatId'");
        if ($count->num_rows == 0) {
            $GLOBALS['connect']->query("insert into users (chat_id) values ('$chatId')");
        }
        $bot->sendPhoto($chatId, "https://islom.uz/img/section/2019/12/1575454571.jpg");

//        $qorilar_massiv = massiv('https://api.alquran.cloud/v1/edition/format/audio',2,"qorilar");
//
//        $link = new \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup($qorilar_massiv);
//        $bot->sendMessage($chatId, "Qorini tanlang", null, false, null, $link, null);


        bosh_menu($bot, $chatId, $firstname, 0);


    } catch (Exception $exception) {

    }

});

$bot->command('help', static function (\TelegramBot\Api\Types\Message $message) use ($bot) {

    $chat_id = $message->getChat()->getId();
    $chat_id = $message->getFrom()->getId();

});


$bot->callbackQuery(static function (\TelegramBot\Api\Types\CallbackQuery $query) use ($bot) {

    try {
        $chatId = $query->getMessage()->getChat()->getId();
        $data = $query->getData();
        $firstname = $query->getMessage()->getChat()->getFirstName();
        $messageId = $query->getMessage()->getMessageId();

        if ($data == 'read_quran') {
            $suralar_massiv = suralar(3, 'base');
            $button = new \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup($suralar_massiv);
            $bot->editMessageText($chatId, $messageId, '<b>Kerakli surani tanlang</b>', 'HTML', false, $button);

        }

        if ($data == 'back_menu') {
            bosh_menu($bot, $chatId, $firstname, $messageId);
        }

        if ($data == 'next') {
            $suralar_massiv = suralar(2, '');
            $button = new \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup($suralar_massiv);
            $bot->editMessageText($chatId, $messageId, '<b>Kerakli surani tanlang</b>', 'HTML', false, $button);

        }


        if (strpos($data, "qorilar") !== false) {
            $qori_name = explode('_', $data)[1];
            $GLOBALS['connect']->query("update users set qori= '$qori_name' where chat_id= '$chatId'");
            $suralar_massiv = suralar(3, 'base');
            $button = new \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup($suralar_massiv);
            $bot->editMessageText($chatId, $messageId, '<b>Kerakli surani tanlang</b>', 'HTML', false, $button);
        }

        if (strpos($data, "sura") !== false) {
            $tanlov = explode('_', $data);

            $back = '';
            $sura_id = 1;
            if (count($tanlov) == 2) {
                $back = 'read_quran';
                $sura_id = $tanlov[1];
            } else {
                if (count($tanlov) == 3) {
                    $back='next';
                    $sura_id = $tanlov[2];
                }
            }

            $qori_name = $GLOBALS['connect']->query("select qori from users where chat_id = '$chatId'")->fetch_row()[0];
            $GLOBALS['connect']->query("update users set learn_place = $sura_id where chat_id= '$chatId'");

            $data = json_decode(file_get_contents("http://api.alquran.cloud/v1/surah/$sura_id/$qori_name"));
            $qori_english_name_data = json_decode(file_get_contents("https://api.alquran.cloud/v1/edition/format/audio"));
            $qori_english_name = '';
            foreach ($qori_english_name_data->data as $datum) {
                if ($datum->identifier == $qori_name) {
                    $qori_english_name = $datum->englishName;
                }
            }


            $manzil = $data->data->revelationType;
            $name = $data->data->englishName;
            $arabic_name = $data->data->name;
            $count_of_ayahs = $data->data->numberOfAyahs;
            $number_of_sura = $data->data->number;

            $button = new \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup([[['text' => 'Oyatlarni qirqib olish', 'callback_data' => 'cut']],
                [['text' => '⬅️  orqaga', 'callback_data' => $back], ['text' => 'Bosh menu  🏠', 'callback_data' => 'back_menu']]]);


            $text = "$number_of_sura  -  sura
Nomi: $name ($arabic_name)
Qori: $qori_english_name (مشاري العفاسي)
Oyatlar soni: $count_of_ayahs
Nozil bo'lgan yeri: $manzil";
            $bot->editMessageText($chatId, $messageId, $text, "HTML", false, $button);
        }

        if ($data=='cut') {
            $GLOBALS['connect']->query('update users set status = "cut"');
        }else{
            $GLOBALS['connect']->query('update users set status = "search"');
        }


    } catch (Exception $exception) {

    }


});


$bot->on(static function () {
},
    static function (\TelegramBot\Api\Types\Update $update) use ($bot) {

        try {
            $chat_id = $update->getMessage()->getChat()->getId();
            $text = $update->getMessage()->getText();
            $sura_id=$GLOBALS['connect']->query("select learn_place from users where chat_id = '$chat_id'")->fetch_row()[0];

            $status= $GLOBALS['connect']->query("select status from users where chat_id= '$chat_id'")->fetch_row()[0];


            if ($status=='search'){
                "http://api.alquran.cloud/v1/search/$text/37/uz.sodik";
            }
        } catch (Exception $exception) {

        }


    });
$bot->run();