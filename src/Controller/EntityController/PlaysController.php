<?php

namespace App\Controller\EntityController;

use App\Entity\Action;
use App\Entity\Difficulty;
use App\Entity\HistoricCharacter;
use App\Entity\Intervenes;
use App\Entity\Level;
use App\Entity\Plays;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PlaysController extends AbstractController
{
    public static function getPlays(EntityManagerInterface $entityManager, User $student, Difficulty $difficulty): Plays
    {
        $plays = $entityManager->getRepository(Plays::class)->findOneBy(array('student' => $student, 'difficulty' => $difficulty));
        if (!$plays) {
            throw new Exception('No relation Plays found');
        }

        return $plays;
    }

    public static function deletePlays(EntityManagerInterface $entityManager, User $student, Difficulty $difficulty)
    {
        $plays = $entityManager->getRepository(Plays::class)->findOneBy(array('student' => $student, 'difficulty' => $difficulty));
        if (!$plays) {
            throw new Exception('No relation Plays found');
        }

        $entityManager->remove($plays);
        $entityManager->flush();
    }

    public static function getFirstUncompletedDifficulty(EntityManagerInterface $entityManager, User $student, Level $level)
    {
        $difficulties = $level->getDifficulties();
        $firstUncompletedDifficulty = null;

        foreach ($difficulties as $difficulty){
            $plays = PlaysController::getPlays($entityManager, $student, $difficulty);

            if(!$plays->isCompleted()){
                $firstUncompletedDifficulty = $difficulty;
                break;
            }
        }
        return $firstUncompletedDifficulty;
    }
}
