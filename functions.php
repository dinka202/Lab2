<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$products = [
    ["id" => 1, "name" => "Шина Michelin", "price" => 10000, "description" => "Летние шины"],
    ["id" => 2, "name" => "Фильтр масляный", "price" => 500, "description" => "Для двигателя"],
    ["id" => 3, "name" => "Свечи зажигания", "price" => 800, "description" => "Иридиевые, комплект 4 шт."],
    ["id" => 4, "name" => "Тормозные колодки", "price" => 3000, "description" => "Передние, комплект"],
];

$cart = [];

$config = [
    "smtp_server" => "smtp.gmail.com",
    "smtp_port" => 587,
    "email_address" => "Magazine@gmail.com",
    "email_password" => "пароль",
    "admin_email" => "почта администратора",
];

function showCatalog(): void
{
    global $products;
    echo "\nКАТАЛОГ ТОВАРОВ" . PHP_EOL;
    foreach ($products as $product) {
        echo "ID: {$product['id']}, Название: {$product['name']}, Цена: {$product['price']} руб., Описание: {$product['description']}" . PHP_EOL;
    }
    echo "-------------\n";
}

/**
 * Добавить товар в корзину
 * @param int $productId ID товара из каталога
 */
function addToCart(int $productId): void
{
    global $products, $cart;
    foreach ($products as $product) {
        if ($product['id'] == $productId) {
            $cart[] = $product;
            echo "\nТовар '{$product['name']}' добавлен в корзину!" . PHP_EOL;
            return;
        }
    }
    echo "\nТовар не найден!" . PHP_EOL;
}

function showCart(): void
{
    global $cart;
    if (empty($cart)) {
        echo "\nВаша корзина пуста!" . PHP_EOL;
        return;
    }

    echo "\nВАША КОРЗИНА" . PHP_EOL;
    $total = 0;
    foreach ($cart as $product) {
        echo "Название: {$product['name']}, Цена: {$product['price']} руб." . PHP_EOL;
        $total += $product['price'];
    }
    echo "Общая сумма: {$total} руб." . PHP_EOL;
    echo "--------------------\n";
}

function confirmPayment(): void
{
    global $cart, $config;
    if (empty($cart)) {
        echo "\nКорзина пуста! Добавьте товары." . PHP_EOL;
        return;
    }

    $total = array_sum(array_column($cart, 'price'));
    echo "\nПодтверждение оплаты:" . PHP_EOL;
    echo "Общая сумма: {$total} руб." . PHP_EOL;
    echo "Подтвердите оплату (да/нет): ";
    $answer = trim(fgets(STDIN));

    if (strtolower($answer) === 'да') {
        echo "\nОплата подтверждена! Спасибо за покупку!" . PHP_EOL;
        sendOrderNotification($cart, $total);
        $cart = [];
    } else {
        echo "\nОплата отменена." . PHP_EOL;
    }
}

/**
 * Отправить уведомление о новом заказе
 * @param array $cart Корзина с товарами
 * @param float $total Общая сумма заказа
 */
function sendOrderNotification(array $cart, float $total): void
{
    global $config;

    require __DIR__ . '/vendor/phpmailer/phpmailer/src/Exception.php';
    require __DIR__ . '/vendor/phpmailer/phpmailer/src/PHPMailer.php';
    require __DIR__ . '/vendor/phpmailer/phpmailer/src/SMTP.php';

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = $config['smtp_server'];
        $mail->SMTPAuth = true;
        $mail->Username = $config['email_address'];
        $mail->Password = $config['email_password'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = $config['smtp_port'];

        $mail->setFrom($config['email_address'], 'Интернет-магазин');
        $mail->addAddress($config['admin_email'], 'Администратор');

        $mail->Subject = 'Новый заказ в интернет-магазине "Автомагазин"';
        $body = "Новый заказ подтверждён!\n\n";
        $body .= "Детали заказа:\n";
        foreach ($cart as $product) {
            $body .= "- {$product['name']} — {$product['price']} руб.\n";
        }
        $body .= "\nОбщая сумма: {$total} руб.\n";
        $body .= "Дата заказа: " . date('Y-m-d H:i:s') . "\n";
        $mail->Body = $body;

        $mail->send();
        echo "Уведомление администратору отправлено!" . PHP_EOL;
    } catch (Exception $e) {
        echo "Ошибка при отправке уведомления: {$e->getMessage()}" . PHP_EOL;
    }
}
?>