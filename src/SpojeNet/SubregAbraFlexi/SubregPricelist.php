<?php

/**
 * Subreg2AbraFlexi - AbraFlexi Customers handler class.
 *
 * @author Vítězslav Dvořák <vitezslav.dvorak@spojenet.cz>
 * @copyright  2020-2024 SpojeNet s.r.o.
 */

namespace SpojeNet\SubregAbraFlexi;

/**
 * Description of SubregPricelist
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 */
class SubregPricelist extends \AbraFlexi\Cenik
{
    /**
     *
     * @var array
     */
    private $config;

    /**
     *
     * @var \Subreg\Client
     */
    private $subreg;

    /**
     * @inheritDoc
     */
    public function __construct($init = null, $options = [])
    {
        $this->config = [
            "location" => \Ease\Shared::cfg('SUBREG_LOCATION'),
            "uri" => \Ease\Shared::cfg('SUBREG_URI'),
            "login" => \Ease\Shared::cfg('SUBREG_LOGIN'),
            "password" => \Ease\Shared::cfg('SUBREG_PASSWORD')
        ];
        parent::__construct($init, $options);
        $this->subreg = new \Subreg\Client($this->config);
        //$this->countries = $this->getCountries();
    }

    /**
     * Main Import Loop
     */
    public function import()
    {
        $this->subreg->login();
        $domainsToProcess = $this->subreg->pricelist();

        $position = 0;
        foreach ($domainsToProcess as $domain => $domainInfo) {
            $synced = false;
            if (array_key_exists('prices', $domainInfo)) {
                $domainInfo['name'] = $domain;
                $prices = $domainInfo['prices'];
                if ((array_key_exists('register', $prices) && array_key_exists('renew', $prices)) && ($prices['register'] == $prices['renew'])) {
                    $synced = $this->saveDomain('common', $domainInfo);
                } else {
                    foreach ($prices as $priceType => $priceValue) {
                        switch ($priceType) {
                            case 'renew':
                            case 'register':
                                try {
                                    $synced = $this->saveDomain($priceType, $domainInfo);
                                } catch (\AbraFlexi\Exception $exc) {
                                    echo $exc->getTraceAsString();
                                    $this->addStatusMessage('Error importing domain: ' . $domain, 'error');
                                }
                                break;
                            case "ea1":
                            case "ea2":
                            case "ea3":
                            case "ea4":
                            case "ea5":
                            case "ea6":
                            case "ea7":
                            case "preregistration":
                            case "restore":
                            case "setup":
                            case "sunrise":
                            case "transfer":
                            case 'change_owner':
                            case 'local_presence':
                                // Price type ignored
                                break;
                            default:
                                if (floatval($priceValue)) {
                                    $this->addStatusMessage($domain . ': ' . _('Unhandled price type') . ': ' . $priceType, 'warning');
                                }
                                break;
                        }
                    }
                }

                $this->addStatusMessage($domain . ': ' . (++$position) . '/' . count($domainsToProcess) . ' ' . $this->getRecordCode(), ($synced ? 'success' : 'error'));
            } else {
                $this->addStatusMessage($domain . ': ' . _('No prices provided ?!?'), 'warning');
            }
        }
    }

