<?php

namespace App\Services;

use App\Dto\MobileDto;
use App\Entity\Mobile;
use JMS\Serializer\SerializerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class MobileService
{

    public function __construct(
        private EntityManagerInterface $em,
        private SerializerInterface $serializer
    ){ }
    
    public function update(Mobile $mobile, MobileDto $mobileDto): void
    {
        if($mobileDto->model) {
            $mobile->setModel($mobileDto->model);
        }
        
        if($mobileDto->mark) {
            $mobile->setMark($mobileDto->mark);
        }

        if($mobileDto->shortDescription) {
            $mobile->setShortDescription($mobileDto->shortDescription);
        }

        if($mobileDto->longDescription) {
            $mobile->setLongDescription($mobileDto->longDescription);
        }
        
        $this->em->flush();
    }

    public function create(Request $request): Mobile
    {
        $mobile = $this->serializer->deserialize($request->getContent(), Mobile::class, 'json');
        
        $this->em->persist($mobile);
        $this->em->flush();

        return $mobile;
    }
}