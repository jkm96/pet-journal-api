<?php

namespace App\Services\User;

use App\Http\Requests\CreatePetRequest;

class PetService
{

    public function createPetProfile($createPetRequest)
    {
        dd($createPetRequest);
    }
}
