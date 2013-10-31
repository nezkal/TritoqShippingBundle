<?php
/** 
 * User: nzk
 * Date: 20/06/13
 * Time: 19:33
 */

namespace Tritoq\Bundle\ShippingBundle\Services;

/**
 * Class ServicesInterface
 * @package Tritoq\Bundle\ShippingBundle\Services
 */
interface ServicesInterface {

    /**
     * @param string $destiny
     * @return array
     */
    public function calculate ($destiny);

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
    public function addItem ($weight, $width, $height, $depth);

    /**
     * @param array $configurations
     * @return ServicesInterface
     */
    public function setConfigurations(array $configurations);

    /**
     * @return array
     */
    public function getConfigurations ();
}