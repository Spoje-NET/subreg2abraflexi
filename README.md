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
ABRAFLEXI_GRANSY_CODE=GRANSY
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

Debian Package Install
----------------------

```shell
sudo apt install lsb-release wget apt-transport-https bzip2


wget -qO- https://repo.vitexsoftware.com/keyring.gpg | sudo tee /etc/apt/trusted.gpg.d/vitexsoftware.gpg
echo "deb [signed-by=/etc/apt/trusted.gpg.d/vitexsoftware.gpg]  https://repo.vitexsoftware.com  $(lsb_release -sc) main" | sudo tee /etc/apt/sources.list.d/vitexsoftware.list
sudo apt update

sudo apt install subreg2abraflexi
```

MultiFlexi
----------

[![MultiFlexi App](https://github.com/VitexSoftware/MultiFlexi/blob/main/doc/multiflexi-app.svg)](https://www.multiflexi.eu/apps.php)

Subreg2AbraFlexi is ready for run as [MultiFlexi](https://multiflexi.eu) application.
See the full list of ready-to-run applications within the MultiFlexi platform on the [application list page](https://www.multiflexi.eu/apps.php).
