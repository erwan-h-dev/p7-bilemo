<?php

namespace App\Services;

use App\Dto\UserDto;
use App\Entity\User;
use JMS\Serializer\SerializerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class UserService 
{
    public function __construct(
        private EntityManagerInterface $em,
        private SerializerInterface $serializer
    ) {
    }

    public function update(User $user, UserDto $userDto): void
    {
        if($userDto->firstName){
            $user->setFirstName($userDto->firstName);
        }
        if($userDto->lastName){
            $user->setLastName($userDto->lastName);
        }
        if($userDto->email){
            $user->setEmail($userDto->email);
        }
        if($userDto->adress){
            $user->setAdress($userDto->adress);
        }
        if($userDto->postalCode){
            $user->setPostalCode($userDto->postalCode);
        }
        if($userDto->city){
            $user->setCity($userDto->city);
        }
       
        $this->em->flush();
    }

    public function create(Request $request): User
    {
        $user = $this->serializer->deserialize($request->getContent(), User::class, 'json');

        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }
}