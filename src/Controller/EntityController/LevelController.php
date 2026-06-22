<?php

namespace App\Controller\EntityController;

use App\Entity\Level;
use App\Entity\Room;
use App\Entity\User;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class LevelController extends AbstractController
{
    public static function getLevel(EntityManagerInterface $entityManager, int $id): Level
    {
        $level = $entityManager->getRepository(Level::class)->find($id);
        if (!$level) {
            throw new Exception('No level found for id '.$id);
        }

        return $level;
    }

    public static function newLevel(EntityManagerInterface $entityManager, string $levelName, string $displayName): int
    {
        $level = new Level();

        $level->setLevelName($levelName);
        $level->setDisplayName($displayName);

        $entityManager->persist($level);
        $entityManager->flush();

        return $level->getId();
    }

    public static function updateLevel(EntityManagerInterface $entityManager, int $id, string $levelName)
    {
        $level = $entityManager->getRepository(Level::class)->find($id);
        if (!$level) {
            throw new Exception('No level found for id '.$id);
        }

        $level->setLevelName($levelName);

        $entityManager->persist($level);
        $entityManager->flush();
    }

    public static function addRoom(EntityManagerInterface $entityManager, int $id, Room $room)
    {
        $level = $entityManager->getRepository(Level::class)->find($id);
        if (!$level) {
            throw new Exception('No level found for id '.$id);
        }

        $level->addRoom($room);
        $room->addLevel($level);

        $entityManager->persist($level);
        $entityManager->persist($room);
        $entityManager->flush();
    }

    public static function deleteLevel(EntityManagerInterface $entityManager, int $id)
    {
        $level = $entityManager->getRepository(Level::class)->find($id);
        if (!$level) {
            throw new Exception('No level found for id '.$id);
        }

        $entityManager->remove($level);
        $entityManager->flush();
    }
}
