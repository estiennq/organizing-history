<?php

namespace App\Controller;

use App\Controller\EntityController\ActionController;
use App\Controller\EntityController\PlaysController;
use App\Controller\EntityController\RoomController;
use App\Controller\EntityController\UserController;
use App\Entity\Action;
use App\Entity\Difficulty;
use App\Entity\Intervenes;
use App\Entity\Level;
use App\Entity\Scene;
use App\Entity\TakesPlace;
use App\Entity\User;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Driver\AttributeReader;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AttributeLoader;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class GamePageController extends AbstractController
{

    #[Route('/level/{levelName}/{difficultyName}', name:'level')]
    public function loadDifficulty(EntityManagerInterface $entityManager, string $levelName, string $difficultyName)
    {
        $level = $entityManager->getRepository(Level::class)->findOneBy(array('levelName' => $levelName));
        $difficulty = $entityManager->getRepository(Difficulty::class)->findOneBy(array('level' => $level, 'difficultyName' => $difficultyName));
        $boxAmount = $difficulty->getTakesPlaces()->count();
        $pageAmount = ceil($boxAmount/6);
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
            'title' => 'Game',
            'pageName' => $difficulty->getDisplayName(),
            'firstName' => "FisrtName",
            'lastName' => "LasteName",
            'boxAmount' => $boxAmount,
            'boxPercentWidth' => 25,
            'role' => 'Roels',
            'pageAmount' => $pageAmount,
            'scenes' => array_unique($scenes, SORT_REGULAR),
            'characters' => array_unique($characters, SORT_REGULAR),
            'difficultyId' => $difficulty->getId()
            //'solution' => $solution
        ]);
    }

    #[Route('/level/{levelName}/{difficultyName}/has-next')]
    public function hasNextDifficulty(EntityManagerInterface $entityManager, string $levelName, string $difficultyName){
        $level = $entityManager->getRepository(Level::class)->findOneBy(array('levelName' => $levelName));
        $previousDifficulty = $entityManager->getRepository(Difficulty::class)->findOneBy(array('level' => $level, 'difficultyName' => $difficultyName));

        $previousDifficultyId = array_search($previousDifficulty, $level->getDifficulties()->toArray());

        return new Response(count($level->getDifficulties()) > $previousDifficultyId + 1 ? 'true' : 'false');
    }

    #[Route('/level/{levelName}/{difficultyName}/next')]
    public function loadNextDifficulty(EntityManagerInterface $entityManager, string $levelName, string $difficultyName){
        $level = $entityManager->getRepository(Level::class)->findOneBy(array('levelName' => $levelName));
        $previousDifficulty = $entityManager->getRepository(Difficulty::class)->findOneBy(array('level' => $level, 'difficultyName' => $difficultyName));

        $previousDifficultyId = array_search($previousDifficulty, $level->getDifficulties()->toArray());

        if(count($level->getDifficulties()) > $previousDifficultyId + 1){
            // Redirige vers la difficulté suivante
            return $this->redirectToRoute('level', ['levelName' => $levelName, 'difficultyName' => $level->getDifficulties()[$previousDifficultyId + 1]->getDifficultyName()]);
        }
        else{
            // Ramène vers la page de sélection de niveau
            return $this->redirectToRoute('app_studentpage_studentpages');
        }
    }

    #[Route('/level/{levelName}/{difficultyName}/action-context', methods: "POST")]
    public function getActionContext(EntityManagerInterface $entityManager, string $levelName, string $difficultyName, Request $request){

        $actionId = intval(json_decode($request->getContent(), true)['actionId']);

        $level = $entityManager->getRepository(Level::class)->findOneBy(array('levelName' => $levelName));
        $difficulty = $entityManager->getRepository(Difficulty::class)->findOneBy(array('level' => $level, 'difficultyName' => $difficultyName));

        $actionContext = ActionController::getAction($entityManager, $actionId)->getContext();

        return new Response($actionContext);
    }

    #[Route('/level/{levelName}/{difficultyName}/solution', methods: "POST")]
    public function checkLevelSolution(EntityManagerInterface $entityManager, string $levelName, string $difficultyName, Request $request){

        $answer = json_decode($request->getContent(), true)['answer'];

        // Traitement du cas particulier où la réponse donnée est vide
        if(count($answer) == 0){
            return new JsonResponse(['isValid' => 'false', 'firstInvalidBox' => 0]);
        }

        $level = $entityManager->getRepository(Level::class)->findOneBy(array('levelName' => $levelName));
        $difficulty = $entityManager->getRepository(Difficulty::class)->findOneBy(array('level' => $level, 'difficultyName' => $difficultyName));

        $takesPlaces = $entityManager->getRepository(TakesPlace::class)->findBy(array('difficulty' => $difficulty));

        $hasFoundValidCase = false;
        $firstInvalidBox = -1;
        $actionIds = array();
        for ($takesPlaceCaseIndex = 0; $takesPlaceCaseIndex < $this->getCaseAmount($takesPlaces); $takesPlaceCaseIndex++) {

            $isCaseValid = true;
            $caseFirstInvalidBox = -1;
            $caseActionIds = array();

            for($boxAnswerIndex = 0; $boxAnswerIndex < count($takesPlaces); $boxAnswerIndex++){

                if(count($answer) > $boxAnswerIndex){
                    $result = $this->isBoxValid($entityManager, $difficulty, $boxAnswerIndex, $answer[$boxAnswerIndex], $takesPlaceCaseIndex);
                    $isBoxValid = $result['isBoxValid'];
                    if($isBoxValid){
                        array_push($caseActionIds, $result['action']->getId());
                    }
                }
                else{   // Invalide si réponse ne couvre pas tous les takes place
                    $isBoxValid = false;
                }

                if(!$isBoxValid){
                    $isCaseValid = false;

                    $caseFirstInvalidBox = $boxAnswerIndex;
                    //die('box : ' . $boxAnswerIndex . ' is invalid');
                    break;

                }
            }
            if($isCaseValid){
                $hasFoundValidCase = true;
                $firstInvalidBox = -1;
                $actionIds = $caseActionIds;
            }
            else if(!$hasFoundValidCase && $caseFirstInvalidBox > $firstInvalidBox){
                $firstInvalidBox = $caseFirstInvalidBox;
            }
        }

        //$firstInvalidBox = $hasFoundValidCase ? -1 : $firstInvalidBox;

        return new JsonResponse(['isValid' => ($hasFoundValidCase ? 'true' : 'false'), 'firstInvalidBox' => $firstInvalidBox, 'actionIds' => $actionIds]);
    }

    #[Route('/level/{levelName}/{difficultyName}/solution/manage-error', methods: "POST")]
    public function manageError(EntityManagerInterface $entityManager,string $levelName, string $difficultyName, Request $request)
    {
        $postData = json_decode($request->getContent(), true);
        $studentError= $postData["error"] ??  '';
        if ($studentError != ''){
            $student = $entityManager->getRepository(User::class)->findOneBy(array('username' => $this->getUser()->getUserIdentifier()));
            $difficulty = $entityManager->getRepository(Difficulty::class)->findOneBy(array('difficultyName' => $difficultyName));
            $plays = PlaysController::getPlays($entityManager, $student, $difficulty);
            if ($studentError == 'true'){
                $plays->incrementErrorCount();
            }else if ($studentError == 'false'){
                $plays->setIsCompleted(true);
            }
            $entityManager->persist($plays);
            $entityManager->flush();
        }
        return new \Symfony\Component\HttpFoundation\Response();
    }

    private function isBoxValid(EntityManagerInterface $entityManager, $difficulty, $currentPosition, $boxAnswer, $takesPlaceCaseIndex)
    {
        $currentSceneId = $boxAnswer['SceneId'];
        $currentScene = $entityManager->getRepository(Scene::class)->findOneBy(array('id' => $currentSceneId));

        //Les actions pouvant correspondre à la scène qu'a placé le joueur
        $actionsWithSameScene = $entityManager->getRepository(Action::class)->findBy(array('scene' => $currentScene));
        $relatedActions = array();
        foreach ($actionsWithSameScene as $action){
            if($entityManager->getRepository(TakesPlace::class)->findOneBy(array('action' => $action, 'difficulty' => $difficulty)) != null){
                array_push($relatedActions, $action);
            }
        }

        $compatibleAction = $this->getActionFromCharacters($entityManager, $relatedActions, $boxAnswer['CharacterContainers']);

        if($compatibleAction == null){

            //die('Scene : ' . $boxAnswer['SceneId'] . ' ne peut pas avoir les personnages : ' . json_encode($boxAnswer['CharacterContainers']));
            return ['isBoxValid' => false, 'action' => null];
        }
        /*if($currentPosition == 1){
            die(json_encode(array('action id' => $compatibleAction->getId())));
        }*/

        $takesPlace = $entityManager->getRepository(TakesPlace::class)->findOneBy(array('action' => $compatibleAction, 'difficulty' => $difficulty));

        //die(explode('|', strval($takesPlace->getPositionId()))[$takesPlaceCaseIndex] == $currentPosition + 1 ? 'true' : 'false');
        //die('exploded : ' . explode('|', strval($takesPlace->getPositionId()))[$takesPlaceCaseIndex] . ', rough' . strval($takesPlace->getPositionId()) . ', currentPosition : ' . ($currentPosition + 1));
        /*if(explode('|', strval($takesPlace->getPositionId()))[$takesPlaceCaseIndex] != $currentPosition + 1){
            die(json_encode('takesPlace : '.$takesPlace->getId().', exploded : '.explode('|', strval($takesPlace->getPositionId()))[$takesPlaceCaseIndex].', rough : ' . strval($takesPlace->getPositionId()).', currentPosition : '.($currentPosition + 1)));
        }*/

        $isThisBoxValid = explode('|', strval($takesPlace->getPositionId()))[$takesPlaceCaseIndex] == $currentPosition + 1;

        return ['isBoxValid' => $isThisBoxValid, 'action' => $compatibleAction];
    }

    private function getActionFromCharacters(EntityManagerInterface $entityManager, $possibleActions, $characterContainersAnswer){

        $foundAction = null;

        foreach ($possibleActions as $action){

            $intervenes = $entityManager->getRepository(Intervenes::class)->findBy(array('action' => $action));

            $hasFoundValidCase = false;

            for ($IntervenesCaseIndex = 0; $IntervenesCaseIndex < $this->getCaseAmount($intervenes); $IntervenesCaseIndex++) {

                $isCurrentCaseValid = true;

                for ($containerAnswerIndex = 0; $containerAnswerIndex < count($characterContainersAnswer); $containerAnswerIndex++){

                    $isContainerValid = $this->isCharacterContainerValid($intervenes, $characterContainersAnswer[$containerAnswerIndex], $IntervenesCaseIndex);

                    if(!$isContainerValid){
                        $isCurrentCaseValid = false;
                    }
                }

                if($isCurrentCaseValid){
                    $hasFoundValidCase = true;
                }
            }

            if($hasFoundValidCase){
                $foundAction = $action;
            }
        }
        return $foundAction;
    }

    private function isCharacterContainerValid($intervenes, $characterContainerAnswer, $intervenesCaseIndex): bool
    {
        $currentContainerId = $characterContainerAnswer['containerId'];
        $characterId = $characterContainerAnswer['characterId'];

        $hasFoundValidIntervene = false;
        foreach($intervenes as $intervene){
            $wantedPositionId = explode('|', $intervene->getPositionId())[$intervenesCaseIndex];
            $wantedCharacterId = $intervene->getHistoricCharacter()->getId();

            if($currentContainerId == $wantedPositionId && $characterId == $wantedCharacterId){
                $hasFoundValidIntervene = true;
            }
        }
        return $hasFoundValidIntervene;
    }

    private function getCaseAmount(array $array){
        $maxCases = 0;

        for($i = 0; $i < count($array); $i++){
            $currentProperty = $array[$i]->getPositionId();
            $currentItemCaseAmount = count(explode('|', strval($currentProperty)));
            if($currentItemCaseAmount > $maxCases){
                $maxCases = $currentItemCaseAmount;
            }
        }
        return $maxCases;
    }
}