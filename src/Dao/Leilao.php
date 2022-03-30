<?php

namespace Alura\Leilao\Dao;

use Alura\Leilao\Infra\ConnectionCreator;
use Alura\Leilao\Model\Leilao as ModelLeilao;

class Leilao
{
    private \PDO $connection;

    public function __construct(\PDO $connection)
    {
        $this->connection = $connection;
    }

    public function salva(ModelLeilao $leilao): void
    {
        $sql = 'INSERT INTO leiloes (descricao, finalizado, dataInicio) VALUES (?, ?, ?)';
        $stm = $this->connection->prepare($sql);
        $stm->bindValue(1, $leilao->recuperarDescricao(), \PDO::PARAM_STR);
        $stm->bindValue(2, $leilao->estaFinalizado(), \PDO::PARAM_BOOL);
        $stm->bindValue(3, $leilao->recuperarDataInicio()->format('Y-m-d'));
        $stm->execute();
    }

    /**
     * @return ModelLeilao[]
     */
    public function recuperarNaoFinalizados(): array
    {
        return $this->recuperarLeiloesSeFinalizado(false);
    }

    /**
     * @return ModelLeilao[]
     */
    public function recuperarFinalizados(): array
    {
        return $this->recuperarLeiloesSeFinalizado(true);
    }

    /**
     * @return ModelLeilao[]
     */
    private function recuperarLeiloesSeFinalizado(bool $finalizado): array
    {
        $sql = 'SELECT * FROM leiloes WHERE finalizado = ' . ($finalizado ? 1 : 0);
        $statement = $this->connection->query($sql, \PDO::FETCH_ASSOC);

        $dados = $statement->fetchAll();
        $leiloes = [];
        foreach ($dados as $dado) {
            $leilao = new ModelLeilao($dado['descricao'], new \DateTimeImmutable($dado['dataInicio']), $dado['id']);
            if ($dado['finalizado']) {
                $leilao->finaliza();
            }
            $leiloes[] = $leilao;
        }

        return $leiloes;
    }

    public function atualiza(ModelLeilao $leilao)
    {
        $sql = 'UPDATE leiloes SET descricao = :descricao, dataInicio = :dataInicio, finalizado = :finalizado WHERE id = :id';
        $statement = $this->connection->prepare($sql);
        $statement->bindValue(':descricao', $leilao->recuperarDescricao());
        $statement->bindValue(':dataInicio', $leilao->recuperarDataInicio()->format('Y-m-d'));
        $statement->bindValue(':finalizado', $leilao->estaFinalizado(), \PDO::PARAM_BOOL);
        $statement->bindValue(':id', $leilao->recuperarId(), \PDO::PARAM_INT);
        $statement->execute();
    }
}
