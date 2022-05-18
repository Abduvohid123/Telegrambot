<?php


$connect =mysqli_connect('localhost','user','password','quran');
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


function query($sql){
    return $GLOBALS['connect']->query($sql);
}
function suralar($cols, $mode)
{

    $next='';
    $massiv = [];
    $data = $GLOBALS['connect']->query('select name from suralar')->fetch_all();
    $arr = [];
    foreach ($data as $datum) {
        $arr[]=$datum[0];
    }
    if ($mode == 'base') {

        array_splice($arr, 96);
    } else {
        $next="_next";
        array_splice($arr, 0, 96);

    }

    $count=$mode=='base'?0:96;
    $bolindi = array_chunk($arr, $cols);



    foreach ($bolindi as $bolim) {
        $row = [];
        for ($i = 0; $i < $cols; $i++) {
            $col = ['text' => $bolim[$i], 'callback_data' => "sura$next" . '_' . ($count+1)];
            $row[] = $col;
            $count++;
        }
        $massiv[] = $row;

    }
    if ($mode == 'base') {

        $massiv[] = [['text' => '🏠  Bosh menu', 'callback_data' => 'back_menu'], ['text' => 'Keyingi   ➡️', 'callback_data' => 'next']];
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



