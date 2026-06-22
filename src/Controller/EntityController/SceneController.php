<?php

namespace App\Controller\EntityController;

use App\Entity\Scene;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SceneController extends AbstractController
{
    public static function getScene(EntityManagerInterface $entityManager, int $id): Scene
    {
        $scene = $entityManager->getRepository(Scene::class)->find($id);
        if (!$scene) {
            throw new Exception('No scene found for id '.$id);
        }

        return $scene;
    }

    public static function newScene(EntityManagerInterface $entityManager, string $name, string $sprite): int
    {
        $scene = new Scene();

        $scene->setSprite($sprite);
        $scene->setName($name);

        $entityManager->persist($scene);
        $entityManager->flush();

        return $scene->getId();
    }

    public static function updateScene(EntityManagerInterface $entityManager, int $id, string $sprite)
    {
        $scene = $entityManager->getRepository(Scene::class)->find($id);
        if (!$scene) {
            throw new Exception('No scene found for id '.$id);
        }

        $scene->setSprite($sprite);

        $entityManager->persist($scene);
        $entityManager->flush();
    }

    public static function deleteScene(EntityManagerInterface $entityManager, int $id)
    {
        $scene = $entityManager->getRepository(Scene::class)->find($id);
        if (!$scene) {
            throw new Exception('No scene found for id '.$id);
        }

        $entityManager->remove($scene);
        $entityManager->flush();
    }
}
