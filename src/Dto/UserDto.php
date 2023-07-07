<?php
namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class UserDto{
    public function __construct(

        #[Assert\Length(max: 255)]
        public readonly ?string $firstName = null,

        #[Assert\Length(max: 255)]
        public readonly ?string $lastName = null,

        #[Assert\Length(max: 255)]
        public readonly ?string $email = null,

        #[Assert\Length(max: 255)]
        public readonly ?string $adress = null,

        #[Assert\Length(max: 255)]
        public readonly ?string $postalCode = null,

        #[Assert\Length(max: 255)]
        public readonly ?string $city = null
    ) { }
}