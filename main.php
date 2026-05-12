<?php
require 'functions.php';

// Имитация бота: считываем команду из консоли
$command = $_GET['cmd'] ?? ''; // В реальном боте — из сообщения
$params = explode(' ', $command);

// Аутентификация
if ($params === '/админ' && $params === 'пароль123') {
    echo "Доступ разрешён!\n";
    processAdminCommand(array_slice($params, 2));
} else {
    echo "Доступ запрещён!\n";
}

function processAdminCommand($params) {
    global $db;

    switch ($params) {
        // CRUD для товаров
        case 'добавить_товар':
            createProduct($params, $params, $params, $params);
            echo "Товар добавлен!\n";
            break;
        case 'показать_товары':
            $products = readProducts($params ?? null);
            foreach ($products as $p) {
                echo "$p[id]. $p[name] ($p[price] руб.)\n";
            }
            break;
        case 'обновить_товар':
            updateProduct($params, $params, $params);
            echo "Товар обновлён!\n";
            break;
        case 'удалить_товар':
            deleteProduct($params);
            echo "Товар удалён!\n";
            break;

        // Статистика
        case 'статистика_посещений':
            $visits = getVisits($params);
            echo "Посещений за $params: $visits\n";
            break;
        case 'статистика_продаж':
            $sales = getSales($params);
            echo "Продаж за $params: {$sales['count']} (на {$sales['total']} руб.)\n";
            break;
        case 'статистика_прибыли':
            $profit = getProfit($params);
            echo "Прибыль за $params: $profit руб.\n";
            break;

        default:
            echo "Неизвестная команда!\n";
    }
}
?>
