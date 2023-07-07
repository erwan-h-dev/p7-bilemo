<?php

namespace App\Controller;

use App\Dto\UserDto;
use App\Entity\User;
use App\Services\UserService;
use App\Repository\UserRepository;
use App\Services\VersioningService;
use App\Repository\ClientRepository;
use JMS\Serializer\SerializerInterface;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializationContext;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
#[Route('api/users')]
class ClientController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private Security $security,
        private SerializerInterface $serializer,
        private TagAwareCacheInterface $cache,
        private ClientRepository $clientRepository,
        private UserRepository $userRepository,
        private UserService $userService,
        private VersioningService $versioningService
    ) { }

    #[Route(name: 'get_users', methods: ['GET'])]
    public function getUsers(Request $request): JsonResponse
    {
        $client = $this->getUser();

        $page = $request->query->get('page', 1);
        $limit = $request->query->get('limit', 10);

        $cacheIndex = 'users'. $client->getId().'_' . $page . '_' . $limit;

        $users = $this->cache->get($cacheIndex, function (ItemInterface $item) use ($client, $page, $limit) {
            $item->tag("usersCache");
            return $this->userRepository->findAllByClientPaginate($client, $page, $limit);
        });

        $context = SerializationContext::create()->setGroups(['users']);
        $context->setVersion($this->versioningService->getVersion());
        $jsonUsers = $this->serializer->serialize($users, 'json', $context);

        return new JsonResponse($jsonUsers, Response::HTTP_OK, [], true);
    }

    #[Route('/{id}', name: 'get_user', methods: ['GET'])]
    public function get_User(User $user): JsonResponse
    {
        if($user->getClient() !== $this->getUser()) {
            return $this->json([
                'message' => 'Vous n\'avez pas les droits suffisants pour accéder à cet user'
            ], Response::HTTP_FORBIDDEN);
        }

        // appel du groupe de donnée souhaité
        $context = SerializationContext::create()->setGroups(['user']);
        $context->setVersion($this->versioningService->getVersion());
        $jsonUser = $this->serializer->serialize($user, 'json', $context);

        return new JsonResponse($jsonUser, Response::HTTP_OK, [], true);
    }

    #[Route(name: 'create_user', methods: ['POST'])]
    public function createUser(Request $request): JsonResponse
    {

        $user = $this->userService->create($request);

        $this->cache->invalidateTags(["usersCache"]);

        $context = SerializationContext::create()->setGroups(['user']);
        $context->setVersion($this->versioningService->getVersion());
        $jsonUser = $this->serializer->serialize($user, 'json', $context);

        return new JsonResponse($jsonUser, Response::HTTP_CREATED, [], true);
    }

    #[Route('/{id}', name: 'update_user', methods: ['POST'])]
    public function updateUser(User $user, #[MapRequestPayload] UserDto $userDto): JsonResponse
    {
        $this->userService->update($user, $userDto);

        $this->cache->invalidateTags(["usersCache"]);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/{id}', name: 'delete_user', methods: ['DELETE'])]
    public function deleteUser(User $user): JsonResponse
    {
        $this->cache->invalidateTags(["usersCache"]);
        
        $this->em->remove($user);
        $this->em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}