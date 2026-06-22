<?php

namespace App\Controller;

use App\Controller\EntityController\RoomController;
use App\Controller\EntityController\UserController;
use App\Entity\Level;
use App\Entity\Room;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use mysql_xdevapi\Exception;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Flex\Response;

class TeacherPageController extends AbstractController
{

    public function getTeacher(EntityManagerInterface $entityManager){
        return $entityManager->getRepository(User::class)->findOneBy(array('username'=>$this->getUser()->getUserIdentifier()));
    }

    public function getCurrentRoom(EntityManagerInterface $entityManager, $roomId){
        return $entityManager->getRepository(Room::class)->find($roomId);
    }

    #[Route('/teacher', name: 'app_teacherpage_teacherhomepage')]
    public function teacherHomePage(EntityManagerInterface $entityManager)
    {
        $teacher = $this->getTeacher($entityManager);
        $teacherRooms = $teacher->getRooms();
        $data = [];
        foreach ($teacherRooms as $i) {
            $data[] = ['roomId' => $i->getId(), 'roomName' => $i->getRoomName(), 'roomDesciption' => $i->getRoomDescription()];
        }
        if ($data == null){
            return $this->render('Teacher/TeacherHomepage.html.twig', [
                'pageName' => 'Crée un salon',
            ]);
        }else {
            $columnName = ['column1' => 'Nom du salon',
                'column2' => 'Description du salon'
            ];
            return $this->forward('\App\Controller\ListController::renderBaseList', [
                'pageName' => 'Salons',
                'data' => $data,
                'columnName' => $columnName,
                'listClasses' => 'TeacherRoomsList',
                'itemPath' => 'List/Teacher/TeacherList.html.twig',
                'basePath' => 'Teacher/TeacherHomepage.html.twig',
            ]);
        }
    }
    #[Route('/teacher-create-room', methods: 'POST')]
    public function teacherCreateRoom(EntityManagerInterface $entityManager, Request $request)
    {
        $postData = json_decode($request->getContent(), true);

        $roomName = $postData["roomName"] ??  '';
        $roomDescription = $postData["roomDescription"] ?? '';

        if (!empty($roomName) && !empty($roomDescription)){
            RoomController::newRoom($entityManager,$roomName,$roomDescription,$this->getTeacher($entityManager));
        }

        return new JsonResponse();
    }

    #[Route('/teacher/{slug}')]
    public function teacherRoom(EntityManagerInterface $entityManager,$slug = null)
    {
        if($slug == 'create-room'){
            return $this->render('Teacher/TeacherCreateRoom.html.twig', [
                'pageName' => 'Crée un salon',
            ]);
        }

        $teacher = $this->getTeacher($entityManager);
        $teacherCurrentRoom = $this->getCurrentRoom($entityManager,$slug);

        if (!$teacherCurrentRoom) {
            return $this->redirectToRoute('app_teacherpage_teacherhomepage');
        } else if ($teacherCurrentRoom->getTeacher() !== $teacher) {
            return $this->redirectToRoute('app_teacherpage_teacherhomepage');
        }

        $data = [];
        $teacherRoomLevels = $teacherCurrentRoom->getLevels();
        $students = $teacherCurrentRoom->getStudents();
        foreach ($teacherRoomLevels as $level){
            $studentsError = 0;
            $studentsCompletedRate = 0;
            if($students != null) {
                foreach ($students as $student) {
                    $studentsError += UserController::getErrorCount($entityManager, $student->getId(), $level);
                    $studentsCompletedRate += UserController::getCompletesCount($entityManager, $student->getId(), $level);

                }
                if ($students->count() > 0) {
                    $studentsError /= $students->count();
                    $studentsCompletedRate *= 100;
                    $studentsCompletedRate /= $students->count();
                }
            }



            $data[] = ['levelName' => $level->getDisplayName(), 'levelErrorRate' => round($studentsError, 1), 'levelCompletion' => round($studentsCompletedRate), 'levelId' => $level->getId()];
        }

        if ($data == null) {
            return $this->render('Teacher/TeacherRoom.html.twig', [
                'pageName' => $teacherCurrentRoom->getRoomName(),
                'id' => $teacherCurrentRoom->getId(),
                'popupAcceptedText' => 'Voulez vous vraiment supprimer le salon : '.$teacherCurrentRoom->getRoomName().' ?'
            ]);
        }else{
            $columnName = ['column1' => 'Position',
                'column2' => 'Nom du chapitre',
                'column3' => "Nombre moyen d'erreur",
                'column4' => 'Pourcentage de réussite',
                'column5' => 'Supprimer le chapitre',
            ];

            return $this->forward('\App\Controller\ListController::renderBaseList', [
                'pageName' => $teacherCurrentRoom->getRoomName(),
                'data' => $data,
                'columnName' => $columnName,
                'id' => $teacherCurrentRoom->getId(),
                'popupAcceptedText' => 'Voulez vous vraiment supprimer le salon : '.$teacherCurrentRoom->getRoomName().' ?',
                'itemPath' => 'List/Teacher/TeacherRoomList.html.twig',
                'basePath' => 'Teacher/TeacherRoom.html.twig',
                'listClasses' => 'teacherRoomLevelList',
            ]);
        }
    }

