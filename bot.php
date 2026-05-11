<?php

require_once 'vendor/autoload.php';
require_once 'config.php';
require_once 'Database.php';

use DigitalStars\SimpleVK\LongPoll;

$config = require 'config.php';
$db = new Database($config);
$vk = new LongPoll($config['vk_token'], $config['vk_api_version']);

// Обрабатываем события LongPoll
$vk->listen(function () use ($vk, $db, $config) {
    try {
        // Исправляем метод получения обновления — проверяем документацию LongPoll
        $updates = $vk->getUpdates(); // Вместо getUpdate()
        foreach ($updates as $update) {
            if ($update['object']['message']['peer_id'] === $vk->getGroupId()) { // Проверяем, что сообщение в группу
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
        // Обрабатываем исключения SimpleVK
        error_log('SimpleVK Exception: ' . $e->getMessage());
        $vk->msg('Произошла ошибка. Попробуйте позже.')->send($user_id);
    } catch (\Exception $e) {
        error_log('PHP Exception: ' . $e->getMessage());
        $vk->msg('Внутренняя ошибка сервера.')->send($user_id);
    }
});

// Запуск бота — исправляем метод
$vk->start(); // Вместо run() — проверяем актуальную документацию LongPoll

// Функция меню
function sendMenu($uid, $vk) {
    $kbd = [['text' => 'Добавить товар', 'color' => 'primary'], ['text' => 'Корзина', 'color' => 'secondary']];
    $vk->msg('Меню')->kbd($kbd)->send($uid);
}