    /**
     * Save Domain into AbraFlexi Pricelist
     *
     * @param string $type
     * @param array  $domainInfo
     *
     * @return boolean
     */
    public function saveDomain($type, $domainInfo)
    {
        $domain = idn_to_utf8($domainInfo['name'], IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46);
        $itemType = '';
        if ($type == 'common') {
            $price = floatval($domainInfo['prices']['register']);
        } else {
            $price = floatval($domainInfo['prices'][$type]);
            if ($type != 'register') {
                $itemType = '_' . $type;
            }
        }

        $this->dataReset();
        $this->setDataValue('id', 'ext:subreg:' . $domainInfo['name'] . $itemType);
        //            $asciiDomain = iconv('UTF-8', 'ASCII//IGNORE', $domain);
        //            if(($asciiDomain != $domain) && $this->recordExists(\AbraFlexi\Functions::code($asciiDomain))){
        //                $this->addStatusMessage('Old Domain Code '. \AbraFlexi\Functions::code($asciiDomain) . ' Removal', $this->deleteFromAbraFlexi() ? 'success' : 'warning');
        //            }
        //            if ($asciiDomain != $domain) {
        $this->setDataValue('kod', 'DOMENA' . $domainInfo['name'] . $itemType);
        //            } else {
        //            }

        $this->setDataValue('nazev', '*.' . $domain . ' ' . str_replace('_', '', $itemType));
        $this->setDataValue(
            'popis',
            str_replace('_', '', $itemType) . ' Doména ' . $domainInfo['country'] . ' (' . $domainInfo['continent'] . ')'
        );
        $this->setDataValue('nakupCena', $price);
        //            $this->setDataValue('cenaZakl', $price);
        $this->setDataValue('mj1', 'code:ROK');

        //        if (array_key_exists($domainInfo['country'], $this->countries)) {
        //            $this->setDataValue('stat', $this->countries[$domainInfo['country']]);
        //            $ccode = strtolower(\AbraFlexi\RO::uncode($this->countries[$domainInfo['country']]));
        //        } else {
        //            $ccode = false;
        //        }
        //$this->setDataValue('skupZboz','code:DOMENY');

        return $this->sync() && $this->saveDomainPrice();
    }

    /**
     * Save Current domain's price
     *
     * @return bool
     */
    public function saveDomainPrice()
    {
        $restult = false;
        $supplier = new \AbraFlexi\RW([
            'cenik' => $this->getRecordCode(),
            'firma' => 'code:' . \Ease\Shared::cfg('ABRAFLEXI_GRANSY_CODE', 'GRANSY'),
            'kodIndi' => $this->getDataValue('kod'),
            'primarni' => true,
            'nakupCena' => $this->getDataValue('nakupCena'),
                ], ['evidence' => 'dodavatel']);

        if ($supplier->recordExists(['firma' => 'code:' . \Ease\Shared::cfg('ABRAFLEXI_GRANSY_CODE', 'GRANSY'), 'kodIndi' => $this->getDataValue('kod')])) {
            $supplier->insertToAbraFlexi(['id' => $supplier->lastResult['dodavatel'][0]['id'],
                'nakupCena' => $this->getDataValue('nakupCena')]);
            $restult = true;
        } else {
            if ($supplier->sync()) {
                $supplier->addStatusMessage(sprintf(
                    _('GRANSY is supplier for %s now'),
                    $this->getDataValue('kod')
                ), 'success');
                $restult = true;
            }
        }
        return $restult;
    }

    /**
     * Add Country Flag as image to AbraFlexi
     *
     * @param string $code
     *
     * @return boolean
     */
    public function addFlag($code)
    {
        $flagFile = 'images/country-flags/png250px/' . $code . '.png';
        $result = 0;
        if (!file_exists($flagFile)) {
            $flagFile = '../' . $flagFile;
        }

        if ($code && file_exists($flagFile)) {
            $result = \AbraFlexi\Priloha::addAttachmentFromFile(
                $this,
                $flagFile
            );
            $this->addStatusMessage(
                'FLAG Image Attachment',
                ($result == 200) ? 'success' : 'warning'
            );
        }
        return ($result == 200);
    }

    /**
     * Return list of countries known to AbraFlexi
     *
     * @return array
     */
    public function getCountries()
    {
        $countries = [];
        $contryer = new \AbraFlexi\RO(null, ['evidence' => 'stat']);
        $countriesRaw = $contryer->getColumnsFromAbraFlexi(['kod', 'nazev']);
        foreach ($countriesRaw as $countryInfo) {
            $countries[$countryInfo['nazev']] = 'code:' . $countryInfo['kod'];
        }
        return $countries;
    }
}
