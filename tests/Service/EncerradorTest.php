<?php

namespace Alura\Leilao\Tests\Service;

use Alura\Leilao\Model\Leilao;
use Alura\Leilao\Service\Encerrador;
use Alura\Leilao\Service\EnviadorEmail;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Alura\Leilao\Dao\Leilao as LeilaoDao;

class EncerradorTest extends TestCase
{
    private Encerrador $encerrador;
    private MockObject $enviadorEmail;
    private Leilao $leilaoFiat147;
    private Leilao $leilaoVariant;

    protected function setUp(): void
    {
        $this->leilaoFiat147 = new Leilao('Fiat 147 0Km', new \DateTimeImmutable('8 days ago'));
        $this->leilaoVariant = new Leilao('Variant 1972 0Km', new \DateTimeImmutable('10 days ago'));

        $leilaoDaoMock = $this->createMock(LeilaoDao::class);
        $leilaoDaoMock->method('recuperarNaoFinalizados')->willReturn([$this->leilaoFiat147, $this->leilaoVariant]);

        $leilaoDaoMock->expects($this->exactly(2))
            ->method('atualiza')
            ->withConsecutive([$this->leilaoFiat147], [$this->leilaoVariant]);

        $this->enviadorEmail = $this->createMock(EnviadorEmail::class);
        $this->encerrador = new Encerrador($leilaoDaoMock, $this->enviadorEmail);
//        $leilaoDaoMock = $this->getMockBuilder(LeilaoDao::class)
//            ->disableOriginalConstructor()
//            ->setConstructorArgs([new \PDO('sqlite::memory:')])
//            ->getMock();
    }

    public function testLeiloesComMaisDeUmaSemanaDevemSerEncerrados()
    {
        $this->encerrador->encerra();

        $leiloes = [$this->leilaoFiat147, $this->leilaoVariant];
        self::assertCount(2, $leiloes);
        self::assertEquals($leiloes[0]->recuperarDescricao(), "Fiat 147 0Km");
        self::assertEquals($leiloes[1]->recuperarDescricao(), "Variant 1972 0Km");
    }

    public function testDeveContinuarOProcessamentoMesmoOcorrendoErroAoEnviarEmail()
    {
        $this->enviadorEmail->expects($this->exactly(2))
            ->method('notificarTerminoLeilao')
            ->willThrowException(new \DomainException("Erro ao enviar email!"));

        $this->encerrador->encerra();
    }

    public function testSoDeveEnviarEmailAposLeilaoFinalizado()
    {
        $this->enviadorEmail->expects($this->exactly(2))
            ->method('notificarTerminoLeilao')
            ->willReturnCallback(function(Leilao $leilao) {
                self::assertTrue($leilao->estaFinalizado());
            });

        $this->encerrador->encerra();
    }
}