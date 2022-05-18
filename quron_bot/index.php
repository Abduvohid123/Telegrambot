<?php


require_once __DIR__ . '/vendor/autoload.php';
include 'Database.php';
$baza = new Database();

$connect = $baza->conn;

$botToken = "5116267217:AAGAiLf8oXEWD98JncIuvnCVXpn1gNupJQw";

// https://api.telegram.org/bot5116267217:AAGAiLf8oXEWD98JncIuvnCVXpn1gNupJQw/setWebhook?url=https://64ed-84-54-120-198.ngrok.io/bot/TelegramBot/quron_bot/index.php

/**
 * @var $bot \TelegramBot\Api\Client | \TelegramBot\Api\BotApi
 */
$bot = new \TelegramBot\Api\Client($botToken);

include 'funksiyalar.php';

$bot->command('start', static function (\TelegramBot\Api\Types\Message $message) use ($bot) {
    try {
        $chatId = $message->getChat()->getId();
        $firstname = $message->getChat()->getFirstName();
        $count = query("select * from users where chat_id='$chatId'");
        if ($count->num_rows == 0) {
            query("insert into users (chat_id) values ('$chatId')");
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


$bot->callbackQuery(static function (\TelegramBot\Api\Types\CallbackQuery $callbackquery) use ($botToken, $bot) {

    try {
        $chatId = $callbackquery->getMessage()->getChat()->getId();
        $data = $callbackquery->getData();
        $firstname = $callbackquery->getMessage()->getChat()->getFirstName();
        $messageId = $callbackquery->getMessage()->getMessageId();

        if ($data == 'read_quran2') {
            $suralar_massiv = suralar(3, 'base');
            $button = new \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup($suralar_massiv);
            $bot->sendMessage($chatId, '<b>Kerakli surani tanlang</b>', 'HTML', false,null, $button);

            $bot->deleteMessage($chatId,$messageId);
        }

        if ($data == 'read_quran') {
            $suralar_massiv = suralar(3, 'base');
            $button = new \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup($suralar_massiv);
            $bot->editMessageText($chatId, $messageId, '<b>Kerakli surani tanlang</b>', 'HTML', false, $button);

        }
        if ($data == 'next') {
            $suralar_massiv = suralar(2, '');
            $button = new \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup($suralar_massiv);
            $bot->editMessageText($chatId, $messageId, '<b>Kerakli surani tanlang</b>', 'HTML', false, $button);

        }

        if ($data == 'back_menu') {
            bosh_menu($bot, $chatId, $firstname, $messageId);

        }
        if ($data == 'back_menu2') {
            bosh_menu($bot, $chatId, $firstname, 0);
            $bot->deleteMessage($chatId, $messageId);

        }




        if (strpos($data, "qorilar") !== false) {
            $qori_name = explode('_', $data)[1];
            query("update users set qori= '$qori_name' where chat_id= '$chatId'");
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
                    $back = 'next';
                    $sura_id = $tanlov[2];
                }
            }

            $qori_name = query("select qori from users where chat_id = '$chatId'")->fetch_row()[0];
            query("update users set learn_place = $sura_id where chat_id= '$chatId'");


            $manzil = query("select manzil from suralar where id = '$sura_id'")->fetch_row()[0];
            $name = query("select name from suralar where id = '$sura_id'")->fetch_row()[0];
            $count_of_ayahs = query("SELECT max(number_of_ayah) from ayahs where surah_id ='$sura_id'")->fetch_row()[0];
            $number_of_sura = $sura_id;


            $button = new \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup([[['text' => 'Oyatlarni qirqib olish', 'callback_data' => 'cut']],
                [['text' => '⬅️  orqaga', 'callback_data' => $back], ['text' => 'Bosh menu  🏠', 'callback_data' => 'back_menu2']]]);


            $text = "$number_of_sura  -  sura
Nomi: $name 
Oyatlar soni: $count_of_ayahs
Nozil bo'lgan yeri: $manzil";

            $bot->sendDocument($chatId, 'https://server8.mp3quran.net/afs/001.mp3', $text, null, $button);
            $bot->deleteMessage($chatId, $messageId);
        }

        if ($data == 'cut') {
            query('update users set status = "cut"');
        } else {
            query('update users set status = "search"');
        }


    } catch (Exception $exception) {

    }


});


$bot->on(static function () {
},
    static function (\TelegramBot\Api\Types\Update $update) use ($bot) {

        try {

            $message_id = $update->getMessage()->getMessageId();

            //  $image = $update->getMessage()->getPhoto();
            //var_dump($image[0]->fileId);
//            $bot->sendPhoto($chat_id,$image[0]->fileId);

            $chat_id = $update->getMessage()->getChat()->getId();
            $text = $update->getMessage()->getText();
            $sura_id = query("select learn_place from users where chat_id = '$chat_id'")->fetch_row()[0];

            $status = query("select status from users where chat_id= '$chat_id'")->fetch_row()[0];


            if ($text == 't') {
                $bot->sendVoice($chat_id, new CURLFile('https://server8.mp3quran.net/afs/001.mp3'), "");
            }
            if ($status == 'search') {
                // "http://api.alquran.cloud/v1/search/$text/37/uz.sodik";
            }
        } catch (Exception $exception) {

        }


    });
$bot->run();