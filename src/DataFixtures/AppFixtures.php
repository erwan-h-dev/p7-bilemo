<?php

namespace App\DataFixtures;

use App\Entity\Client;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    ){ }
    
    public function load(ObjectManager $em): void
    {
        $admin = new Client();

        $admin->setUsername('admin')
            ->setRoles(['ROLE_ADMIN'])
        ;

        $hashedPassword = $this->passwordHasher->hashPassword(
            $admin,
            "password"
        );
        
        $admin->setPassword($hashedPassword);

        $em->persist($admin);
        $em->flush();
    }
}
