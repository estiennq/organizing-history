<?php

namespace App\Controller\EntityController;

use App\Entity\Action;
use App\Entity\Difficulty;
use App\Entity\Level;
use App\Entity\TakesPlace;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DifficultyController extends AbstractController
{
    public static function getDifficulty(EntityManagerInterface $entityManager, int $id): Difficulty
    {
        $difficulty = $entityManager->getRepository(Difficulty::class)->find($id);
        if (!$difficulty) {
            throw new Exception('No difficulty found for id '.$id);
        }

        return $difficulty;
    }

    public static function newDifficulty(EntityManagerInterface $entityManager, string $difficultyName, string $displayName, Level $level): int
    {
        $difficulty = new Difficulty();

        $difficulty->setDifficultyName($difficultyName);
        $difficulty->setDisplayName($displayName);
        $difficulty->setLevel($level);
        $level->addDifficulty($difficulty);

        $entityManager->persist($difficulty);
        $entityManager->persist($level);
        $entityManager->flush();

        return $difficulty->getId();
    }

    public static function updateDifficulty(EntityManagerInterface $entityManager, int $id, string $difficultyName)
    {
        $difficulty = $entityManager->getRepository(Difficulty::class)->find($id);
        if (!$difficulty) {
            throw new Exception('No difficulty found for id '.$id);
        }

        $difficulty->setDifficultyName($difficultyName);

        $entityManager->persist($difficulty);
        $entityManager->flush();
    }

    public static function addAction(EntityManagerInterface $entityManager, int $id, Action $action, string $positionId)
    {
        $difficulty = $entityManager->getRepository(Difficulty::class)->find($id);
        if (!$difficulty) {
            throw new Exception('No difficulty found for id '.$id);
        }

        $takesPlace = new TakesPlace();
        
        $takesPlace->setPositionId($positionId);
        $takesPlace->setDifficulty($difficulty);
        $takesPlace->setAction($action);
        $action->addTakesPlace($takesPlace);
        $difficulty->addTakesPlace($takesPlace);

        $entityManager->persist($takesPlace);
        $entityManager->persist($action);
        $entityManager->persist($difficulty);
        $entityManager->flush();
    }

    public static function deleteDifficulty(EntityManagerInterface $entityManager, int $id)
    {
        $difficulty = $entityManager->getRepository(Difficulty::class)->find($id);
        if (!$difficulty) {
            throw new Exception('No difficulty found for id '.$id);
        }

        $entityManager->remove($difficulty);
        $entityManager->flush();
    }
}
