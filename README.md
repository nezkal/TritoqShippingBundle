tritoq-bundle-ShippingBundle
============================

Shipping Controller using Correios Service and Jadlog Sevice

## Instaling

### Download

** Only with submodule**

`git submodule add git@bitbucket.org:nezkal/tritoq-shipping-bundle.git vendor/tritoq`

Example Configuration:

```yaml
#
# Serviços Correios
#   41106 = PAC sem contrato
#   40010 = SEDEX sem contrato
#   40045 = SEDEX a Cobrar, sem contrato
#   40215 = SEDEX 10, sem contrato
#   40290 = SEDEX Hoje, sem contrato
#   40096 = SEDEX com contrato
#   40436 = SEDEX com contrato
#   40444 = SEDEX com contrato
#   81019 = e-SEDEX, com contrato
#   41068 = PAC com contrato
#
# Formatos
#   1 - Caixa
#   2 - Rolo Prisma
#   3 - Envelope
#
tritoq_shipping:
    correios:
        company: 123456
        password: 123456
        services: "41068,40096"
        parameters:
            sCepOrigem: 89812120
            nCdFormato: 1
#
#
# Servicos Jadlog
#   0 - Expresso
#   3 - Package
#   4 - Rodoviário
#   4 - Econômico
#   6 - Doc
#   7 - Corporate
#   9 - .COM
#   10 - Internacional
#   12 - Cargo
#   14 - Emergencial
#
    jadlog:
        user: "123456"
        password: "123456"
        services: "0,4,5"
        parameters:
            vCepOrig: "89812120"
            vVlColeta: "10,00"
            vSeguro: N
```


Register Bundle in AppKernel:

```php
    $bundles = array(
        ...
        new Tritoq\Bundle\ShippingBundle\TritoqShippingBundle(),
        ...
    )

```

Usage:

```php

public function indexController ()
{
       $shipping = $this->container->get('tritoq.shipping.manager');
       // Add um product
       /** @var Tritoq\Bundle\ShippingBundle\Manager\ShippingManager $shipping **/
       $shipping->addProduct($weight, $width, $height, $depth);
}

```