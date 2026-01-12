<?php
namespace App\Repositories;

use PDO;

final class ClienteRepository
{
    public function __construct(private PDO $pdo)
    {}

    public function listarClientes(): array
    {
        $sql = "
            SELECT
                cr.DtFinal,
                c.Nome,
                c.Email
            FROM
                ContasReceber cr
            INNER JOIN cliente c ON c.id = cr.IdCliente
            WHERE
                cr.DtFinal < DATE_SUB(CURDATE(), INTERVAL 5 DAY)
                AND cr.vlTotal > cr.vlconciliado
                AND c.Ativo = 1
                AND c.EnvioEmail = 1
                AND TRIM(c.Email) IS NOT NULL
                AND TRIM(c.Email) <> ''
            GROUP BY
                cr.DtFinal,
                c.Nome,
                c.Email
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
