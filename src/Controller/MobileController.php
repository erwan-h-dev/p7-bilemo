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
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

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

    #[Route(name: 'get_mobiles', methods: ['GET'])]
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

    #[Route('/{id}', name: 'get_mobile', methods: ['GET'])]
    public function getMobile(Mobile $mobile): JsonResponse
    {
        // appel du groupe de donnée souhaité
        $context = SerializationContext::create()->setGroups(['mobile']);
        $context->setVersion($this->versioningService->getVersion());
        $jsonMobile = $this->serializer->serialize($mobile, 'json', $context);

        return new JsonResponse($jsonMobile, Response::HTTP_OK, [], true);
    }

    #[Route(name: 'create_mobile', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour créer un livre')]
    public function createMobile(Request $request): JsonResponse
    {
        // création d'un nouveau mobile
        $mobile = $this->mobileService->create($request);

        $context = SerializationContext::create()->setGroups(['mobile']);
        $context->setVersion($this->versioningService->getVersion());
        $jsonMobile = $this->serializer->serialize($mobile, 'json', $context);

        return new JsonResponse($jsonMobile, Response::HTTP_CREATED, [], true);
    }

    #[Route('/{id}', name: 'update_mobile', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour modifier ce livre')]
    public function updateMobile(Mobile $mobile,  #[MapRequestPayload] MobileDto $mobileDto): JsonResponse
    {

        $this->mobileService->update($mobile, $mobileDto);

        $this->cache->invalidateTags(["mobilesCache"]);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/{id}', name: 'delete_mobile', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour supprimer ce livre')]
    public function deleteMobile(Mobile $mobile): JsonResponse
    {
        $this->cache->invalidateTags(["mobilesCache"]);

        $this->em->remove($mobile);
        $this->em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}