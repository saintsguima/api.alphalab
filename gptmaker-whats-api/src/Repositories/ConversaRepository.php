<?php
namespace App\Repositories;

use DateTime;
use PDO;

final class ConversaRepository
{
    public function __construct(private PDO $pdo)
    {}

    public function obterPorTelefone(string $agentId, string $telefone): ?array
    {
        $sql = "SELECT * FROM Conversas WHERE AgentId = :agent AND Telefone = :tel LIMIT 1";
        $st  = $this->pdo->prepare($sql);
        $st->execute([':agent' => $agentId, ':tel' => $telefone]);
        $row = $st->fetch();
        return $row ?: null;
    }

    public function upsert(string $agentId, string $telefone, ?string $threadId): void
    {
        $exist = $this->obterPorTelefone($agentId, $telefone);
        if ($exist) {
            $sql = "UPDATE Conversas
                       SET ThreadId = COALESCE(:threadId, ThreadId),
                           UltimoEnvioAt = :agora
                     WHERE Id = :id";
            $st = $this->pdo->prepare($sql);
            $st->execute([
                ':threadId' => $threadId,
                ':agora'    => (new DateTime())->format('Y-m-d H:i:s'),
                ':id'       => $exist['Id'],
            ]);
        } else {
            $sql = "INSERT INTO Conversas (Telefone, AgentId, ThreadId, UltimoEnvioAt)
                    VALUES (:tel, :agent, :threadId, :agora)";
            $st = $this->pdo->prepare($sql);
            $st->execute([
                ':tel'      => $telefone,
                ':agent'    => $agentId,
                ':threadId' => $threadId,
                ':agora'    => (new DateTime())->format('Y-m-d H:i:s'),
            ]);
        }
    }
}
