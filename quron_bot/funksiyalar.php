<?php


function str_bormi($needle, $str)
{

}

function massiv($url, $cols, $id)
{
    $massiv = [];
    $data = json_decode(file_get_contents($url));
    $bolindi = array_chunk($data->data, $cols);
    foreach ($bolindi as $bolim) {
        $massiv[] = [['text' => $bolim[0]->englishName, 'callback_data' => $id . '_' . $bolim[0]->identifier], ['text' => $bolim[1]->englishName, 'callback_data' => $id . '_' . $bolim[1]->identifier]];
    }
    return $massiv;
}


function suralar($cols, $mode)
{

    $massiv = [];
    $data = json_decode(file_get_contents('https://api.alquran.cloud/v1/meta'));
    $arr = $data->data->surahs->references;
    if ($mode == 'base') {

        array_splice($arr, 96);
    } else {
        array_splice($arr, 0, 96);

    }
    var_dump($arr);
    $bolindi = array_chunk($arr, $cols);
    foreach ($bolindi as $bolim) {
        $row = [];
        for ($i = 0; $i < $cols; $i++) {
            $col = ['text' => $bolim[$i]->englishName, 'callback_data' => 'sura' . '_' . $bolim[$i]->number];
            $row[] = $col;
        }
        $massiv[] = $row;
    }
    if ($mode == 'base') {

        $massiv[] = [['text' => '🏠️  Bosh menu', 'callback_data' => 'back_menu'], ['text' => 'Keyingi   ➡', 'callback_data' => 'next']];
    } else {
        $massiv[] = [['text' => '⬅️  orqaga', 'callback_data' => 'read_quran'], ['text' => 'Bosh menu  🏠', 'callback_data' => 'back_menu']];

    }
    return $massiv;
}

function bosh_menu($bot, $chatId, $firstname, $messageId)
{
    $link = new \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup([
        [['text' => '🟢 Biz haqimizda', 'callback_data' => 'about_us'], ['text' => '⚙ Sozlamalar', 'callback_data' => 'settings']],
        [['text' => '🔎 Izlash Xizmati', 'callback_data' => 'search'], ['text' => '🕋️ Quron yodlash ', 'callback_data' => 'read_quran']]]);
    if ($messageId == 0) {
        $bot->sendMessage($chatId, "<b>Assalomu alaykum! $firstname \nKerakli bo'limni tanlang</b>", 'HTML', false, null, $link, null);
    } else {
        $bot->editMessageText($chatId, $messageId, "<b>Assalomu alaykum! $firstname \nKerakli bo'limni tanlang</b>", 'HTML', false, $link);

    }


}