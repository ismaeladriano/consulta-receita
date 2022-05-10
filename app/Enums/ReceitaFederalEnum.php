<?php

namespace App\Enums;

class ReceitaFederalEnum
{
    const URL_CAPTCHA = 'http://servicos.receita.fazenda.gov.br/Servicos/cnpjreva/captcha/gerarCaptcha.asp';
    const URL_INFO_CNPJ = 'http://servicos.receita.fazenda.gov.br/Servicos/cnpjreva/Cnpjreva_Solicitacao_CS.asp';
    const URL_VALIDA = 'http://servicos.receita.fazenda.gov.br/Servicos/cnpjreva/valida.asp';
    const HOST_RECEITA = 'servicos.receita.fazenda.gov.br';

    const NUMERO_INSCRICAO = 0;
    const DATA_ABERTURA = 1;
    const NOME_EMPRESARIAL = 2;
    const NOME_FANTASIA = 3;
    const PORTE = 4;
    const CNAE_PRINCIPAL = 5;
    const CNAE_SECUNDARIO = 6;
    const NATUREZA = 7;
    const LOGRADOURO = 8;
    const NUMERO = 9;
    const COMPLEMENTO = 10;
    const CEP = 11;
    const BAIRRO = 12;
    const MUNICIPIO = 13;
    const UF = 14;
    const EMAIL = 15;
    const TELEFONE = 16;
    const EFR = 17;
    const SITUACAO = 18;
    const DATA_SITUACAO = 19;
    const MOTIVO_SITUACAO = 20;
    const SITUACAO_ESPECIAL = 21;
    const DATA_SITUACAO_ESPECIAL = 22;

    public static function getHtmlField($constValue)
    {
        switch ($constValue) {
            case self::NUMERO_INSCRICAO:
                return 'NÚMERO DE INSCRIÇÃO';
            case self::DATA_ABERTURA:
                return 'DATA DE ABERTURA';
            case self::NOME_EMPRESARIAL:
                return 'NOME EMPRESARIAL';
            case self::NOME_FANTASIA:
                return 'TÍTULO DO ESTABELECIMENTO (NOME DE FANTASIA)';
            case self::PORTE:
                return 'PORTE';
            case self::CNAE_PRINCIPAL:
                return 'CÓDIGO E DESCRIÇÃO DA ATIVIDADE ECONÔMICA PRINCIPAL';
            case self::CNAE_SECUNDARIO:
                return 'CÓDIGO E DESCRIÇÃO DAS ATIVIDADES ECONÔMICAS SECUNDÁRIAS';
            case self::NATUREZA:
                return 'CÓDIGO E DESCRIÇÃO DA NATUREZA JURÍDICA';
            case self::LOGRADOURO:
                return 'LOGRADOURO';
            case self::NUMERO:
                return 'NÚMERO';
            case self::COMPLEMENTO:
                return 'COMPLEMENTO';
            case self::CEP:
                return 'CEP';
            case self::BAIRRO:
                return 'BAIRRO/DISTRITO';
            case self::MUNICIPIO:
                return 'MUNICÍPIO';
            case self::UF:
                return 'UF';
            case self::EMAIL:
                return 'ENDEREÇO ELETRÔNICO';
            case self::TELEFONE:
                return 'TELEFONE';
            case self::EFR:
                return 'ENTE FEDERATIVO RESPONSÁVEL (EFR)';
            case self::SITUACAO:
                return 'SITUAÇÃO CADASTRAL';
            case self::DATA_SITUACAO:
                return 'DATA DA SITUAÇÃO CADASTRAL';
            case self::MOTIVO_SITUACAO:
                return 'MOTIVO DE SITUAÇÃO CADASTRAL';
            case self::SITUACAO_ESPECIAL:
                return 'SITUAÇÃO ESPECIAL';
            case self::DATA_SITUACAO_ESPECIAL:
                return 'DATA DA SITUAÇÃO ESPECIAL';
        }
    }
}
