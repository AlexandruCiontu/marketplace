<?php

namespace App\Services;

use App\Models\User;
use App\Models\Vendor;

class TransactionClassifierService
{
    /**
     * Classifies a transaction as 'Domestic' or 'OSS'.
     *
     * @param Vendor $vendor The vendor selling the product.
     * @param User $client The customer buying the product.
     * @return string The transaction type ('Domestic' or 'OSS').
     */
    public function classify(Vendor $vendor, User $client): string
    {
        // Assume the client has an associated country.
        // In the B2C scenario, the client's country is determined at checkout,
        // possibly based on the shipping address.
        if (empty($client->country_code)) {
            // Throw an exception or handle the case where the client's country is unknown.
            // For now, treat the transaction as domestic to avoid errors.
            return 'Domestic';
        }

        return $vendor->country_code === $client->country_code
            ? 'Domestic'
            : 'OSS';
    }
}
