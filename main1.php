<?php
require 'functions1.php';

echo "Доступ разрешён!\n";

function showMenu() {
    echo "\n=== Меню ===\n";
    echo "1. Добавить товар\n";
    echo "2. Показать товары\n";
    echo "3. Обновить товар\n";
    echo "4. Удалить товар\n";
    echo "5. Статистика посещений\n";
    echo "6. Выйти\n";
    echo "Выберите пункт меню (1-6): ";
}

function processCommand($choice) {
    switch ($choice) {
        case '1':
            echo "Введите данные для добавления товара (название, цена, категория, количество через пробел): ";
            $input = trim(fgets(STDIN));
            $params = explode(' ', $input);

            if (count($params) !== 4) {
                echo "Ошибка: нужно 4 аргумента (название, цена, категория, количество)\n";
                return;
            }

            list($name, $price, $category, $stock) = $params;

            if (!is_string($name) || !is_numeric($price) || !is_string($category) || !is_numeric($stock)) {
                echo "Ошибка типов данных\n";
                return;
            }

            if (createProduct((string)$name, (float)$price, (string)$category, (int)$stock)) {
                echo "Товар добавлен\n";
            } else {
                echo "Ошибка при добавлении\n";
            }
            break;

        case '2':
            echo "Введите категорию (или оставьте пустым для всех): ";
            $category = trim(fgets(STDIN));
            $products = readProducts((string)$category);

            if (empty($products)) {
                echo "Товаров нет\n";
            } else {
                foreach ($products as $product) {
                    echo sprintf(
                        "%s. %s (%d руб., категория: %s, в наличии: %d)\n",
                        $product['id'],
                        $product['name'],
                        (int)$product['price'],
                        $product['category'],
                        (int)$product['stock']
                    );
                }
            }
            break;

        case '3':
            echo "Введите ID товара, поле и значение для обновления (через пробел, например: 1 name НовоеНазвание): ";
            $input = trim(fgets(STDIN));
            $params = explode(' ', $input);

            if (count($params) !== 3) {
                echo "Ошибка: нужно 3 аргумента (ID, поле, значение)\n";
                return;
            }

            list($id, $field, $value) = $params;

            if (!is_numeric($id) || !in_array((string)$field, ['name', 'price', 'category', 'stock'])) {
                echo "Ошибка параметров\n";
                return;
            }

            if (in_array((string)$field, ['price', 'stock']) && !is_numeric($value)) {
                echo "Цена/количество должны быть числами\n";
                return;
            }

            if (updateProduct((int)$id, (string)$field, (string)$value)) {
                echo "Товар обновлён\n";
            } else {
                echo "Ошибка обновления\n";
            }
            break;

        case '4':
            echo "Введите ID товара для удаления: ";
            $id = trim(fgets(STDIN));

            if (!is_numeric($id)) {
                echo "Ошибка: ID должен быть числом\n";
                return;
            }

            if (deleteProduct((int)$id)) {
                echo "Товар удалён\n";
            } else {
                echo "Ошибка удаления\n";
            }
            break;

        case '5':
            echo "Введите период (day/week/month): ";
            $period = trim(fgets(STDIN));

            if (!in_array(strtolower($period), ['day', 'week', 'month'])) {
                echo "Неверный период\n";
                return;
            }

            $visits = getVisits($period);
            echo "Посещений за $period: $visits\n";
            break;

        case '6':
            echo "До свидания!\n";
            exit;

        default:
            echo "Неверный выбор. Пожалуйста, выберите пункт от 1 до 6.\n";
    }
}

while (true) {
    showMenu();
    $choice = trim(fgets(STDIN));
    processCommand($choice);
}
