<?php

namespace App\Controller;

use App\Dto\UserDto;
use App\Entity\User;
use App\Entity\Client;
use App\Services\UserService;
use OpenApi\Attributes as OA;
use App\Repository\UserRepository;
use App\Services\VersioningService;
use App\Repository\ClientRepository;
use JMS\Serializer\SerializerInterface;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializationContext;
use Nelmio\ApiDocBundle\Annotation\Model;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
#[Route('api/clients/')]
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

    /**
     * Retourne une liste paginé de users
     */
    #[Route(path:'{client_id}/users', name: 'get_users', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: "Renvoie la liste des users",
        content: new OA\JsonContent(
            type: "array",
            items: new OA\Items(ref: new Model(type: User::class, groups: ["users"]))
        )
    )]
    #[OA\Parameter(
        name: "page",
        in: "query",
        description: "Numéro de la page",
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\Parameter(
        name: "limit",
        in: "query",
        description: "Nombre d'users par page",
        schema: new OA\Schema(type: "integer")
    )]
    #[OA\Tag(name: "Users")]
    #[ParamConverter('client', options: ['mapping' => ['client_id' => 'id']])]
    public function getUsers(Client $client, Request $request): JsonResponse
    {
        $page = $request->query->get('page', 1);
        $limit = $request->query->get('limit', 10);

        $cacheIndex = 'users'. $client->getId().'_' . $page . '_' . $limit;

        $users = $this->cache->get($cacheIndex, function (ItemInterface $item) use ($client, $page, $limit) {
            $item->tag("usersCache");
            return $this->userRepository->findAllByClientPaginate($client, $page, $limit);
        });
        foreach($users as $user) {
            $this->denyAccessUnlessGranted('show', $user);
        }
        
        $context = SerializationContext::create()->setGroups(['users']);
        $context->setVersion($this->versioningService->getVersion());
        $jsonUsers = $this->serializer->serialize($users, 'json', $context);

        return new JsonResponse($jsonUsers, Response::HTTP_OK, [], true);
    }

    /**
     * Retourne un user
     */
    #[Route('{client_id}/users/{user_id}', name: 'get_user', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: "Retourne un user",
        content: new OA\JsonContent(
            type: "array",
            items: new OA\Items(ref: new Model(type: User::class, groups: ["user"]))
        )
    )]
    #[OA\Tag(name: "Users")]
    #[IsGranted('show', 'user', 'Vous n\'avez pas les droits suffisants pour modifier cet user')]
    #[ParamConverter('client', options: ['mapping' => ['client_id' => 'id']])]
    #[ParamConverter('user', options: ['mapping' => ['user_id' => 'id']])]
    public function get_User(Client $client, User $user): JsonResponse // getUser method is already used by Symfony in AbstractController
    {
        if($user->getClient() !== $client) {
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

    /**
     * Créer un user
     */
    #[Route(path: '{client_id}/users', name: 'create_user', methods: ['POST'])]
    #[OA\RequestBody(
        description: "data du user",
        required: true,
        content: new OA\JsonContent(
            type: "array",
            items: new OA\Items(ref: new Model(type: User::class, groups: ["create"]))
        )
    )]
    #[OA\Tag(name: "Users")]
    #[IsGranted('ROLE_USER')]
    #[ParamConverter('client', options: ['mapping' => ['client_id' => 'id']])]
    public function createUser(Request $request, Client $client): JsonResponse
    {
        if($client !== $this->security->getUser()) {
            return $this->json([
                'message' => 'Vous n\'avez pas les droits suffisants pour créer un user'
            ], Response::HTTP_FORBIDDEN);
        }

        $user = $this->userService->create($request, $client);
        
        $this->cache->invalidateTags(["usersCache"]);

        $context = SerializationContext::create()->setGroups(['user']);
        $context->setVersion($this->versioningService->getVersion());
        
        $jsonUser = $this->serializer->serialize($user, 'json', $context);

        return new JsonResponse($jsonUser, Response::HTTP_CREATED, [], true);
    }

    /**
     * Modifie un user
     */
    #[Route('{client_id}/users/{user_id}', name: 'update_user', methods: ['PATCH'])]
    #[OA\Response(
        response: Response::HTTP_NO_CONTENT,
        description: "Modifie un user",
        content: null
    )]
    #[OA\RequestBody(
        description: "data du user",
        required: true,
        content: new OA\JsonContent(
            type: "array",
            items: new OA\Items(ref: new Model(type: User::class, groups: ["create"]))
        )
    )]
    #[OA\Tag(name: "Users")]
    #[IsGranted('edit', 'user', 'Vous n\'avez pas les droits suffisants pour modifier cet user')]
    #[ParamConverter('client', options: ['mapping' => ['client_id' => 'id']])]
    #[ParamConverter('user', options: ['mapping' => ['user_id' => 'id']])]
    public function updateUser(User $user, Client $client, #[MapRequestPayload] UserDto $userDto): JsonResponse
    {
        $this->userService->update($user, $userDto);

        $this->cache->invalidateTags(["usersCache"]);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Supprime un user
     */
    #[Route('{client_id}/users/{user_id}', name: 'delete_user', methods: ['DELETE'])]
    #[OA\Response(
        response: Response::HTTP_NO_CONTENT,
        description: "supprime un user",
        content: null
    )]
    #[OA\Tag(name: "Users")]
    #[IsGranted('delete', 'user', 'Vous n\'avez pas les droits suffisants pour supprimer cet user')]
    #[ParamConverter('user', options: ['mapping' => ['user_id' => 'id']])]
    public function deleteUser(User $user): JsonResponse
    {
        $this->cache->invalidateTags(["usersCache"]);
        
        $this->em->remove($user);
        $this->em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}