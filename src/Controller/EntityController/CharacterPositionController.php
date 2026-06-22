<?php

namespace App\Controller\EntityController;

use App\Entity\CharacterPosition;
use App\Entity\Scene;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CharacterPositionController extends AbstractController
{
    public static function getCharacterPosition(EntityManagerInterface $entityManager, int $id): CharacterPosition
    {
        $characterPosition = $entityManager->getRepository(CharacterPosition::class)->find($id);
        if (!$characterPosition) {
            throw new Exception('No character position found for id '.$id);
        }

        return $characterPosition;
    }

    public static function newCharacterPosition(EntityManagerInterface $entityManager, string $name, float $positionX, float $positionY, float $offsetX, float $offsetY,  Scene $scene): int
    {
        $characterPosition = new CharacterPosition();

        $characterPosition->setName($name);
        $characterPosition->setPositionX($positionX);
        $characterPosition->setPositionY($positionY);
        $characterPosition->setOffsetX($offsetX);
        $characterPosition->setOffsetY($offsetY);
        $characterPosition->setScene($scene);
        $scene->addCharacterPosition($characterPosition);

        $entityManager->persist($characterPosition);
        $entityManager->persist($scene);
        $entityManager->flush();

        return $characterPosition->getId();
    }

    public static function updateCharacterPosition(EntityManagerInterface $entityManager, int $id, float $positionX, float $positionY)
    {
        $characterPosition = $entityManager->getRepository(CharacterPosition::class)->find($id);
        if (!$characterPosition) {
            throw new Exception('No character position found for id '.$id);
        }

        $characterPosition->setPositionX($positionX);
        $characterPosition->setPositionY($positionY);

        $entityManager->persist($characterPosition);
        $entityManager->flush();
    }

    public static function deleteCharacterPosition(EntityManagerInterface $entityManager, int $id)
    {
        $characterPosition = $entityManager->getRepository(CharacterPosition::class)->find($id);
        if (!$characterPosition) {
            throw new Exception('No character position found for id '.$id);
        }

        $entityManager->remove($characterPosition);
        $entityManager->flush();
    }
}
