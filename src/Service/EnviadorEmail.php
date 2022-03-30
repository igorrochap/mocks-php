<?php

namespace Alura\Leilao\Service;

use Alura\Leilao\Model\Leilao;

class EnviadorEmail
{
    public function notificarTerminoLeilao(Leilao $leilao): void
    {
        $emailEnviado = mail('dingobeudingobeudingodingobeu@email.com',
            'Leilão finalizado',
            "O leilão para " . $leilao->recuperarDescricao() . " foi finalizado"
        );

        if(!$emailEnviado) {
            throw new \DomainException("Erro ao enviar email!");
        }
    }
}