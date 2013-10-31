<?php
namespace Tritoq\Bundle\ShippingBundle\Manager;

use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tritoq\Bundle\ShippingBundle\Services\ServicesInterface;

/**
 * Class ShippingManager
 * @package Tritoq\Bundle\ShippingBundle\Manager
 */
class ShippingManager
{
    const SESSION_KEY = '_shipping.cep';
    const SESSION_TYPE = '_shipping.type';
    /**
     * @var array
     */
    private $items;

    /**
     * @var array
     */
    private $providers;


    /**
     * @var Session
     */
    private $session;


    public function __construct(Session $session)
    {
        $this->items = array();
        $this->session = $session;
    }

    /**
     * @param array $providers
     */
    public function setProviders($providers)
    {
        $this->providers = $providers;
    }

    /**
     * @return string
     */
    public function getTypes()
    {
        return implode(",", array_keys($this->providers));
    }

    /**
     * @param $type
     * @return ServicesInterface
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function getProvider($type)
    {
        /** @var ServicesInterface $provider */
        $provider = $this->providers[$type];

        if (!isset($provider))
            throw new NotFoundHttpException('This type ' . $type . 'is not supported. Types (' . $this->getTypes() . ')');

        return $provider;

    }

    /**
     * @param $type
     * @param $destiny
     * @return mixed
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function getPrices($type, $destiny)
    {
        $provider = $this->getProvider($type);

        foreach ($this->items as $item) {
            $provider->addItem($item['weight'], $item['width'], $item['height'], $item['depth']);
        }

        $data = $provider->calculate($destiny);

        $this->save($destiny);

        return $data;
    }

    /**
     * @param $destiny
     * @return $this
     */
    public function save($destiny)
    {
        $this->session->set(self::SESSION_KEY, $destiny);
        return $this;
    }

    /**
     * @return string|null
     */
    public function getLasted()
    {
        $value = $this->session->get(self::SESSION_KEY);
        return empty($value) ? null : $value;
    }

    /**
     * @param $value
     */
    public function setType($value)
    {
        $this->session->set(self::SESSION_TYPE, $value);
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->session->get(self::SESSION_TYPE);
    }


    /**
     * @param int $weight
     * @param int $width
     * @param int $height
     * @param int $depth
     */
    public function addProduct($weight, $width, $height, $depth)
    {
        $this->items[] = array("weight" => $weight, "width" => $width, "height" => $height, "depth" => $depth);
    }
}