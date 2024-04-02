<?php

/**
 * Subreg2AbraFlexi - AbraFlexi Customers handler class.
 *
 * @author Vítězslav Dvořák <vitezslav.dvorak@spojenet.cz>
 * @copyright  2020-2024 SpojeNet s.r.o.
 */

declare(strict_types=1);

namespace SpojeNet\SubregAbraFlexi;

/**
 * Description of Customers
 *
 * @author vitex
 */
class Customers extends \AbraFlexi\Adresar
{
    /**
     * Obtain Subreg userName for AbraFlexi address
     *
     * @param string $addressCode AbraFlexi address code
     *
     * @return string
     */
    public function abraFlexiAddressToSubregUserName($addressCode)
    {
        $subregLogin = null;
        $customer = new \AbraFlexi\Adresar(
            \AbraFlexi\RO::code($addressCode),
            ['detail' => 'id']
        );
        $subregID = $customer->getExternalID('subreg');
        if (empty($subregID)) {
            $this->addStatusMessage(
                \AbraFlexi\RO::uncode($addressCode) . ': ' . _('SubReg client without ext:subreg:xxx'),
                'warning'
            );
        } else {
            $userListRaw = $this->subreger()->call('Users_List');

            if (intval($userListRaw['count'])) {
                foreach ($userListRaw['users'] as $user) {
                    if ($user['id'] == $subregID) {
                        $subregLogin = $user['username'];
                        break;
                    }
                }
            }
        }
        return $subregLogin;
    }

    public function getCustomers()
    {
        $customers = [];
        $candidates = $this->getColumnsFromAbraFlexi('summary', ['limit' => 0]);
        foreach ($candidates as $candidat) {
            $this->setData($candidat);
            if ($subregId = $this->getExternalID('subreg')) {
                $customers[$this->getRecordCode()] = $subregId;
            }
        }
        return $customers;
    }
}
