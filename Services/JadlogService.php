<?php
namespace Tritoq\Bundle\ShippingBundle\Services;


use Symfony\Component\DomCrawler\Crawler;
use Tritoq\Bundle\ShippingBundle\Services\Exception\HardException;

class JadlogService implements ServicesInterface
{

    private $url = "http://www.jadlog.com.br:8080/JadlogEdiWs/services/ValorFreteBean?wsdl";

    /**
     * @var string
     */
    private $user;

    /**
     * @var string
     */
    private $password;

    private $listServices = array(
        '0' => 'Expresso',
        '3' => 'Package',
        '4' => 'Rodoviário',
        '5' => 'Econômico',
        '6' => 'Doc',
        '7' => 'Corporate',
        '9' => '.COM',
        '10' => 'Internacional',
        '12' => 'Cargo',
        '14' => 'Emergencial'
    );

    private $parameters = array(
        'vModalidade' => null,
        'Password' => null,
        'vSeguro' => null,
        'vVlDec' => null,
        'vVlColeta' => null,
        'vCepOrig' => null,
        'vCepDest' => null,
        'vPeso' => null,
        'vFrap' => 'N',
        'vEntrega' => 'D',
        'vCnpj' => null
    );

    /**
     * @var array
     */
    private $configurations;

    /**
     * @var array
     */
    private $services;

    /**
     * @var array
     */
    private $items = array();


    /**
     * @param array $parameters
     */
    public function setParameters($parameters)
    {
        $this->parameters = $parameters;
    }

    /**
     * @param $valorFrete
     */
    public function setValorDeclarado($valorFrete)
    {
        $this->parameters['vVlDec'] = $valorFrete;
    }

    /**
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * @param array $services
     */
    public function setServices($services)
    {
        $this->services = $services;
    }

    /**
     * @return array
     */
    public function getServices()
    {
        return $this->services;
    }

    /**
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
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


    /**
     * @param array $configurations
     * @return $this|ServicesInterface
     */
    public function setConfigurations(array $configurations)
    {
        $this->configurations = $configurations;

        if (is_array($configurations)) {
            $this->services = $this->configurations['services'];

            if (isset($this->configurations['url'])) {
                $this->url = $this->configurations['url'];
            }

            if (isset($this->configurations['user'])) {
                $this->setUser($this->configurations['user']);
            }
            if (isset($this->configurations['password'])) {
                $this->setPassword($this->configurations['password']);
            }

            if (isset($this->configurations['password'])) {
                $this->setPassword($this->configurations['password']);
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
     * @param string $destiny
     * @return array
     * @throws Exception\HardException
     */
    public function calculate($destiny)
    {
        // TODO: Implement calculate() method.
        $this->parameters['vCnpj'] = $this->getUser();
        $this->parameters['Password'] = $this->getPassword();
        $this->parameters['vCepDest'] = $this->clearCep($destiny);

        $weight = 0;


        foreach ($this->items as $item) {
            $weight += $item['weight'];
        }

        $this->parameters['vPeso'] = ceil($weight / 1000);

        $method = "valorar";


        $options = array('location' => $this->url);


        $services = explode(",", $this->services);


        $client = new \SoapClient($this->url);

        $values = array();

        foreach ($services as $service) {
            $this->parameters['vModalidade'] = $service;
            $arguments = array($method => $this->parameters);
            $result = $client->__soapCall($method, $arguments, $options);
            $crawler = new Crawler();
            $crawler->addXmlContent($result->valorarReturn);
            $value = $crawler->filter('Retorno')->first()->text();


            if ($value == '-1') {
                throw new HardException($crawler->filter('Mensagem')->first()->text());
            }


            $label = isset($this->listServices[$service]) ? $this->listServices[$service] : 'Sem descrição';

            $number = doubleval(str_replace(",", ".", str_replace(".", "", $value)));

            $values[$service] = array(
                'prazo' => 0,
                'valor' => number_format($number, 2, ",", ""),
                'label' => $label,
                'number' => $number
            );

        }

        return $values;


    }

    /**
     *
     * Add Items in service
     *
     * @param int $weight
     * @param int $width
     * @param int $height
     * @param int $depth
     * @return ServicesInterface
     */
    public function addItem($weight, $width, $height, $depth)
    {
        // TODO: Implement addItem() method.
        $this->items[] = array(
            'weight' => $weight,
            'width' => $width,
            'height' => $height,
            'depth' => $depth
        );

        return $this;
    }

    /**
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param string $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return string
     */
    public function getUser()
    {
        return $this->user;
    }
}