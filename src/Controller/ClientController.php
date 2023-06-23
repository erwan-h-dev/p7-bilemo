<?php

namespace App\Controller;

use App\Entity\User;
use App\Services\UserService;
use App\Repository\UserRepository;
use App\Repository\ClientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('api/utilisateurs')]
class ClientController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private ClientRepository $clientRepository,
        private UserRepository $userRepository,
        private UserService $userService
    ) { }

    #[Route(name: 'app_clients', methods: ['GET'])]
    public function apiUtilisateurs(Request $request, TagAwareCacheInterface $cache): JsonResponse
    {
        $client = $this->getUser();

        $page = $request->query->get('page', 1);
        $limit = $request->query->get('limit', 10);

        $cacheIndex = 'app_clients'. $client->getId().'_' . $page . '_' . $limit;

        $utilisateurs = $cache->get($cacheIndex, function (ItemInterface $item) use ($client, $page, $limit) {
            $item->tag("utilisateursCache");
            return $this->userRepository->findAllByClientPaginate($client, $page, $limit);
        });

        $cacheTotalUtilisateurIndex = 'app_utilisateurs_total';	

        $totalUtilisateur = $cache->get($cacheTotalUtilisateurIndex, function (ItemInterface $item) use ($client) {
            $item->tag("totalUtilisateursCache");
            return $this->userRepository->countByClient($client);
        });

        return $this->json([
            "data" => $utilisateurs,
            "page" => $page,
            "limit" => $limit,
            "total" => $totalUtilisateur,
        ], Response::HTTP_OK, [], ['groups' => 'users']);
    }

    #[Route('/{id}', name: 'app_client', methods: ['GET'])]
    public function apiUtilisateur(User $user): JsonResponse
    {

        if($user->getClient() !== $this->getUser()) {
            return $this->json([
                'message' => 'Vous n\'avez pas les droits suffisants pour accéder à cet utilisateur'
            ], Response::HTTP_FORBIDDEN);
        }

        return $this->json($user, Response::HTTP_OK, [], ['groups' => 'user']);
    }

    #[Route(name: 'app_utilisateur_create', methods: ['POST'])]
    public function apiUtilisateurCreate(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $user = $this->userService->createUser($data);

        return $this->json([
            'message' => 'utilisateur Created',
            'id' => $user->getId()
        ], Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'app_utilisateur_update', methods: ['PATCH'])]
    public function apiUtilisateurUpdate(Request $request, User $user): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $this->userService->updateUser($user, $data);

        return $this->json([
            'message' => 'user Updated',
        ], Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'app_utilisateur_delete', methods: ['DELETE'])]
    public function apiUtilisateurDelete(User $user, TagAwareCacheInterface $cache): JsonResponse
    {
        $cache->invalidateTags(["utilisateursCache", "totalUtilisateursCache"]);
        
        $this->em->remove($user);
        $this->em->flush();

        return $this->json([
            'message' => 'user Deleted',
        ], Response::HTTP_NO_CONTENT);
    }
}