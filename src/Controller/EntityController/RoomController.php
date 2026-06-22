<?php

namespace App\Controller\EntityController;

use App\Entity\Level;
use App\Entity\Room;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class RoomController extends AbstractController
{
    public static function getRoom(EntityManagerInterface $entityManager, int $id): Room
    {
        $room = $entityManager->getRepository(Room::class)->find($id);
        if (!$room) {
            throw new Exception('No room found for id '.$id);
        }

        return $room;
    }

    public static function newRoom(EntityManagerInterface $entityManager, string $roomName, string $description, User $teacher): int
    {
        $room = new Room();

        $room->setRoomName($roomName);
        $room->setRoomDescription($description);
        $room->setTeacher($teacher);
        $teacher->addRoom($room);

        $entityManager->persist($room);
        $entityManager->persist($teacher);
        $entityManager->flush();

        return $room->getId();
    }

    public static function updateRoom(EntityManagerInterface $entityManager, int $id, string $roomName)
    {
        $room = $entityManager->getRepository(Room::class)->find($id);
        if (!$room) {
            throw new Exception('No room found for id '.$id);
        }

        $room->setRoomName($roomName);

        $entityManager->persist($room);
        $entityManager->flush();
    }

    public static function changeLevelPosition(EntityManagerInterface $entityManager, int $roomId, Level $level, int $direction)
    {
        $room = $entityManager->getRepository(Room::class)->find($roomId);
        if (!$room) {
            throw new Exception('No room found for id '.$roomId);
        }
        $levelIndex = $room->getLevels()->indexOf($level);

        if ($levelIndex+$direction >= 0 && $levelIndex+$direction < $room->getLevels()->count()) {
            $temp = $room->getLevels()->get($levelIndex);
            $room->getLevels()->set($levelIndex, $room->getLevels()->get($levelIndex + $direction));
            $room->getLevels()->set($levelIndex + $direction, $temp);
        }

        $entityManager->flush();
    }

    public static function addLevel(EntityManagerInterface $entityManager, int $id, Level $level)
    {
        $room = $entityManager->getRepository(Room::class)->find($id);
        if (!$room) {
            throw new Exception('No room found for id '.$id);
        }

        $room->addLevel($level);
        $level->addRoom($room);
        foreach ($room->getStudents() as $student) {
            UserController::addLevelPlayed($entityManager, $student->getId(), $level);
        }

        $entityManager->persist($room);
        $entityManager->persist($level);
        $entityManager->flush();
    }

    public static function removeLevel(EntityManagerInterface $entityManager, int $roomId, Level $level)
    {
        $room = $entityManager->getRepository(Room::class)->find($roomId);
        if (!$room) {
            throw new Exception('No room found for id '.$roomId);
        }

        foreach ($room->getStudents() as $student) {
            UserController::removeLevelPlayed($entityManager, $student->getId(), $level);
        }
        $room->removeLevel($level);

        $entityManager->flush();
    }

    public static function removeStudent(EntityManagerInterface $entityManager, int $roomId, User $student)
    {
        $room = $entityManager->getRepository(Room::class)->find($roomId);
        if (!$room) {
            throw new Exception('No room found for id '.$roomId);
        }

        $room->removeStudent($student);

        $entityManager->flush();
    }

    public static function deleteRoom(EntityManagerInterface $entityManager, int $id)
    {
        $room = $entityManager->getRepository(Room::class)->find($id);
        if (!$room) {
            throw new Exception('No room found for id '.$id);
        }
        $students = $room->getStudents();
        foreach ($students as $i){
            $room->removeStudent($i);
            UserController::deleteUser($entityManager, $i->getId());
        }
        $entityManager->remove($room);
        $entityManager->flush();
    }
}
