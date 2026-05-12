<?php

declare(strict_types=1); // Строгая типизация

// Конфигурация подключения к БД
const DB_HOST = '127.0.0.1';      // Хост БД (можно заменить на IP-адрес, например, '127.0.0.1')
const DB_PORT = 3308;             // Порт MySQL (по умолчанию 3306)
const DB_USER = 'root';   // Имя пользователя БД
const DB_PASSWORD = 'qwerty123_qw'; // Пароль пользователя БД
const DB_NAME = 'shop';  // Название базы данных

/**
 * Устанавливает соединение с БД
 * @return mysqli Объект соединения с БД
 * @throws Exception Если подключение не удалось
 */
function connectDB(): mysqli
{
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME, DB_PORT);

    // Проверка успешности подключения
    if ($conn->connect_error) {
        throw new Exception("Не удалось подключиться к БД: " . $conn->connect_error);
    }

    // Устанавливаем кодировку соединения (рекомендуется UTF-8)
    $conn->set_charset("utf8mb4");

    return $conn;
}

// CRUD для товаров

/**
 * Создаёт новый товар
 * @param string $name Название товара
 * @param float $price Цена товара
 * @param string $category Категория товара
 * @param int $stock Количество на складе
 * @return bool true при успешном создании, false — иначе
 */
function createProduct(string $name, float $price, string $category, int $stock): bool
{
    $db = connectDB();
    $sql = "INSERT INTO products (name, price, category, stock) VALUES (?, ?, ?, ?)";
    $stmt = $db->prepare($sql);
    $result = $stmt->bind_param("ssdi", $name, $price, $category, $stock);
    $stmt->execute();
    $db->close();
    return $result;
}

/**
 * Получает список товаров
 * @param string|null $category Фильтрация по категории (опционально)
 * @return array Список товаров в виде ассоциативного массива
 */
function readProducts(?string $category = null): array
{
    $db = connectDB();
    $sql = "SELECT * FROM products";

    if ($category) {
        $sql .= " WHERE category = ?";
        $stmt = $db->prepare($sql);
        $stmt->bind_param("s", $category);
        $stmt->execute();
        $result = $stmt->get_result();
    } else {
        $result = $db->query($sql);
    }

    $products = $result->fetch_all(MYSQLI_ASSOC);
    $db->close();
    return $products;
}

/**
 * Обновляет товар
 * @param int $id ID товара
 * @param string $field Имя поля для обновления (например, 'price')
 * @param mixed $value Новое значение поля
 * @return bool true при успешном обновлении, false — иначе
 */
function updateProduct(int $id, string $field, mixed $value): bool
{
    $db = connectDB();
    $sql = "UPDATE products SET $field = ? WHERE id = ?";
    $stmt = $db->prepare($sql);

    switch (gettype($value)) {
        case 'string':
            $stmt->bind_param("si", $value, $id);
            break;
        case 'integer':
            $stmt->bind_param("ii", $value, $id);
            break;
        case 'double':
            $stmt->bind_param("di", $value, $id);
            break;
        default:
            throw new Exception("Неподдерживаемый тип значения: " . gettype($value));
    }

    $result = $stmt->execute();
    $db->close();
    return $result;
}

/**
 * Удаляет товар
 * @param int $id ID товара для удаления
 * @return bool true при успешном удалении, false — иначе
 */
function deleteProduct(int $id): bool
{
    $db = connectDB();
    $sql = "DELETE FROM products WHERE id = ?";
    $stmt = $db->prepare($sql);
    $stmt->bind_param("i", $id);
    $result = $stmt->execute();
    $db->close();
    return $result;
}

// Пример функции для получения статистики
/**
 * Получает количество посещений за указанный период
 * @param string $period Период ('day', 'week', 'month')
 * @return int Количество посещений
 */
function getVisits(string $period): int
{
    $db = connectDB();
    $days = match ($period) {
        'day' => 1,
        'week' => 7,
        'month' => 30,
        default => 1
    };

    $sql = "SELECT COUNT(*) as count FROM visits WHERE date >= CURDATE() - INTERVAL $days DAY";
    $result = $db->query($sql);
    $row = $result->fetch_assoc();
    $db->close();
    return (int)$row['count'];
}
?>