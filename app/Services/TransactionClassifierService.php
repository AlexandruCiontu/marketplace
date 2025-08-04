<?php

namespace App\Services;

use App\Models\User;
use App\Models\Vendor;

class TransactionClassifierService
{
    /**
     * Clasifică o tranzacție ca fiind 'Domestic' sau 'OSS'.
     *
     * @param Vendor $vendor Vendorul care vinde produsul.
     * @param User $client Clientul care cumpără produsul.
     * @return string Tipul tranzacției ('Domestic' sau 'OSS').
     */
    public function classify(Vendor $vendor, User $client): string
    {
        // Presupunem că clientul are o țară asociată.
        // În scenariul B2C, țara clientului este determinată la checkout,
        // posibil pe baza adresei de livrare.
        if (empty($client->country_code)) {
            // Aruncă o excepție sau gestionează cazul în care țara clientului nu este cunoscută.
            // Pentru moment, vom considera tranzacția ca fiind domestică pentru a evita erori.
            return 'Domestic';
        }

        return $vendor->country_code === $client->country_code
            ? 'Domestic'
            : 'OSS';
    }
}
