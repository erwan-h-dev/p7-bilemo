<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Client;
use App\Entity\Mobile;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher,
        private TagAwareCacheInterface $cache
    ){ }
    
    public function load(ObjectManager $em): void
    {
        
        $admin = new Client();
        
        $client = new Client();

        $admin->setUsername('admin')
            ->setRoles(['ROLE_ADMIN'])
        ;

        $client->setUsername('user')
            ->setRoles(['ROLE_USER'])
        ;

        $hashedPassword = $this->passwordHasher->hashPassword(
            $admin,
            "password"
        );
        
        $admin->setPassword($hashedPassword);
        $client->setPassword($hashedPassword);

        $em->persist($admin);
        $em->persist($client);

        for($i = 1; $i <= 10; $i++) {

            $mobile = new Mobile();

            $mobile->setModel("Model $i")
                ->setMark("Mark $i")
                ->setShortDescription("Short description $i")
                ->setLongDescription("Long description $i")
                ->setCreatedAt(new \DateTimeImmutable())
                ->setUpdatedAt(new \DateTimeImmutable())
            ;

            $em->persist($mobile);
        }

        for ($i = 1; $i <= 10; $i++) {

            $user = new User();

            $user->setFirstName("First name $i")
                ->setLastName("Last name $i")
                ->setEmail("email-".$i."@gmail.com")
                ->setAdress("Adress $i")
                ->setPostalCode("Postal code $i")
                ->setCity("City $i")
            ;

            $client->addUser($user);

            $em->persist($user);
        }
        
        $em->flush();

        $this->cache->invalidateTags(["mobilesCache", "usersCache"]);
    }
}
