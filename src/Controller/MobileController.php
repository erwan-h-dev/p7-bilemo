<?php

namespace App\Controller;

use App\Dto\MobileDto;
use App\Entity\Mobile;
use App\Services\MobileService;
use App\Services\VersioningService;
use App\Repository\MobileRepository;
use JMS\Serializer\SerializerInterface;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializationContext;
use OpenApi\Attributes\RequestBody;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;

#[Route('api/mobiles')]
class MobileController extends AbstractController
{
    
    public function __construct(
        private EntityManagerInterface $em,
        private TagAwareCacheInterface $cache,
        private SerializerInterface $serializer,
        private MobileRepository $mobileRepository,
        private MobileService $mobileService,
        private VersioningService $versioningService
    ){ }

    /**
     * Retourne une liste paginé de mobiles
     */
    #[Route(name: 'get_mobiles', methods: ['GET'])]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: "Renvoie la liste des mobiles",
        content: new OA\JsonContent(
            type: "array",
            items: new OA\Items(ref: new Model(type: Mobile::class, groups: ["mobiles"]))
        )
    )]
    #[OA\Response(
        response: Response::HTTP_UNAUTHORIZED,
        description: "JWT Token not found"
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
        description: "Nombre de mobiles par page",
        schema: new OA\Schema(type: "integer")
    )]
    #[OA\Tag(name: "Mobiles")]
    public function getMobiles(Request $request): JsonResponse
    {
        $page = $request->query->get('page', 1);
        $limit = $request->query->get('limit', 10);

        // nom de l'index du cache
        $cacheIndex = 'mobiles_' . $page . '_' . $limit;

        // récupération des données en cache
        $mobiles = $this->cache->get($cacheIndex, function(ItemInterface $item) use ($page, $limit) {
            // si le cache n'existe pas, on le crée
            $item->tag("mobilesCache");

            // renvoie les données de la requête
            return $this->mobileRepository->findAllPaginate($page, $limit);
        });

        // serialisation des données
        $context = SerializationContext::create()->setGroups(['mobiles']);
        $context->setVersion($this->versioningService->getVersion());
        $jsonMobiles = $this->serializer->serialize($mobiles, 'json', $context);

        return new JsonResponse($jsonMobiles, Response::HTTP_OK, [], true);
    }

    /**
     * Retourne un mobile en fonction de son id
     */
    #[Route('/{id}', name: 'get_mobile', methods: ['GET'])]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: "Retourne un mobile",
        content: new Model(type: Mobile::class)
    )]
    #[OA\Response(
        response: Response::HTTP_UNAUTHORIZED,
        description: "JWT Token not found"
    )]
    #[OA\Response(
        response: Response::HTTP_NOT_FOUND,
        description: "La ressource demandée n'existe pas."
    )]
    #[OA\Tag(name: "Mobiles")]
    public function getMobile(Mobile $mobile): JsonResponse
    {

        // appel du groupe de donnée souhaité
        $context = SerializationContext::create()->setGroups(['mobile']);
        $context->setVersion($this->versioningService->getVersion());
        $jsonMobile = $this->serializer->serialize($mobile, 'json', $context);

        return new JsonResponse($jsonMobile, Response::HTTP_OK, [], true);
    }
}