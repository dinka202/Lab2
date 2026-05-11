<?php
class Database {
    private $connection;

    public function __construct($config) {
        $this->connection = new mysqli(
            $config['mysql_host'],
            $config['mysql_user'],
            $config['mysql_password'],
            $config['mysql_dbname'],
            $config['mysql_port']
        );
        if ($this->connection->connect_error) {
            die("Ошибка подключения к БД: " . $this->connection->connect_error);
        }
    }

    public function executeQuery($query, $params = []) {
        $stmt = $this->connection->prepare($query);
        if ($params) {
            $types = str_repeat('s', count($params));
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        return $stmt->get_result();
    }

    public function executeUpdate($query, $params = []) {
        $stmt = $this->connection->prepare($query);
        if ($params) {
            $types = str_repeat('s', count($params));
            $stmt->bind_param($types, ...$params);
        }
        return $stmt->execute();
    }

    public function close() {
        $this->connection->close();
    }
}
?>
