<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Enums\ReceitaFederalEnum;

class ConsultController extends Controller
{

    const TOKENS = ['Y29tcHVmYWNpbDpjb21wdWZhY2ls'];

    public function index(Request $request)
    {
        $params = $request->all();

        if (!$request->header('Authorization')) {
            return response()->json(
                ['message' => 'Você não está autorizado a consumir este recurso'
            ], 401);
        }

        if ($request->header('Authorization')) {
            if (!in_array($request->header('Authorization'), self::TOKENS)) {
                return response()->json(
                    ['message' => 'Você não está autorizado a consumir este recurso'
                ], 401);
            }
        }

        if (!isset($params['document'])) {
            return response()->json(
                ['message' => 'CNPJ não informado para realizar a pesquisa'
            ], 404);
        }

        $data = [
            'documento' => $params['document'],
            'key' => md5(34324) . $params['document'],
        ];

        return $this->getCaptcha($data);
    }

    public function post(Request $request)
    {
        $params = $request->all();

        if (!$request->header('Authorization')) {
            return response()->json(
                ['message' => 'Você não está autorizado a consumir este recurso'
            ], 401);
        }

        if ($request->header('Authorization')) {
            if (!in_array($request->header('Authorization'), self::TOKENS)) {
                return response()->json(
                    ['message' => 'Você não está autorizado a consumir este recurso'
                ], 401);
            }
        }

        if (!isset($params['document'])) {
            return response()->json(
                ['message' => 'CNPJ não informado para realizar a pesquisa'
            ], 404);
        }

        $params['key'] = md5(34324) . $params['document'];

        $result = $this->getInfo($params);

        $data = [
            'cnpj' => $result[ReceitaFederalEnum::NUMERO_INSCRICAO],
            'razaoSocial' => $result[ReceitaFederalEnum::NOME_EMPRESARIAL],
            'nomeFantasia' => $result[ReceitaFederalEnum::NOME_FANTASIA],
            'email' => $result[ReceitaFederalEnum::EMAIL],
            'telefone' => $result[ReceitaFederalEnum::TELEFONE],
            'endereco' => [
                'cep' => $result[ReceitaFederalEnum::CEP]
            ]
        ];

        return $data;
    }

    private function getInfo($params)
    {
        $fields = [];
        $cnpj = $params['document'];
        $captchaCnpj = $params['captcha'];
        $key = $params['key'];

        if ($cnpj && $captchaCnpj) {
            $getHtmlCNPJ = $this->getHtmlCNPJ($cnpj, $captchaCnpj, $key);
            $fields = $this->parseHtmlCNPJ($getHtmlCNPJ);
        }

        return $fields;
    }

    private function getCaptcha($params)
    {
        $fileContent = '';
        $cookiecnpj = '';

        $cookieFile = $this->getLocalCookie($params['key']);
        if (!file_exists($cookieFile)) {
            $file = fopen($cookieFile, 'w');
            fclose($file);
        } else {
            $file = fopen($cookieFile, 'r');
            while (!feof($file)) {
                $fileContent .= fread($file, 1024);
            }
            fclose($file);

            $line = explode("\n", $fileContent);

            for ($i = 4; $i < count($line) - 1; $i++) {
                $explodeContent = explode(chr(9), $line[$i]);
                $cookiecnpj .= trim($explodeContent[count($explodeContent) - 2]) . '=' .
                    trim($explodeContent[count($explodeContent) - 1]) . '; ';
            }

            $cookiecnpj = substr($cookiecnpj, 0, -2);
        }

        $result = $this->requestCaptchaCookie($cookieFile);

        $file = fopen($cookieFile, 'r');
        while (!feof($file)) {
            $fileContent .= fread($file, 1024);
        }
        fclose($file);

        $line = explode("\n", $fileContent);

        for ($i = 4; $i < count($line) - 1; $i++) {
            $explodeContent = explode(chr(9), $line[$i]);
            if ($line[0][0] != '' && $line[0][0] != '#') {
                $cookiecnpj .= trim($explodeContent[count($explodeContent) - 2]) . '=' .
                    trim($explodeContent[count($explodeContent) - 1]) . '; ';
            }
        }

        $cookiecnpj = substr($cookiecnpj, 0, -2);

        $result = $this->requestCaptchaImage($cookieFile, $cookiecnpj);

        return array(
            'cookie' => $cookiecnpj,
            'captchaBase64' => base64_encode($result),
            'key' => $params['key'],
        );
    }

