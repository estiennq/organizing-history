<?php

namespace App\Controller;
use App\Controller\EntityController\UserController;
use App\Entity\Room;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Flex\Response;

class AdminPageController extends AbstractController
{

    #[Route('/admin')]
    public function adminHomepage(EntityManagerInterface $entityManager)
    {
        return $this->render('Admin/AdminHomepage.html.twig', [
            'pageName' => 'Admin',
        ]);
    }

    #[Route('/admin/students')]
    public function adminstudentsPage(EntityManagerInterface $entityManager)
    {

        $student = $entityManager->getRepository(User::class)->findBy(array('status'=>'pending'));
        $data = [];
        foreach ($student as $i){
            if ("ROLE_PENDING_STUDENT" == $i->getRoles()[0]) {
                $room = $i->getRoom();
                $teacher = $room->getTeacher();
                $data[] = ['studentFirstName' => $i->getFirstName(), 'studentsLastName' => $i->getLastName(), 'studentId' => $i->getId(), 'studentRoomName' => $room->getRoomName(), 'studentRoomTeacherName' => $teacher->getFirstName() . " " . $teacher->getLastName()];
            }
        }

        if($data == null){
            return $this->render('Admin/AdminStudent.html.twig', [
                'pageName' => 'Élèves',
            ]);
        }else {
            $columnName = ['column1' => "Nom complet de l'élève",
                'column2' => 'Class accociée',
                'column3' => 'Professeur associée',
                'column4' => 'Formulaire associée',
            ];
            return $this->forward('\App\Controller\ListController::renderBaseList', [
                'pageName' => 'Élèves',
                'data' => $data,
                'columnName' => $columnName,
                'itemPath' => 'List/Admin/AdminStudentList.html.twig',
                'basePath' => 'Admin/AdminStudent.html.twig',
                'listClasses' => 'adminListStudent',
            ]);
        }
    }

    #[Route('/admin/students/verify', methods: 'POST')]
    public function adminstudentVerifyPage(EntityManagerInterface $entityManager,Request $request)
    {
        $studentId = $request->get('studentId') ??  0;
        $student = $entityManager->getRepository(User::class)->find($studentId);

        return $this->render('Admin/AdminStudentValide.html.twig', [
            'pageName' => $student->getFirstName() . " " . $student->getLastName(),
            'studentId' => $studentId,
            'studentConsentForm' => 'ConsentForm/'.$student->getConsentForm(),
            'popupAcceptedText' => 'Êtes-vous sûr de vouloir valider la création du compte ?',
            'popupDeniedText' => 'Êtes-vous sûr de vouloir refuser la création du compte ?',
        ]);
    }

    #[Route('/admin/user-accepted', methods: 'POST')]
    function userAccepted(EntityManagerInterface $entityManager, Request $request)
    {
        $postData = json_decode($request->getContent(), true);
        $idstudent = $postData["idUser"] ??  0;

        if ($idstudent != 0){
            try {
                UserController::acceptUser($entityManager, $idstudent, 'accepted');
            } catch (\Exception $e) {
                return new \Symfony\Component\HttpFoundation\Response( $e );
            }
        }
        return new \Symfony\Component\HttpFoundation\Response();
    }
    #[Route('/admin/user-denied', methods: 'POST')]
    function userDenied(EntityManagerInterface $entityManager, Request $request)
    {
        $postData = json_decode($request->getContent(), true);

        $idstudent = $postData["idUser"] ??  0;

        if ($idstudent != 0){
            try {
                UserController::deleteUser($entityManager, $idstudent);
            } catch (\Exception $e) {
                return new \Symfony\Component\HttpFoundation\Response( $e );
            }
        }
        return new \Symfony\Component\HttpFoundation\Response();
    }

    #[Route('/admin/teachers')]
    public function adminTeacherPage(EntityManagerInterface $entityManager): \Symfony\Component\HttpFoundation\Response
    {
        $teacher = $entityManager->getRepository(User::class)->findBy(array('status'=>'pending'));
        $data = [];
        foreach ($teacher as $i){
            if ("ROLE_PENDING_TEACHER" == $i->getRoles()[0]){
            $data[] = ['teacherFirstName' => $i->getFirstName(), 'teacherLastName' => $i->getLastName(), 'teacherSchool' => $i->getSchoolName(), 'teacherId' => $i->getId()];
            }
        }
        if ($data == null){
            return $this->render('Admin/AdminTeacher.html.twig', [
                'pageName' => 'Professeurs',
            ]);
        }else {
            $columnName = ['column1' => "Nom complet du professeur",
                'column2' => 'Ecole',
                'column3' => 'Valider',
                'column4' => 'Refuser',
                ];
            return $this->forward('\App\Controller\ListController::renderBaseList', [
                'pageName' => 'Professeurs',
                'data' => $data,
                'columnName' => $columnName,
                'itemPath' => 'List/Admin/AdminTeacherList.html.twig',
                'basePath' => 'Admin/AdminTeacher.html.twig',
                'listClasses' => 'adminListTeacher',
                'popupAcceptedText' => 'Êtes-vous sûr de vouloir valider ce professeur ?',
                'popupDeniedText' => 'Êtes-vous sûr de vouloir refuser ce professeur ?',
            ]);
        }
    }
}
