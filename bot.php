<?php
require_once 'vendor/autoload.php';
require_once 'config.php';
require_once 'Database.php';

use DigitalStars\SimpleVK\LongPoll;

$config = require 'config.php';
$db = new Database($config);
$vk = new LongPoll($config['vk_token'], $config['vk_api_version']);

$vk->listen(function () use ($vk, $db, $config) {
    try {
        $updates = $vk->getUpdates();
        foreach ($updates as $update) {
            if ($update['object']['message']['peer_id'] === $vk->getGroupId()) {
                $user_id = $update['object']['message']['from_id'];
                $text = mb_strtolower(trim($update['object']['message']['text']));

                switch ($text) {
                    case 'старт':
                        sendMenu($user_id, $vk);
                        break;
                    default:
                        $vk->msg('Неизвестная команда. Используйте «старт»')->send($user_id);
                        break;
                }
            }
        }
    } catch (\DigitalStars\SimpleVK\SimpleVkException $e) {
        error_log('SimpleVK Exception: ' . $e->getMessage());
        $vk->msg('Произошла ошибка. Попробуйте позже.')->send($user_id);
    } catch (\Exception $e) {
        error_log('PHP Exception: ' . $e->getMessage());
        $vk->msg('Внутренняя ошибка сервера.')->send($user_id);
    }
});

$vk->start();

function sendMenu($uid, $vk) {
    $kbd = [['text' => 'Добавить товар', 'color' => 'primary'], ['text' => 'Корзина', 'color' => 'secondary']];
    $vk->msg('Меню')->kbd($kbd)->send($uid);
}
