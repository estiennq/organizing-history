<?php

namespace App\Controller\EntityController;

use App\Entity\Action;
use App\Entity\HistoricCharacter;
use App\Entity\Intervenes;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HistoricCharacterController extends AbstractController
{
    public static function getHistoricCharacter(EntityManagerInterface $entityManager, int $id): HistoricCharacter
    {
        $historicCharacter = $entityManager->getRepository(HistoricCharacter::class)->find($id);
        if (!$historicCharacter) {
            throw new Exception('No historic character found for id '.$id);
        }

        return $historicCharacter;
    }

    public static function newHistoricCharacter(EntityManagerInterface $entityManager, string $name, string $sprite): int
    {
        $historicCharacter = new HistoricCharacter();

        $historicCharacter->setSprite($sprite);
        $historicCharacter->setName($name);

        $entityManager->persist($historicCharacter);
        $entityManager->flush();

        return $historicCharacter->getId();
    }

    public static function updateHistoricCharacter(EntityManagerInterface $entityManager, int $id, string $sprite)
    {
        $historicCharacter = $entityManager->getRepository(HistoricCharacter::class)->find($id);
        if (!$historicCharacter) {
            throw new Exception('No historic character found for id '.$id);
        }

        $historicCharacter->setSprite($sprite);

        $entityManager->persist($historicCharacter);
        $entityManager->flush();
    }

    public static function addAction(EntityManagerInterface $entityManager, int $id, Action $action, int $positionId, string $context)
    {
        $historicCharacter = $entityManager->getRepository(HistoricCharacter::class)->find($id);
        if (!$historicCharacter) {
            throw new Exception('No historic character found for id '.$id);
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

    public static function deleteHistoricCharacter(EntityManagerInterface $entityManager, int $id)
    {
        $historicCharacter = $entityManager->getRepository(HistoricCharacter::class)->find($id);
        if (!$historicCharacter) {
            throw new Exception('No historic character found for id '.$id);
        }

        $entityManager->remove($historicCharacter);
        $entityManager->flush();
    }
}
