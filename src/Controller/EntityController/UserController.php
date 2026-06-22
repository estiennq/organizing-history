<?php

namespace App\Controller\EntityController;

use App\Entity\Action;
use App\Entity\Difficulty;
use App\Entity\HistoricCharacter;
use App\Entity\Intervenes;
use App\Entity\Level;
use App\Entity\Plays;
use App\Entity\Room;
use App\Entity\User;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserController extends AbstractController
{
    public static function newTeacher(EntityManagerInterface $entityManager,UserPasswordHasherInterface $userPasswordHasher, string $lastName, string $firstName, string $password, string $schoolName, string $email): int
    {
        $teacher = new User();
        $teacher->setLastName($lastName);
        $teacher->setFirstName($firstName);
        $teacher->setUsername(strtolower(substr($lastName, 0, 7).substr($firstName, 0, 1)));
        $teacher->setRoles(['ROLE_PENDING_TEACHER']);
        $teacher->setPassword($userPasswordHasher->hashPassword($teacher,$password));
        $teacher->setSchoolName($schoolName);
        $teacher->setEmail($email);
        $teacher->setCreationDate(new DateTime());

        $entityManager->persist($teacher);
        $entityManager->flush();

        return $teacher->getId();
    }

    public static function newStudent(EntityManagerInterface $entityManager, UserPasswordHasherInterface $userPasswordHasher, string $lastName, string $firstName, string $password, Room $room, string $consentForm, string $email): int
    {
        $student = new User();
        $student->setLastName($lastName);
        $student->setFirstName($firstName);
        $student->setUsername(strtolower(substr($lastName, 0, 7).substr($firstName, 0, 1)));
        $student->setRoles(['ROLE_PENDING_STUDENT']);
        $student->setPassword($userPasswordHasher->hashPassword($student, $password));
        $student->setRoom($room);
        $room->addStudent($student);
        $student->setConsentForm($consentForm);
        $student->setEmail($email);
        $student->setCreationDate(new DateTime());

        $entityManager->persist($student);
        $entityManager->persist($room);
        $entityManager->flush();

        foreach ($room->getLevels() as $level) {
            UserController::addLevelPlayed($entityManager, $student->getId(), $level);
        }

        return $student->getId();
    }

    public static function acceptUser(EntityManagerInterface $entityManager, int $id, string $status)
    {
        $user = $entityManager->getRepository(User::class)->find($id);
        if (!$user) {
            throw new Exception('No user found for id '.$id);
        }
        $user->setStatus($status);
        if ($user->getRoles()[0] == 'ROLE_PENDING_STUDENT'){
            $user->setRoles(['ROLE_STUDENT']);
        }else{
            $user->setRoles(['ROLE_TEACHER']);
        }


        $entityManager->persist($user);
        $entityManager->flush();
    }

    public static function changePassword(EntityManagerInterface $entityManager, UserPasswordHasherInterface $userPasswordHasher, int $id, string $password)
    {
        $user = $entityManager->getRepository(User::class)->find($id);
        if (!$user) {
            throw new Exception('No user found for id '.$id);
        }

        $user->setPassword($userPasswordHasher->hashPassword($user, $password));

        $entityManager->persist($user);
        $entityManager->flush();
    }

    public static function getErrorCount(EntityManagerInterface $entityManager, int $id, Level $level): int
    {
        $student = $entityManager->getRepository(User::class)->find($id);
        if (!$student) {
            throw new Exception('No student found for id '.$id);
        }

        $errorCount = 0;
        foreach ($student->getLevelsPlayed() as $difficulty) {
            if ($difficulty->getDifficulty()->getLevel() === $level) {
                $errorCount += $difficulty->getErrorCount();
            }
        }

        return $errorCount;
    }

    public static function getCompletesCount(EntityManagerInterface $entityManager, int $id, Level $level): int
    {
        $student = $entityManager->getRepository(User::class)->find($id);
        if (!$student) {
            throw new Exception('No student found for id '.$id);
        }

        $completesCount = 0;
        foreach ($student->getLevelsPlayed() as $difficulty) {
            if ($difficulty->getDifficulty()->getLevel() === $level && $difficulty->isCompleted()) {
                $completesCount += 1/$level->getDifficulties()->count();
            }
        }

        return $completesCount;
    }

    public static function getTotalCompletion(EntityManagerInterface $entityManager, int $id): int
    {
        $student = $entityManager->getRepository(User::class)->find($id);
        if (!$student) {
            throw new Exception('No student found for id '.$id);
        }

        $completesCount = 0;
        foreach ($student->getRoom()->getLevels() as $level) {
            foreach ($level->getDifficulties() as $difficulty) {
                if ($entityManager->getRepository(Plays::class)->findOneBy(array('student' => $student, 'difficulty' => $difficulty))->isCompleted()) {
                    $completesCount += 100 / $student->getRoom()->getLevels()->count() / $level->getDifficulties()->count();
                }
            }
        }

        return $completesCount;
    }

    public static function addLevelPlayed(EntityManagerInterface $entityManager, int $id, Level $level)
    {
        $student = $entityManager->getRepository(User::class)->find($id);
        if (!$student) {
            throw new Exception('No student found for id '.$id);
        }

        foreach ($level->getDifficulties() as $difficulty) {
            $played = new Plays();
            $played->setStudent($student);
            $played->setDifficulty($difficulty);
            $student->addLevelsPlayed($played);
            $difficulty->addPlayedBy($played);

            $entityManager->persist($difficulty);
            $entityManager->persist($played);
        }

        $entityManager->persist($student);
        $entityManager->flush();
    }

    public static function removeLevelPlayed(EntityManagerInterface $entityManager, int $id, Level $level)
    {
        $student = $entityManager->getRepository(User::class)->find($id);
        if (!$student) {
            throw new Exception('No student found for id '.$id);
        }

        foreach ($level->getDifficulties() as $difficulty) {
            $played = PlaysController::getPlays($entityManager, $student, $difficulty);
            $student->removeLevelsPlayed($played);
            $difficulty->removePlayedBy($played);

            $entityManager->persist($difficulty);
            $entityManager->persist($played);
        }

        $entityManager->persist($student);
        $entityManager->flush();
    }

    public static function deleteUser(EntityManagerInterface $entityManager, int $id)
    {
        $user = $entityManager->getRepository(User::class)->find($id);
        if (!$user) {
            throw new Exception('No user found for id '.$id);
        }

        $entityManager->remove($user);
        $entityManager->flush();
    }
}
