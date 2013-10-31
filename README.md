tritoq-bundle-ShippingBundle
============================

Shipping Controller using Correios Service and Jadlog Sevice

## Instaling

### Download

** Only with submodule**

`git submodule add git@bitbucket.org:nezkal/tritoq-shipping-bundle.git`

Example Configuration:

```yaml
tritoq_shipping:
    correios:
        company: 123456
        password: 123456
        services: "41068,40096"
        parameters:
            sCepOrigem: 89812120
            nCdFormato: 1

    jadlog:
        user: "02649956000144"
        password: "B2m0O1l3"
        services: "0,4,5"
        parameters:
            vCepOrig: "89812120"
            vVlColeta: "10,00"
            vSeguro: N
```