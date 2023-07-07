<?php
namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class MobileDto{
    public function __construct(
        #[Assert\Length(
            min: 2, max: 255, 
            minMessage: 'Le model doit être contenu entre 2 et 255 caractères', 
            maxMessage: 'Le model doit être contenu entre 2 et 255 caractères'
        )]
        public readonly ?string $model = null,

        #[Assert\Length(min: 2, max: 255)]
        public readonly ?string $mark = null,

        #[Assert\Length(max: 255)]
        public readonly ?string $shortDescription  = null,

        #[Assert\Length( max: 1000)]
        public readonly ?string $longDescription  = null,
    ) { }
}