    #[Route('/teacher-delete-room', methods: 'POST')]
    public function teacherDeleteRoom(EntityManagerInterface $entityManager, Request $request): \Symfony\Component\HttpFoundation\Response
    {
        $postData = json_decode($request->getContent(), true);
        $roomId = $postData["roomId"] ??  0;

        if ($roomId != 0){
            try {
                RoomController::deleteRoom($entityManager,$roomId);
            } catch (\Exception $e) {
            }
        }
        return new \Symfony\Component\HttpFoundation\Response();
    }
    #[Route('/teacher/{slug}/move-up-level', methods: 'POST')]
    public function teacherMoveUpLevel(EntityManagerInterface $entityManager, Request $request,$slug = null): \Symfony\Component\HttpFoundation\Response
    {
        $postData = json_decode($request->getContent(), true);
        $levelId = $postData["levelId"] ??  0;

        if ($levelId != 0){
            $teacherCurrentRoom = $this->getCurrentRoom($entityManager,$slug);
            $levelToMove = $entityManager->getRepository(Level::class)->find($levelId);
            try {
                //Ne change pas les donnée de la BD actuellement.
                RoomController::changeLevelPosition($entityManager, $teacherCurrentRoom->getId(), $levelToMove, -1);
            } catch (\Exception $e) {
            }
        }
        return new \Symfony\Component\HttpFoundation\Response();
    }
    #[Route('/teacher/{slug}/move-down-level', methods: 'POST')]
    public function teacherMoveDownLevel(EntityManagerInterface $entityManager, Request $request,$slug = null): \Symfony\Component\HttpFoundation\Response
    {
        $postData = json_decode($request->getContent(), true);
        $levelId = $postData["levelId"] ??  0;

        if ($levelId != 0){
            $teacherCurrentRoom = $this->getCurrentRoom($entityManager,$slug);
            $levelToMove = $entityManager->getRepository(Level::class)->find($levelId);
            try {
                //Ne change pas les donnée de la BD actuellement.
                RoomController::changeLevelPosition($entityManager, $teacherCurrentRoom->getId(), $levelToMove, 1);
            } catch (\Exception $e) {
            }
        }
        return new \Symfony\Component\HttpFoundation\Response();
    }
    #[Route('/teacher/{slug}/remove-level', methods: 'POST')]
    public function teacherRemoveLevel(EntityManagerInterface $entityManager, Request $request,$slug = null): \Symfony\Component\HttpFoundation\Response
    {
        $postData = json_decode($request->getContent(), true);
        $levelId = $postData["levelId"] ??  0;

        if ($levelId != 0){
            $teacherCurrentRoom = $this->getCurrentRoom($entityManager,$slug);
            $levelToRemove = $entityManager->getRepository(Level::class)->find($levelId);
            try {
                RoomController::removeLevel($entityManager, $teacherCurrentRoom->getId(), $levelToRemove);
            } catch (\Exception $e) {
            }
        }
        return new \Symfony\Component\HttpFoundation\Response();
    }


