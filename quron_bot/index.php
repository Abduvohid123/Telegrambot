<?php


require_once __DIR__ . '/vendor/autoload.php';
include 'Database.php';
$baza = new Database();

$connect = $baza->conn;

$botToken = "5116267217:AAGAiLf8oXEWD98JncIuvnCVXpn1gNupJQw";

// https://api.telegram.org/bot5116267217:AAGAiLf8oXEWD98JncIuvnCVXpn1gNupJQw/setWebhook?url=https://77a4-213-230-72-70.ngrok.io/Courses/TelegramBot/quron_bot/index.php

/**
 * @var $bot \TelegramBot\Api\Client | \TelegramBot\Api\BotApi
 */
$bot = new \TelegramBot\Api\Client($botToken);

include 'funksiyalar.php';

$bot->command('start', static function (\TelegramBot\Api\Types\Message $message) use ($bot) {
    try {
        $chatId = $message->getChat()->getId();
        $firstname =$message->getChat()->getFirstName();
        $count = $GLOBALS['connect']->query("select * from users where chat_id='$chatId'");
        if ($count->num_rows == 0) {
            $GLOBALS['connect']->query("insert into users (chat_id) values ('$chatId')");
        }
        $bot->sendPhoto($chatId, "https://islom.uz/img/section/2019/12/1575454571.jpg" );

//        $qorilar_massiv = massiv('https://api.alquran.cloud/v1/edition/format/audio',2,"qorilar");
//
//        $link = new \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup($qorilar_massiv);
//        $bot->sendMessage($chatId, "Qorini tanlang", null, false, null, $link, null);


       bosh_menu($bot,$chatId,$firstname,0);


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
        $firstname=$query->getMessage()->getChat()->getFirstName();
        $messageId = $query->getMessage()->getMessageId();

        if ($data=='read_quran'){
            $suralar_massiv=suralar(3,'base');
            $button = new \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup($suralar_massiv);
            $bot->editMessageText($chatId, $messageId, '<b>Kerakli surani tanlang</b>', 'HTML', false, $button);

        }

        if ($data=='back_menu'){
           bosh_menu($bot,$chatId,$firstname,$messageId);
        }

        if ($data=='next'){
            $suralar_massiv=suralar(2,'');
            $button = new \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup($suralar_massiv);
            $bot->editMessageText($chatId, $messageId, '<b>Kerakli surani tanlang</b>', 'HTML', false, $button);

        }

        if (strpos($data, "qorilar") !== false) {
            $qori_name = explode('_', $data)[1];
            $GLOBALS['connect']->query("update users set qori= '$qori_name' where chat_id= '$chatId'");
            $suralar_massiv=suralar(3);
            $button = new \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup($suralar_massiv);
            $bot->editMessageText($chatId, $messageId, '<b>Kerakli surani tanlang</b>', 'HTML', false, $button);
        }

        if (strpos($data, "sura") !== false) {
            $sura_id = explode('_', $data)[1];
            var_dump($sura_id);
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
        } catch (Exception $exception) {

        }


    });
$bot->run();