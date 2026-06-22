<?php

namespace App\Controller\EntityController;

use App\Entity\Action;
use App\Entity\HistoricCharacter;
use App\Entity\Intervenes;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class IntervenesController extends AbstractController
{
    public static function getIntervenes(EntityManagerInterface $entityManager, Action $action, HistoricCharacter $historicCharacter): Intervenes
    {
        $intervenes = $entityManager->getRepository(Intervenes::class)->findOneBy(array('action' => $action, 'historicCharacter' => $historicCharacter));
        if (!$intervenes) {
            throw new Exception('No relation Intervenes found');
        }

        return $intervenes;
    }

    public static function deleteIntervenes(EntityManagerInterface $entityManager, Action $action, HistoricCharacter $historicCharacter)
    {
        $intervenes = $entityManager->getRepository(Intervenes::class)->findOneBy(array('action' => $action, 'historicCharacter' => $historicCharacter));
        if (!$intervenes) {
            throw new Exception('No relation Intervenes found');
        }

        $entityManager->remove($intervenes);
        $entityManager->flush();
    }
}
