<?php

namespace App\Controller\EntityController;

use App\Entity\Action;
use App\Entity\Difficulty;
use App\Entity\HistoricCharacter;
use App\Entity\Intervenes;
use App\Entity\Scene;
use App\Entity\TakesPlace;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ActionController extends AbstractController
{
    public static function getAction(EntityManagerInterface $entityManager, int $id): Action
    {
        $action = $entityManager->getRepository(Action::class)->find($id);
        if (!$action) {
            throw new Exception('No action found for id '.$id);
        }

        return $action;
    }

    public static function newAction(EntityManagerInterface $entityManager, string $context, Scene $scene): int
    {
        $action = new Action();

        $action->setScene($scene);
        $action->setContext($context);
        $scene->addAction($action);

        $entityManager->persist($action);
        $entityManager->persist($scene);
        $entityManager->flush();

        return $action->getId();
    }

    public static function addDifficulty(EntityManagerInterface $entityManager, int $id, Difficulty $difficulty, int $positionId)
    {
        $action = $entityManager->getRepository(Action::class)->find($id);
        if (!$action) {
            throw new Exception('No action found for id '.$id);
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

    public static function addHistoricCharacter(EntityManagerInterface $entityManager, int $id, HistoricCharacter $historicCharacter, string $positionId, string $context)
    {
        $action = $entityManager->getRepository(Action::class)->find($id);
        if (!$action) {
            throw new Exception('No action found for id '.$id);
        }

        $intervenes = new Intervenes();
        
        $intervenes->setPositionId($positionId);
        $intervenes->setContext($context);
        $intervenes->setHistoricCharacter($historicCharacter);
        $intervenes->setAction($action);
        $action->addIntervene($intervenes);
        $historicCharacter->addIntervene($intervenes);

        $entityManager->persist($intervenes);
        $entityManager->persist($action);
        $entityManager->persist($historicCharacter);
        $entityManager->flush();
    }

    public static function deleteAction(EntityManagerInterface $entityManager, int $id)
    {
        $action = $entityManager->getRepository(Action::class)->find($id);
        if (!$action) {
            throw new Exception('No action found for id '.$id);
        }

        $entityManager->remove($action);
        $entityManager->flush();
    }
}
