<?php

namespace App\Controller;

use App\DTO\UserDTO\UpdateUserDTO;
use App\DTO\UserDTO\UpdateRolesDTO;
use App\Enum\Roles;
use App\Repository\UserRepository;
use App\Security\Voter\UserVoter;
use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

final class UserController extends AbstractController
{

    public function __construct(private readonly UserService $userService, private readonly RequestStack $requestStack){}

    #[Route('/api/user/{id}', name: 'app_user', methods: ['GET'])]
    public function show(string $id): JsonResponse
    {
        return $this->json($this->userService->fetchUser($id));
    }

    #[Route('/api/user', name: 'app_search_user', methods: ['GET'])]
    public function search(Request $request): JsonResponse
    {
        $page = $request->query->getInt('page', 1);
        $limit = min($request->query->getInt('limit', 20), 100);

        $filters = $request->query->all();
        unset($filters['page'], $filters['limit'], $filters['orderBy']);

        $results = $this->userService->fetchAll($filters, $page, $limit);

        return $this->json($results);
    }


    /**
     * @throws ExceptionInterface
     */
    #[Route('/api/user/{id}', name: 'api_update_user', requirements: ["id"=>Requirement::DIGITS], methods: ['PUT'])]
    public function update(int $id, #[MapRequestPayload] UpdateUserDTO $dto, UserRepository $userRepository): JsonResponse
    {
        $user = $userRepository->find($id);
        if(!$user){
            throw new NotFoundHttpException("User not found");
        }

        $this->denyAccessUnlessGranted(UserVoter::EDIT, $user);

        $result = $this->userService->updateUser($dto, $user);


        return $this->json($result);

    }

    /**
     * @throws ExceptionInterface
     */
    #[Route('/api/user/change-role/{id}', name: 'api_update_role_user', requirements: ["id"=>Requirement::DIGITS], methods: ['PUT'])]
    public function changeRole(int $id, #[MapRequestPayload] UpdateRolesDTO $dto,  UserRepository $userRepository): JsonResponse
    {
        $user = $userRepository->find($id);
        if(!$user){
            throw new NotFoundHttpException("User not found");
        }

        $this->denyAccessUnlessGranted(UserVoter::EDITROLES, $user);

        if(in_array("ROLE_ADMIN", $this->getUser()->getRoles()) && $dto->roles==Roles::SUPERADMIN->name){
            throw new AccessDeniedHttpException("Access denied.");
        }
        $result = $this->userService->updateRoleUser($dto, $user);

        return $this->json($result);

    }

    #[Route('/api/user/{id}', name: 'app_remove_user', requirements: ["id"=>Requirement::DIGITS], methods: ['DELETE'])]

    public function remove(int $id, UserRepository $userRepository): JsonResponse
    {
        $user = $userRepository->find($id);
        if(!$user){
            throw new NotFoundHttpException("User not found");
        }

        $this->denyAccessUnlessGranted(UserVoter::DELETE, $user);
        return $this->json($this->userService->removeUser($id));
    }
}
