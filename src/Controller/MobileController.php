<?php

namespace App\Controller;

use App\Entity\Mobile;
use App\Repository\MobileRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('api/mobiles')]
class MobileController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private SerializerInterface $serializer,
        private MobileRepository $mobileRepository
    ){ }

    #[Route(name: 'app_mobiles', methods: ['GET'])]
    public function apiMobiles(Request $request, TagAwareCacheInterface $cache): JsonResponse
    {
        $page = $request->query->get('page', 1);
        $limit = $request->query->get('limit', 10);

        $cacheIndex = 'app_mobiles_' . $page . '_' . $limit;

        $mobiles = $cache->get($cacheIndex, function(ItemInterface $item) use ($page, $limit) {
            $item->tag("mobilesCache");
            return $this->mobileRepository->findAllPaginate($page, $limit);
        });

        $cacheTotalMobileIndex = 'app_mobiles_total';

        $totalMobile = $cache->get($cacheTotalMobileIndex, function(ItemInterface $item) {
            $item->tag("totalMobilesCache");
            return $this->mobileRepository->count([]);
        });
       
        return $this->json([
            "data" => $mobiles,
            "page" => $page,
            "limit" => $limit,
            "total" => $totalMobile,
        ], Response::HTTP_OK, [], ['groups' => 'mobiles']);
    }

    #[Route('/{id}', name: 'app_mobile', methods: ['GET'])]
    public function apiMobile(Mobile $mobile): JsonResponse
    {
        return $this->json($mobile, Response::HTTP_OK, [], ['groups' => 'mobile']);
    }

    #[Route(name: 'app_mobile_create', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour créer un livre')]
    public function apiMobileCreate(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $mobile = new Mobile();

        $mobile->setModel($data['model'])
            ->setMark($data['mark'])
            ->setShortDescription($data['shortDescription'])
            ->setLongDescription($data['longDescription'])
            ->setCreatedAt(new \DateTimeImmutable())
            ->setUpdatedAt(new \DateTimeImmutable())
        ;

        $this->em->persist($mobile);
        $this->em->flush();

        return $this->json([
            'message' => 'mobile Created',
            'id' => $mobile->getId()
        ], Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'app_mobile_update', methods: ['PATCH'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour modifier ce livre')]
    public function apiMobileUpdate(Request $request, Mobile $mobile): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $mobile->setModel($data['model'])
            ->setMark($data['mark'])
            ->setShortDescription($data['shortDescription'])
            ->setLongDescription($data['longDescription'])
            ->setUpdatedAt(new \DateTimeImmutable())
        ;

        $this->em->persist($mobile);
        $this->em->flush();

        return $this->json([
            'message' => 'mobile Updated',
        ], Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'app_mobile_delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour supprimer ce livre')]
    public function apiMobileDelete(Mobile $mobile): JsonResponse
    {
        $this->em->remove($mobile);
        $this->em->flush();

        return $this->json([
            'message' => 'mobile Deleted',
        ], 200);
    }
}