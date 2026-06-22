<?php

namespace App\Controller\EntityController;

use App\Entity\Action;
use App\Entity\Difficulty;
use App\Entity\TakesPlace;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class TakesPlaceController extends AbstractController
{
    public static function getTakesPlace(EntityManagerInterface $entityManager, Action $action, Difficulty $difficulty): TakesPlace
    {
        $takesPlace = $entityManager->getRepository(TakesPlace::class)->findOneBy(array('action' => $action, 'difficulty' => $difficulty));
        if (!$takesPlace) {
            throw new Exception('No relation TakesPlace found');
        }

        return $takesPlace;
    }

    public static function deleteTakesPlace(EntityManagerInterface $entityManager, Action $action, Difficulty $difficulty)
    {
        $takesPlace = $entityManager->getRepository(TakesPlace::class)->findOneBy(array('action' => $action, 'difficulty' => $difficulty));
        if (!$takesPlace) {
            throw new Exception('No relation TakesPlace found');
        }

        $entityManager->remove($takesPlace);
        $entityManager->flush();
    }
}
