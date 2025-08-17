<?php

declare(strict_types=1);

/**
 * This file is part of the Subreg2AbraFlexi package
 *
 * https://github.com/Spoje-NET/subreg2abraflexi/
 *
 * (c) Vítězslav Dvořák <http://spojenet.cz/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SpojeNet\SubregAbraFlexi;

use SpojeNet\System\SubregAPI;

/**
 * Description of VoIP.
 *
 * @author vitex
 */
class DomainCredit extends \Ease\Brick
{
    /**
     * Name of Product/Service.
     */
    public string $name = null;

    /**
     * AbraFlexi storage item Code.
     */
    public string $productCode = 'KREDIT_DOMENA';

    /**
     * Offer this Item only to Customer with this extId filled ext:subreg:12345.
     *
     * @var string subreg|subreg|etc ...
     */
    public string $reqExtPrefix = 'subreg';


    public function processInvoice( \AbraFlexi\FakturaVydana $invoice) {
        
    }

    /**
     * Do this when item is settled.
     *
     * @param FakturaVydana $invoice
     */
    public function settled($invoice)
    {
        $result = false;
        $itemsOfInterest = $this->getItemsOfInterest($invoice);

        if (
            \count($itemsOfInterest) && \array_key_exists(
                'API',
                \AbraFlexi\Stitek::listToArray($invoice->getDataValue('stitky')),
            )
        ) {
            $subregLogin = $this->abraFlexiAddressToSubregUserName($invoice->getDataValue('firma'));

            if ($subregLogin) {
                $price = 0;

                foreach ($itemsOfInterest as $creditItemData) {
                    if (isset($creditItemData['mnozMj'])) {
                        $itemPrice = $creditItemData['mnozMj'] * $creditItemData['cenaMj'];
                    } else {
                        $itemPrice = (float) $creditItemData['cenaMj'];
                    }

                    $price += $itemPrice;
                }

                $initialCredit = $this->abraFlexiAddressToSubregCredit($invoice->getDataValue('firma'));
                $this->subreger()->call(
                    'Credit_Correction',
                    ['username' => $subregLogin, 'amount' => $price, 'reason' => 'faktura '.$invoice->getRecordIdent().' uhrazena'],
                );
                $this->addStatusMessage(
                    sprintf(
                        _('SubReg add credit %s for %s by %s'),
                        $price,
                        $subregLogin.'/'.\AbraFlexi\RO::uncode($invoice->getDataValue('firma')),
                        $invoice->getRecordIdent(),
                    ),
                    ($this->subreger()->lastResult === 'ok') ? 'success' : 'error',
                );
                $finalCredit = $this->abraFlexiAddressToSubregCredit($invoice->getDataValue('firma'));

                if (empty($finalCredit) || ($initialCredit === $finalCredit)) {
                    $this->addStatusMessage('Credit adding problem', 'event');
                }

                $result = parent::settled($invoice);
            } else {
                $this->addStatusMessage(
                    sprintf(
                        _('%s not found as subreg clinet'),
                        \AbraFlexi\RO::uncode($invoice->getDataValue('firma')),
                    ),
                    'error',
                );
            }
        }

        return $result;
    }

    /**
     * Obtain Subreg userName for AbraFlexi address.
     *
     * @param string $addressCode AbraFlexi address code
     *
     * @return string
     */
    public function abraFlexiAddressToSubregUserName($addressCode)
    {
        $subregLogin = null;
        $customer = new \AbraFlexi\Adresar(
            \AbraFlexi\Functions::code($addressCode),
            ['detail' => 'id'],
        );
        $subregID = $customer->getExternalID('subreg');

        if (empty($subregID)) {
            $this->addStatusMessage(
                \AbraFlexi\Functions::uncode($addressCode).': '._('SubReg client without ext:subreg:xxx'),
                'warning',
            );
        } else {
            $userListRaw = $this->subreger()->call('Users_List');

            if ((int) $userListRaw['count']) {
                foreach ($userListRaw['users'] as $user) {
                    if ($user['id'] === $subregID) {
                        $subregLogin = $user['username'];

                        break;
                    }
                }
            }
        }

        return $subregLogin;
    }

    /**
     * Subreg ApiClient.
     *
     * @return SubregAPI
     */
    public function subreger()
    {
        if (null === $this->subreg) {
            $this->subreg = new SubregAPI();
            $this->subreg->login();
        }

        return $this->subreg;
    }

    /**
     * @param string $addressCode
     *
     * @return string
     */
    public function abraFlexiAddressToSubregCredit($addressCode)
    {
        $customer = new \AbraFlexi\Adresar(
            \AbraFlexi\Functions::code($addressCode),
            ['detail' => 'id'],
        );
        $subregID = $customer->getExternalID('subreg');
        $userInfo = $subregID ? $this->subreger()->infoUser($subregID) : [];

        return \is_array($userInfo) && \array_key_exists('credit', $userInfo) ? $userInfo['credit'] : null;
    }
}
