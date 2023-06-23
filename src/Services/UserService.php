<?php

namespace App\Services;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class UserService 
{
    public function __construct(
        private EntityManagerInterface $em,
    ){ }

    public function createUser(array $data): User
    {
        $user = new User();

        $user->setFirstName($data['firstName'])
            ->setLastName($data['lastName'])
            ->setEmail($data['email'])
            ->setAdress($data['adress'])
            ->setPostalCode($data['postalCode'])
            ->setCity($data['city']);

        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }

    public function updateUser(User $user, array $data): void
    {
        $user->setFirstName($data['firstName'])
            ->setLastName($data['lastName'])
            ->setEmail($data['email'])
            ->setAdress($data['adress'])
            ->setPostalCode($data['postalCode'])
            ->setCity($data['city'])
        ;

        $this->em->flush();
    }
}

