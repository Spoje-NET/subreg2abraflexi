# subreg2abraflexi

![Project Logo](subreg2abraflexi.svg?raw=true)

Import Subreg Pricelist into AbraFlexi

configuration
-------------

```env
EASE_LOGGER=syslog|console

SUBREG_LOCATION: https://soap.demoreg.net/cmd.php
SUBREG_URI: https://soap.demoreg.net/soap
SUBREG_LOGIN: yourlogin
SUBREG_PASSWORD: xxxxxxx

ABRAFLEXI_URL=https://demo.flexibee.eu:5434
ABRAFLEXI_LOGIN=winstrom
ABRAFLEXI_PASSWORD=winstrom
ABRAFLEXI_COMPANY=demo_de

```

Run In Container
----------------

https://hub.docker.com/repository/docker/vitexsoftware/subreg2abraflexi

```shell
docker run --env-file .env vitexsoftware/subreg2abraflexi:latest
```

```shell
podman run --env-file .env docker.io/vitexsoftware/subreg2abraflexi:latest
```
