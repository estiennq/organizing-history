<?php

namespace App\Controller;

use App\Controller\EntityController\RoomController;
use App\Controller\EntityController\UserController;
use App\Entity\Level;
use App\Entity\Room;
use App\Entity\User;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class DemoController extends AbstractController
{
    #[Route('/demo', name: 'demo')]
    public function demoPage(RequestStack $requestStack): Response
    {
        if ($requestStack->getSession()->get('demo_student_id')) {
            return $this->redirectToRoute('demo_credentials');
        }
        return $this->render('Demo/DemoPage.html.twig');
    }

    #[Route('/demo/start', name: 'demo_start')]
    public function startDemo(
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $hasher,
        RequestStack $requestStack
    ): Response {
        $session = $requestStack->getSession();

        if ($session->get('demo_student_id')) {
            return $this->redirectToRoute('demo_credentials');
        }

        $this->cleanOldDemoAccounts($entityManager);

        $suffix = uniqid();
        $password = bin2hex(random_bytes(4));
        $teacherUsername = 'demo_p_' . $suffix;
        $studentUsername = 'demo_e_' . $suffix;

        // Création du professeur
        $teacher = new User();
        $teacher->setLastName('Démo');
        $teacher->setFirstName('Professeur');
        $teacher->setUsername($teacherUsername);
        $teacher->setRoles(['ROLE_TEACHER']);
        $teacher->setStatus('accepted');
        $teacher->setPassword($hasher->hashPassword($teacher, $password));
        $teacher->setSchoolName('Démonstration');
        $teacher->setEmail('demo@demo.com');
        $teacher->setCreationDate(new DateTime());
        $entityManager->persist($teacher);
        $entityManager->flush();

        // Création de la room avec les niveaux
        $roomId = RoomController::newRoom($entityManager, 'Salle démo', 'demo', $teacher);
        $level1 = $entityManager->getRepository(Level::class)->findOneBy(['levelName' => 'ww1']);
        $level2 = $entityManager->getRepository(Level::class)->findOneBy(['levelName' => 'ww2']);
        if ($level1) RoomController::addLevel($entityManager, $roomId, $level1);
        if ($level2) RoomController::addLevel($entityManager, $roomId, $level2);

        // Création de l'élève
        $room = $entityManager->getRepository(Room::class)->find($roomId);
        $student = new User();
        $student->setLastName('Démo');
        $student->setFirstName('Élève');
        $student->setUsername($studentUsername);
        $student->setRoles(['ROLE_STUDENT']);
        $student->setStatus('accepted');
        $student->setPassword($hasher->hashPassword($student, $password));
        $student->setRoom($room);
        $student->setConsentForm('demo.pdf');
        $student->setEmail('demo@demo.com');
        $student->setCreationDate(new DateTime());
        $room->addStudent($student);
        $entityManager->persist($student);
        $entityManager->persist($room);
        $entityManager->flush();

        if ($level1) UserController::addLevelPlayed($entityManager, $student->getId(), $level1);
        if ($level2) UserController::addLevelPlayed($entityManager, $student->getId(), $level2);

        $session->set('demo_student_id', $student->getId());
        $session->set('demo_teacher_id', $teacher->getId());
        $session->set('demo_room_id', $roomId);
        $session->set('demo_password', $password);
        $session->set('demo_student_username', $studentUsername);
        $session->set('demo_teacher_username', $teacherUsername);

        return $this->redirectToRoute('demo_credentials');
    }

    #[Route('/demo/credentials', name: 'demo_credentials')]
    public function demoCredentials(RequestStack $requestStack): Response
    {
        $session = $requestStack->getSession();
        if (!$session->get('demo_student_id')) {
            return $this->redirectToRoute('demo');
        }
        return $this->render('Demo/DemoCredentials.html.twig', [
            'studentUsername' => $session->get('demo_student_username'),
            'teacherUsername' => $session->get('demo_teacher_username'),
            'password' => $session->get('demo_password'),
        ]);
    }

    #[Route('/demo/stop', name: 'demo_stop')]
    public function stopDemo(EntityManagerInterface $entityManager, RequestStack $requestStack): Response
    {
        $session = $requestStack->getSession();
        $roomId = $session->get('demo_room_id');
        $teacherId = $session->get('demo_teacher_id');

        if ($roomId) {
            try { RoomController::deleteRoom($entityManager, $roomId); } catch (\Exception $e) {}
        }
        if ($teacherId) {
            try { UserController::deleteUser($entityManager, $teacherId); } catch (\Exception $e) {}
        }

        foreach (['demo_student_id', 'demo_teacher_id', 'demo_room_id', 'demo_password', 'demo_student_username', 'demo_teacher_username'] as $key) {
            $session->remove($key);
        }

        return $this->redirectToRoute('demo');
    }

    private function cleanOldDemoAccounts(EntityManagerInterface $entityManager): void
    {
        $threshold = new DateTime('-24 hours');
        $oldTeachers = $entityManager->getRepository(User::class)
            ->createQueryBuilder('u')
            ->where('u.username LIKE :prefix')
            ->andWhere('u.creationDate < :threshold')
            ->setParameter('prefix', 'demo\_p\_%', 'string')
            ->setParameter('threshold', $threshold)
            ->getQuery()
            ->getResult();

        foreach ($oldTeachers as $teacher) {
            foreach ($teacher->getRooms() as $room) {
                try { RoomController::deleteRoom($entityManager, $room->getId()); } catch (\Exception $e) {}
            }
            try { UserController::deleteUser($entityManager, $teacher->getId()); } catch (\Exception $e) {}
        }
    }
}
