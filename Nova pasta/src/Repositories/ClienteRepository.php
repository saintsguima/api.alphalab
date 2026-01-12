<?php
namespace App\Repositories;

use PDO;

final class ClienteRepository
{
    public function __construct(private PDO $pdo)
    {}

    public function listarClientes(): array
    {
        $params = [];
        $sql    = "SELECT Id, Nome, Email FROM cliente WHERE Email != ''";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll();

    }
}
