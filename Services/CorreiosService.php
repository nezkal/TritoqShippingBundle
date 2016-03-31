<?php
namespace Tritoq\Bundle\ShippingBundle\Services;

use Tritoq\Bundle\ShippingBundle\Services\Exception\HardException;

/**
 * Class CorreiosService
 * @package Tritoq\Bundle\ShippingBundle\Services
 */
class CorreiosService implements ServicesInterface
{
    /**
     *
     */
    const MAX_PESO = 30;
    /**
     * @var array
     */
    private $codservices = array(
        41106 => 'PAC', // sem contrato
        41068 => 'PAC', // com contrato
        40010 => 'Sedex', // sem contrato
        40096 => 'Sedex', // com contrato
        40045 => 'Sedex a cobrar',
        40215 => 'Sedex 10',
        40290 => 'Sedex Hoje',
        81019 => 'E-Sedex'
    );

    /**
     * @var string
     */

    private $services;

    /**
     * @var string
     */
    private $company;

    /**
     * @var string
     */
    private $password;

    /**
     * @var array
     */
    private $configurations;

    /**
     * @var string
     */
    private $url = "http://ws.correios.com.br/calculador/CalcPrecoPrazo.asmx";

    /**
     * @var array
     */
    private $items = array();

    /**
     * @var array
     */
    private $parameters = array(
        'nCdEmpresa' => null,
        'sDsSenha' => null,
        'sCepOrigem' => null,
        'sCepDestino' => null,
        'nVlPeso' => null,
        'nCdFormato' => null,
        'nVlComprimento' => null,
        'nVlAltura' => null,
        'nVlLargura' => null,
        'sCdMaoPropria' => 'n',
        'nVlValorDeclarado' => null,
        'sCdAvisoRecebimento' => 'n',
        'nCdServico' => null,
        'nVlDiametro' => 0,
        'StrRetorno' => 'xml'
    );

    /**
     * @param array $configurations
     * @return $this|ServicesInterface
     */
    public function setConfigurations(array $configurations)
    {
        $this->configurations = $configurations;

        if (is_array($this->configurations)) {

            $this->password = $this->configurations['password'];
            $this->company = $this->configurations['company'];
            $this->services = $this->configurations['services'];

            if (isset($this->configurations['url'])) {
                $this->url = $this->configurations['url'];
            }

            if (isset($this->configurations['parameters'])) {
                $this->parameters = array_merge($this->parameters, $this->configurations['parameters']);
            }
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getConfigurations()
    {
        return $this->configurations;
    }

    /**
     * @param $value
     * @return $this
     */
    public function setDeclaredValue($value)
    {
        $value = doubleval($value);
        $this->parameters['nVlValorDeclarado'] = ceil($value);
        return $this;
    }

    /**
     * @param int $weight
     * @param int $width
     * @param int $height
     * @param int $depth
     * @return $this|ServicesInterface
     */
    public function addItem($weight, $width, $height, $depth)
    {
        // TODO: Implement addItem() method.
        $this->items[] = array("weight" => $weight, "width" => $width, "height" => $height, "depth" => $depth);
        return $this;
    }

    /**
     * @param $cep
     * @return string
     */
    private function clearCep($cep)
    {
        preg_match('/(\d{5})-(\d{3})/', $cep, $matches);

        if ($matches)
            return $matches[1] . $matches[2];
        else
            return $cep;
    }


    private function getUrl()
    {
        $this->parameters['nCdEmpresa'] = $this->company;
        $this->parameters['sDsSenha'] = $this->password;

        $url = $this->url . "?";

        foreach ($this->parameters as $key => $param) {
            $url .= $key . "=" . $param . '&';
        }

        return $url;
    }

    /**
     * @param string $destiny
     * @return array
     * @throws Exception\HardException
     */
    public function calculate($destiny)
    {
        // TODO: Implement calculate() method.

        $weight = $width = $height = $depth = 0;

        foreach ($this->items as $item) {
            $weight += $item['weight'];
            $width += $item['width'];
            $height += $item['height'];
            $depth += $item['depth'];
        }

        if ($width < 11)
            $width = 11;

        if ($height < 11)
            $height = 11;


        if ($depth < 16)
            $depth = 16;


        // Força os correios ao peso mínimo
        $depth = 16;
        $width = 11;
        $height = 2;

        $weight = ceil($weight / 1000);

        if ($weight > self::MAX_PESO) {
            return array();
        }

        $this->parameters['nVlPeso'] = $weight;
        $this->parameters['nVlComprimento'] = $depth;
        $this->parameters['nVlLargura'] = ceil($width);
        $this->parameters['nVlAltura'] = ceil($height);
        $this->parameters['sCepDestino'] = $this->clearCep($destiny);

        $values = array();

        $this->parameters['nCdServico'] = $this->services;

        $url = $this->getUrl();


        $error = false;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        $xml = curl_exec($ch);
        curl_close($ch);

        $simplexml = new \SimpleXMLElement($xml);


        if($simplexml->MsgErro) {
            foreach ($simplexml->MsgErr as $item) {

                $nodeValue = isset($item) ? $item : null;

                if (!empty($nodeValue)) {
                    if ($nodeValue == "CEP de destino invalido.") {
                        throw new HardException($nodeValue);
                    }

                    $error = $nodeValue;
                }
            }
        }


        if ($error == false) {


            foreach ($simplexml->cServico as $item) {



                $codigo =  $item->Codigo . "";
                $valor = $item->Valor . "";
                $prazo = $item->PrazoEntrega . "";


                $label = isset($this->codservices[$codigo]) ? $this->codservices[$codigo] : 'Sem descrição';

                $number = doubleval(str_replace(",", ".", str_replace(".", "", $valor)));

                $values[$codigo] = array(
                    'prazo' => $prazo,
                    'valor' => $valor,
                    'label' => $label,
                    'number' => $number
                );

            }
        }

        return $values;
    }
}