    private function requestCaptchaCookie($cookieFile)
    {
        $curl = curl_init(ReceitaFederalEnum::URL_INFO_CNPJ);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $this->getHeaders());
        curl_setopt($curl, CURLOPT_COOKIEJAR, $cookieFile);
        curl_setopt($curl, CURLOPT_COOKIEFILE, $cookieFile);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($curl);
        curl_close($curl);
        return $result;
    }

    private function requestCaptchaImage($cookieFile, $cookiecnpj)
    {
        $curl = curl_init(ReceitaFederalEnum::URL_CAPTCHA);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $this->getHeaders());
        curl_setopt($curl, CURLOPT_COOKIEFILE, $cookieFile);
        curl_setopt($curl, CURLOPT_COOKIEJAR, $cookieFile);
        curl_setopt($curl, CURLOPT_COOKIE, $cookiecnpj);
        curl_setopt($curl, CURLOPT_REFERER, ReceitaFederalEnum::URL_INFO_CNPJ);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($curl);
        curl_close($curl);
        return $result;
    }

    private function requestCnpjInfo($postData, $cookieFile, $cookie)
    {
        $curl = curl_init(ReceitaFederalEnum::URL_VALIDA);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $this->getHeaders());
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($curl, CURLOPT_COOKIEFILE, $cookieFile);
        curl_setopt($curl, CURLOPT_COOKIEJAR, $cookieFile);
        curl_setopt($curl, CURLOPT_COOKIE, $cookie);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_MAXREDIRS, 3);
        curl_setopt($curl, CURLOPT_REFERER, ReceitaFederalEnum::URL_INFO_CNPJ);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $html = curl_exec($curl);
        curl_close($curl);
        return $html;
    }

    private function getFieldHtml($begin, $end, $total)
    {
        return str_replace(
            $begin,
            '',
            str_replace(strstr(strstr($total, $begin), $end), '', strstr($total, $begin))
        );
    }

    private function getLocalCookie($key)
    {
        return storage_path('app/consults/' . $key);
    }

    private function getHeaders()
    {
        return array(
            'Host: ' . ReceitaFederalEnum::HOST_RECEITA,
            'User-Agent: Mozilla/5.0 (Windows NT 6.1; rv:53.0) Gecko/20100101 Firefox/53.0',
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Accept-Language: pt-BR,pt;q=0.9,en-US;q=0.8,en;q=0.7',
            'Connection: keep-alive',
            'Upgrade-Insecure-Requests: 1',
        );
    }

    private function parseHtmlCNPJ($html)
    {
        $result = [];
        $fieldCount = 23;
        $caract_especiais = array(
            chr(9),
            chr(10),
            chr(13),
            '&nbsp;',
            '</b>',
            '  ',
            '<b>MATRIZ<br>',
            '<b>FILIAL<br>'
        );

        $html = str_replace('<br><b>', '<b>', str_replace($caract_especiais, '', strip_tags($html, '<b><br>')));
        $html = str_replace(' <b>', '<b>', $html);

        for ($i = 0; $i < $fieldCount; $i++) {
            $html2 = strstr($html, utf8_decode(ReceitaFederalEnum::getHtmlField($i)));
            $result[] = trim($this->getFieldHtml(utf8_decode(ReceitaFederalEnum::getHtmlField($i)) .
                '<b>', '<br>', $html2));
            $html = $html2;
        }

        if (strstr($result[ReceitaFederalEnum::CNAE_SECUNDARIO], '<b>')) {
            $secondaryCnae = explode('<b>', $result[ReceitaFederalEnum::CNAE_SECUNDARIO]);
            $result[ReceitaFederalEnum::CNAE_SECUNDARIO] = $secondaryCnae;
            unset($secondaryCnae);
        }

        return $result;
    }

    private function getHtmlCNPJ($cnpj, $captcha, $key)
    {
        $fileContent = '';
        $cookie = '';
        $cookieFile = $this->getLocalCookie($key);

        if (!$cookieFile) {
            return false;
        }

        $file = fopen($cookieFile, 'r');
        while (!feof($file)) {
            $fileContent .= fread($file, 1024);
        }
        fclose($file);

        $line = explode("\n", $fileContent);

        for ($i = 4; $i < count($line) - 1; $i++) {
            $explodeContent = explode(chr(9), $line[$i]);
            $cookie .= trim($explodeContent[count($explodeContent) - 2]) . '=' .
                trim($explodeContent[count($explodeContent) - 1]) . '; ';
        }

        $cookie = substr($cookie, 0, -2);

        if (!strstr($fileContent, 'flag	1')) {
            $line = chr(10) . chr(10) . ReceitaFederalEnum::HOST_RECEITA . '	FALSE	/	FALSE	0	flag	1' . chr(10);
            $newCookie = str_replace(chr(10) . chr(10), $line, $fileContent);
            unlink($cookieFile);

            $file = fopen($cookieFile, 'w');
            fwrite($file, $newCookie);
            fclose($file);

            $cookie .= ';flag=1';
        }

        $postData = array(
            'origem' => 'comprovante',
            'cnpj' => $cnpj,
            'txtTexto_captcha_serpro_gov_br' => $captcha,
            'search_type' => 'cnpj'
        );

        $postData = http_build_query($postData, '', '&');

        return $this->requestCnpjInfo($postData, $cookieFile, $cookie);
    }
}
