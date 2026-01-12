<?php
namespace App\Repositories;

use DateTimeInterface;
use PDO;

final class PagamentoRepository
{
    public function __construct(private PDO $pdo)
    {}

    /**
     * Ex.: SELECT Nome, Telefone FROM Pagamentos
     *      WHERE DtPagamento < NOW() AND Fl_Conciliado = 0
     */
    public function listarPendentes(DateTimeInterface $ate): array
    {
        $sql = "
            SELECT
                cr.DtFinal,
                c.Nome,
                c.Telefone
            FROM
                ContasReceber cr
            INNER JOIN cliente c ON c.id = cr.IdCliente
            WHERE
                cr.DtFinal < DATE_SUB(CURDATE(), INTERVAL 5 DAY)
                AND cr.vlTotal > cr.vlconciliado
                AND c.Ativo = 1
                AND c.EnvioWhatsapp = 1
                AND TRIM(c.Telefone) IS NOT NULL
                AND TRIM(c.Telefone) <> ''
            GROUP BY
                cr.DtFinal,
                c.Nome,
                c.Telefone
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
