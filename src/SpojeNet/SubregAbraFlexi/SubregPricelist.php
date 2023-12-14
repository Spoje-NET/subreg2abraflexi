<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace SpojeNet\SubregAbraFlexi;

/**
 * Description of SubregPricelist
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 */
class SubregPricelist extends \AbraFlexi\Cenik
{
    public function import()
    {
        $config = [
            "location" => constant('SUBREG_LOCATION'),
            "uri" => constant('SUBREG_URI'),
            "login" => constant('SUBREG_LOGIN'),
            "password" => constant('SUBREG_PASSWORD')
        ];

        $countries = $this->getCountries();

        $subreger = new \Subreg\Client($config);

        $domainsToProcess = $subreger->pricelist();
        $position = 0;
        foreach ($domainsToProcess as $domain => $domainInfo) {
            $domain = idn_to_utf8($domain, IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46);

            if (array_key_exists('prices', $domainInfo)) {
                if (array_key_exists('register', $domainInfo['prices'])) {
                    $price = $domainInfo['prices']['register'];
                } else {
                    if (array_key_exists('renew', $domainInfo['prices'])) {
                        $price = $domainInfo['prices']['renew'];
                    } else {
                        $price = $domainInfo;
                    }
                }
            } else {
                $this->addStatusMessage(sprintf(_('No prices availble for %s (%s)'), $domain, $domainInfo['country']), 'warning');
                continue;
            }


            $this->dataReset();
            $this->setDataValue('id', 'ext:subreg:' . $domain);
            $asciiDomain = iconv('UTF-8', 'ASCII//IGNORE', $domain);
            $this->setDataValue(
                'kod',
                $this->getKod('DOMENA' . (strlen($asciiDomain) ? strtoupper($asciiDomain) : $domain ))
            );
            $this->setDataValue('nazev', '*.' . $domain);
            $this->setDataValue(
                'popis',
                'Doména ' . $domainInfo['country'] . ' (' . $domainInfo['continent'] . ')'
            );
            $this->setDataValue('nakupCena', $price);
//            $this->setDataValue('cenaZakl', $price);
            $this->setDataValue('mj1', 'code:ROK');

            if (array_key_exists($domainInfo['country'], $countries)) {
                $this->setDataValue('stat', $countries[$domainInfo['country']]);
                $ccode = strtolower(\AbraFlexi\RO::uncode($countries[$domainInfo['country']]));
            } else {
                $ccode = false;
            }

            //$this->setDataValue('skupZboz','code:DOMENY');

            $synced = $this->sync();
            $this->addStatusMessage(
                ++$position . '/' . count($domainsToProcess) . ' ' . $domain . ' ' . (array_key_exists('register', $domainInfo['prices']) ? $domainInfo['prices']['register'] : '') . ' ' . $domainInfo['country'],
                ($synced ? 'success' : 'error')
            );

            $supplier = new \AbraFlexi\RW([
                'cenik' => $this->getRecordCode(),
                'firma' => 'code:GRANSY',
                'kodIndi' => $this->getDataValue('kod'),
                'primarni' => true,
                'nakupCena' => $this->getDataValue('nakupCena'),
                    ], ['evidence' => 'dodavatel']);

            if ($supplier->recordExists(['firma' => 'code:GRANSY', 'kodIndi' => $this->getDataValue('kod')])) {
                $supplier->insertToAbraFlexi(['id' => $supplier->lastResult['dodavatel'][0]['id'],
                    'nakupCena' => $this->getDataValue('nakupCena')]);
            } else {
                if ($supplier->sync()) {
                    $supplier->addStatusMessage(sprintf(
                        _('GRANSY is supplier for %s now'),
                        $this->getDataValue('kod')
                    ), 'success');
                }
            }
        }
    }

    public function addFlag($code)
    {
        $flagFile = 'images/country-flags/png250px/' . $code . '.png';

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

    function getCountries()
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
