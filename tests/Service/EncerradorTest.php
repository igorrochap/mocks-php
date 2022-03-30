<?php

namespace Alura\Leilao\Tests\Service;

use Alura\Leilao\Model\Leilao;
use Alura\Leilao\Service\Encerrador;
use PHPUnit\Framework\TestCase;
use Alura\Leilao\Dao\Leilao as LeilaoDao;

class EncerradorTest extends TestCase
{
    public function testLeiloesComMaisDeUmaSemanaDevemSerEncerrados()
    {
        $fiat147 = new Leilao('Fiat 147 0Km', new \DateTimeImmutable('8 days ago'));
        $variant = new Leilao('Variant 1972 0Km', new \DateTimeImmutable('10 days ago'));

        $leilaoDaoMock = $this->createMock(LeilaoDao::class);
        $leilaoDaoMock->method('recuperarNaoFinalizados')->willReturn([$fiat147, $variant]);
        $leilaoDaoMock->expects($this->exactly(2))
            ->method('atualiza')
            ->withConsecutive([$fiat147], [$variant]);

        $encerrador = new Encerrador($leilaoDaoMock);
        $encerrador->encerra();

        $leiloes = [$fiat147, $variant];
        self::assertCount(2, $leiloes);
        self::assertEquals($leiloes[0]->recuperarDescricao(), "Fiat 147 0Km");
        self::assertEquals($leiloes[1]->recuperarDescricao(), "Variant 1972 0Km");
    }
}