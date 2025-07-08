<?php

/**
 * Subreg2AbraFlexi - AbraFlexi Customers handler class.
 *
 * @author Vítězslav Dvořák <vitezslav.dvorak@spojenet.cz>
 * @copyright  2020-2024 SpojeNet s.r.o.
 */

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

/**
 * Description of Customers.
 *
 * @author vitex
 *
 * @no-named-arguments
 */
class Customers extends \AbraFlexi\Adresar
{
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
            \AbraFlexi\RO::code($addressCode),
            ['detail' => 'id'],
        );
        $subregID = $customer->getExternalID('subreg');

        if (empty($subregID)) {
            $this->addStatusMessage(
                \AbraFlexi\RO::uncode($addressCode).': '._('SubReg client without ext:subreg:xxx'),
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
     * AbraFlexi Customers list with ext subregID.
     *
     * @return array<string, int>
     */
    public function getCustomers(): array
    {
        $customers = [];
        $candidates = $this->getColumnsFromAbraFlexi('summary', ['limit' => 0]);

        foreach ($candidates as $candidat) {
            $this->setData($candidat);

            if ($subregId = $this->getExternalID('subreg')) {
                $customers[$this->getRecordCode()] = (int) $subregId;
            }
        }

        return $customers;
    }
}