    #[Route('/teacher/{slug}/manage-students',  name: 'app_teacher_manange-student')]
    public function teacherRoomManageStudents(EntityManagerInterface $entityManager,$slug = null)
    {
        $teacherCurrentRoom = $this->getCurrentRoom($entityManager,$slug);
        $data = [];
        $teacherRoomStudent = $teacherCurrentRoom->getStudents();
        foreach ($teacherRoomStudent as $i){
            $data[] = ['studentFullName' => $i->getFirstName().' '.$i->getLastName(),'studentUsername' => $i->getUsername(), 'studentCompletion' => UserController::getTotalCompletion($entityManager, $i->getId()),
                'studentPDF' => $i->getConsentForm(), 'studentId' => $i->getId()];
        }

        if ($data == null){
            return $this->render('Teacher/TeacherManageStudents.html.twig', [
                'pageName' => 'Liste des élèves'
                ]);
        }else {
            $columnName = ['column1' => "Nom complet de l'élève",
                'column2' => "Identifiant de l'élève",
                'column3' => 'Completion total',
                'column4' => 'Nom de son pdf',
                'column5' => "Supprimer l'élève"
            ];

            return $this->forward('\App\Controller\ListController::renderBaseList', [
                'pageName' => 'Liste des élèves',
                'data' => $data,
                'columnName' => $columnName,
                'popupAcceptedText' => "Voulez vous vraiment supprimer : ",
                'itemPath' => 'List/Teacher/TeacherStudentsList.html.twig',
                'basePath' => 'Teacher/TeacherManageStudents.html.twig',
                'listClasses' => 'studentsList',
            ]);
        }
    }

    #[Route('/teacher/{slug}/manage-students/remove-student', methods: 'POST')]
    public function teacherRemovestudent(EntityManagerInterface $entityManager, Request $request,$slug = null): \Symfony\Component\HttpFoundation\Response
    {
        $postData = json_decode($request->getContent(), true);
        $studentId = $postData["studentId"] ??  0;

        if ($studentId != 0){
            $teacherCurrentRoom = $this->getCurrentRoom($entityManager,$slug);
            $stuentToRemove = $entityManager->getRepository(User::class)->find($studentId);
            try {
                RoomController::removeStudent($entityManager, $teacherCurrentRoom->getId(), $stuentToRemove);
            } catch (\Exception $e) {
            }
        }
        return new \Symfony\Component\HttpFoundation\Response();
    }

    #[Route('/teacher/{slug}/add-level')]
    public function teacherRoomAddLevelPage(EntityManagerInterface $entityManager,$slug = null)
    {
        $teacherCurrentRoom = $this->getCurrentRoom($entityManager,$slug);
        $teacherCurrentRoomLevel = $teacherCurrentRoom->getLevels();
        $levels = $entityManager->getRepository(Level::class)->findAll();
        $data = [];
        foreach ($levels as $i){
            if($teacherCurrentRoomLevel->contains($i)){
                $data[] = ['levelDisplayName' => $i->getDisplayName(), 'alreadyInLevel' => 'alreadyInLevel', 'levelId' => $i->getId()];
            }
        }
        foreach ($levels as $i){
            if(!$teacherCurrentRoomLevel->contains($i)){
                $data[] = ['levelDisplayName' => $i->getDisplayName(), 'alreadyInLevel' => '', 'levelId' => $i->getId()];
            }
        }

        if ($data == null){
            return $this->render('Teacher/TeacherAddLevel.html.twig', [
                'pageName' => 'Aucun chapitre disponible',
            ]);
        }else {
            $columnName = ['column1' => "Nom du chapitre",
                'column2' => 'Ajouter',
            ];
            return $this->forward('\App\Controller\ListController::renderBaseList', [
                'pageName' => 'Ajouter un chapitre',
                'data' => $data,
                'columnName' => $columnName,
                'itemPath' => 'List/Teacher/TeacherAddLevelList.html.twig',
                'basePath' => 'Teacher/TeacherAddLevel.html.twig',
                'listClasses' => 'levelList',
            ]);
        }
    }
    #[Route('/teacher/{slug}/add-level/add', methods: 'POST')]
    public function teacherRoomAddLevel(EntityManagerInterface $entityManager, Request $request,$slug = null): \Symfony\Component\HttpFoundation\Response
    {
        $postData = json_decode($request->getContent(), true);
        $levelId = $postData["levelId"] ??  0;

        if ($levelId != 0){
                $teacherCurrentRoom = $this->getCurrentRoom($entityManager,$slug);
                $levelToAdd = $entityManager->getRepository(Level::class)->find($levelId);
            try {
                RoomController::addLevel($entityManager, $teacherCurrentRoom->getId(), $levelToAdd);
            } catch (\Exception $e) {
            }
        }
        return new \Symfony\Component\HttpFoundation\Response();
    }
}