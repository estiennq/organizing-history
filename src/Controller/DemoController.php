<?php

namespace App\Controller;

use App\Entity\Difficulty;
use App\Entity\Level;
use App\Entity\Scene;
use App\Entity\TakesPlace;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DemoController extends AbstractController
{
    #[Route('/demo', name: 'demo')]
    public function demoPage(EntityManagerInterface $entityManager): Response
    {
        $levels = $entityManager->getRepository(Level::class)->findAll();

        return $this->render('Demo/DemoPage.html.twig', [
            'levels' => $levels,
        ]);
    }

    #[Route('/demo/{levelName}/{difficultyName}', name: 'demo_level')]
    public function loadDemoLevel(EntityManagerInterface $entityManager, string $levelName, string $difficultyName): Response
    {
        $level = $entityManager->getRepository(Level::class)->findOneBy(['levelName' => $levelName]);
        $difficulty = $entityManager->getRepository(Difficulty::class)->findOneBy(['level' => $level, 'difficultyName' => $difficultyName]);
        $boxAmount = $difficulty->getTakesPlaces()->count();
        $pageAmount = ceil($boxAmount / 6);
        $actions = $difficulty->getTakesPlaces();
        $scenes = [];
        $intervenes = [];
        $characters = [];
        foreach ($actions as $action) {
            $scenes[] = $action->getAction()->getScene();
            $intervenes[] = $action->getAction()->getIntervenes();
        }
        foreach ($intervenes as $intervene) {
            foreach ($intervene as $character) {
                $characters[] = $character->getHistoricCharacter();
            }
        }

        return $this->render('Game/GamePage.html.twig', [
            'title' => 'Démo',
            'pageName' => $difficulty->getDisplayName(),
            'firstName' => '',
            'lastName' => '',
            'boxAmount' => $boxAmount,
            'boxPercentWidth' => 25,
            'role' => '',
            'pageAmount' => $pageAmount,
            'scenes' => array_unique($scenes, SORT_REGULAR),
            'characters' => array_unique($characters, SORT_REGULAR),
            'difficultyId' => $difficulty->getId(),
            'demoMode' => true,
        ]);
    }

    #[Route('/demo/{levelName}/{difficultyName}/solution', methods: 'POST')]
    public function checkSolution(string $levelName, string $difficultyName): Response
    {
        return $this->forward(GamePageController::class . '::checkLevelSolution', [
            'levelName' => $levelName,
            'difficultyName' => $difficultyName,
        ]);
    }

    #[Route('/demo/{levelName}/{difficultyName}/solution/manage-error', methods: 'POST')]
    public function manageError(): Response
    {
        return new Response();
    }

    #[Route('/demo/{levelName}/{difficultyName}/has-next')]
    public function hasNextDifficulty(string $levelName, string $difficultyName): Response
    {
        return $this->forward(GamePageController::class . '::hasNextDifficulty', [
            'levelName' => $levelName,
            'difficultyName' => $difficultyName,
        ]);
    }

    #[Route('/demo/{levelName}/{difficultyName}/next')]
    public function loadNextDifficulty(EntityManagerInterface $entityManager, string $levelName, string $difficultyName): Response
    {
        $level = $entityManager->getRepository(Level::class)->findOneBy(['levelName' => $levelName]);
        $previousDifficulty = $entityManager->getRepository(Difficulty::class)->findOneBy(['level' => $level, 'difficultyName' => $difficultyName]);
        $previousDifficultyId = array_search($previousDifficulty, $level->getDifficulties()->toArray());

        if (count($level->getDifficulties()) > $previousDifficultyId + 1) {
            return $this->redirectToRoute('demo_level', [
                'levelName' => $levelName,
                'difficultyName' => $level->getDifficulties()[$previousDifficultyId + 1]->getDifficultyName(),
            ]);
        }

        return $this->redirectToRoute('demo');
    }
}
