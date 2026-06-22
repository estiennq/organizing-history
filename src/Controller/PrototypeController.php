<?php

namespace App\Controller;

use App\Controller\EntityController\ActionController;
use App\Controller\EntityController\CharacterPositionController;
use App\Controller\EntityController\DifficultyController;
use App\Controller\EntityController\HistoricCharacterController;
use App\Controller\EntityController\LevelController;
use App\Controller\EntityController\RoomController;
use App\Controller\EntityController\SceneController;

use App\Controller\EntityController\UserController;
use App\Entity\CharacterPosition;
use App\Entity\HistoricCharacter;
use App\Entity\Level;
use App\Entity\Room;
use App\Entity\Scene;
use App\Entity\User;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class PrototypeController extends AbstractController
{
    #[Route('/prototype/dragdrop')]
    function dragDropPage()
    {
        return $this->render('Prototype/DragDropPrototype.html');
    }
    public function generateRandomLastName($i) {
        $noms = ['Blanchard', 'Lépine', 'Cormier', 'Lemieux', 'Pelletier', 'Archambault', 'Gauthier', 'Trudeau',
            'Daigneault', 'Morissette', 'Faucher', 'Bérubé', 'Nadeau', 'Guérin', 'Lacombe', 'Turcotte', 'Piché', 'Plourde',
            'Royer', 'Lussier', 'Russo','Joly','Perraud','Fabrier','Dupond', 'Soufre','Diakaté','Vaz-Gonsalvez','Lafraise',
            'Friend', 'Théberge', 'Vadnais', 'Charron', 'Rosales', 'Clay', 'Azuka', 'Reyes', 'Cardona', 'Rackers', 'Berggren',
            'Gerste', 'Havasy', 'Holm', 'Novak', 'Mutou', 'Kleist', 'Oster', 'Fournier', 'Stedman', 'Baba', 'Hakimi', 'Holtzmann',
            'Bagi', 'Baz', 'Ueno', 'Hermansson', 'Petrussen', 'Bosanac', 'Sugita', 'Stipanov', 'Behrend', 'Kelly', 'Caballero',
            'Smallburrow', 'Mamelu', 'Pettersson', 'Sokolova', 'Seeth', 'Balun', 'Trentino'];
        return $noms[$i];
    }

    public function generateRandomFirstName($i) {
        $prenoms = ['Marcel', 'Simone', 'Roger', 'Lucienne',
                    'René', 'Gilbert', 'Paulette', 'Maurice',
                    'Denise', 'André', 'Hélène', 'Yves', 'Gisèle',
                    'Claude', 'Monique', 'Jacques', 'Marie-Thérèse',
                    'Raymond', 'Colette', 'Henri', 'Sophie', 'Julien',
                    'Jade', 'Alexi', 'Tom', 'Camille', 'Evan', 'Niko',
                    'Noémie','Sarah'
        ];
        return $prenoms[$i];
    }

    public function generateRandomSchoolName($i) {
        $ecoles = ['Collège Saint-Exupéry', 'Collège Montaigne', 'Collège Voltaire',
            'Collège Victor Hugo', 'Collège Jules Verne', 'Collège Saint-Michel',
            'Collège Balzac', 'Collège Descartes', 'Collège Pasteur', 'Collège Curie',
            'Collège Molière', 'Collège Rousseau', 'Collège La Fontaine', 'Collège Corneille',
            'Collège Racine', 'Collège Péguy', 'Collège Maupassant', 'Collège Sartre',
            'Collège Camus', 'Collège Zola'
        ];
        return $ecoles[$i];
    }

    #[Route('/prototype/generate-all')]
    function generateAll(EntityManagerInterface $entityManager, UserPasswordHasherInterface $userPasswordHasher)
    {
        // Création du Level 1 - 1
        $this->generateLevel1($entityManager);

        // Création du Level 1 - 2
        $this->generateLevel2($entityManager);

        // Création du Level 2 - 3
        $this->generateLevel3($entityManager);

        $level1 = $entityManager->getRepository(Level::class)->findOneBy(array("levelName" => 'ww1'));
        $level2 = $entityManager->getRepository(Level::class)->findOneBy(array("levelName" => 'ww2'));



        // Création Teachers
        $teacher1Id = UserController::newTeacher($entityManager, $userPasswordHasher, 'Blanchard', 'Marcel', 123456, 'Collège Saint-Exupéry', 'xxx@xxx.com');
        $teacher1 = $entityManager->getRepository(User::class)->find($teacher1Id);
        $teacher1->setStatus('accepted');
        $teacher1->setRoles(['ROLE_TEACHER']);

        $teacher2Id = UserController::newTeacher($entityManager, $userPasswordHasher, 'Faucher', 'Hélène', 123456, 'Collège Montaigne', 'xxx@xxx.com');
        $teacher2 = $entityManager->getRepository(User::class)->find($teacher2Id);
        $teacher2->setStatus('accepted');
        $teacher2->setRoles(['ROLE_TEACHER']);



        //Créations Rooms
        $room1Id = RoomController::newRoom($entityManager, '3ème 2', 'description', $teacher1);
        $room1 = $entityManager->getRepository(Room::class)->find($room1Id);
        RoomController::addLevel($entityManager, $room1Id, $level1);
        RoomController::addLevel($entityManager, $room1Id, $level2);

        $room2Id = RoomController::newRoom($entityManager, '3ème 4', 'description', $teacher2);
        $room2 = $entityManager->getRepository(Room::class)->find($room2Id);
        RoomController::addLevel($entityManager, $room2Id, $level1);
        RoomController::addLevel($entityManager, $room2Id, $level2);



        // Créations Students

        // Students de Room1
        $studentId = UserController::newStudent($entityManager, $userPasswordHasher, 'Royer', 'Colette', '123456', $room1, 'royer-colette.pdf', 'xxx@xxx.com');
        $student = $entityManager->getRepository(User::class)->find($studentId);
        $student->setStatus('accepted');
        $student->setRoles(['ROLE_STUDENT']);

        $studentId = UserController::newStudent($entityManager, $userPasswordHasher, 'Dupond', 'Lucienne', '123456', $room1, 'dupond-lucienne.pdf', 'xxx@xxx.com');
        $student = $entityManager->getRepository(User::class)->find($studentId);
        $student->setStatus('accepted');
        $student->setRoles(['ROLE_STUDENT']);

        $studentId = UserController::newStudent($entityManager, $userPasswordHasher, 'Lussier', 'Henri', '123456', $room1, 'lussier-henri.pdf', 'xxx@xxx.com');
        $student = $entityManager->getRepository(User::class)->find($studentId);
        $student->setStatus('accepted');
        $student->setRoles(['ROLE_STUDENT']);

        $studentId = UserController::newStudent($entityManager, $userPasswordHasher, 'Russo', 'Sophie', '123456', $room1, 'russo-sophie.pdf', 'xxx@xxx.com');
        $student = $entityManager->getRepository(User::class)->find($studentId);
        $student->setStatus('accepted');
        $student->setRoles(['ROLE_STUDENT']);

        $studentId = UserController::newStudent($entityManager, $userPasswordHasher, 'Joly', 'Julien', '123456', $room1, 'joly-julien.pdf', 'xxx@xxx.com');
        $student = $entityManager->getRepository(User::class)->find($studentId);
        $student->setStatus('accepted');
        $student->setRoles(['ROLE_STUDENT']);


        // Students de Room2
        $studentId = UserController::newStudent($entityManager, $userPasswordHasher, 'Archambault', 'Paulette', '123456', $room2, 'archambault-paulette.pdf', 'xxx@xxx.com');
        $student = $entityManager->getRepository(User::class)->find($studentId);
        $student->setStatus('accepted');
        $student->setRoles(['ROLE_STUDENT']);
        $levelPlayed = $student->getLevelsPlayed()->get(0);
        $levelPlayed->incrementErrorCount();
        $levelPlayed->setIsCompleted(true);

        $studentId = UserController::newStudent($entityManager, $userPasswordHasher, 'Gauthier', 'Maurice', '123456', $room2, 'gauthier-maurice.pdf', 'xxx@xxx.com');
        $student = $entityManager->getRepository(User::class)->find($studentId);
        $student->setStatus('accepted');
        $student->setRoles(['ROLE_STUDENT']);

        $studentId = UserController::newStudent($entityManager, $userPasswordHasher, 'Trudeau', 'Denise', '123456', $room2, 'trudeau-denise.pdf', 'xxx@xxx.com');
        $student = $entityManager->getRepository(User::class)->find($studentId);
        $student->setStatus('accepted');
        $student->setRoles(['ROLE_STUDENT']);
        $levelPlayed = $student->getLevelsPlayed()->get(0);
        $levelPlayed->incrementErrorCount();$levelPlayed->incrementErrorCount();$levelPlayed->incrementErrorCount();
        $levelPlayed = $student->getLevelsPlayed()->get(2);
        $levelPlayed->incrementErrorCount();
        $levelPlayed->setIsCompleted(true);

        $studentId = UserController::newStudent($entityManager, $userPasswordHasher, 'Daigneault', 'André', '123456', $room2, 'daigneault-andré.pdf', 'xxx@xxx.com');
        $student = $entityManager->getRepository(User::class)->find($studentId);
        $student->setStatus('accepted');
        $student->setRoles(['ROLE_STUDENT']);
        $levelPlayed = $student->getLevelsPlayed()->get(0);
        $levelPlayed->incrementErrorCount();
        $levelPlayed->setIsCompleted(true);
        $levelPlayed = $student->getLevelsPlayed()->get(1);
        $levelPlayed->incrementErrorCount();$levelPlayed->incrementErrorCount();
        $levelPlayed = $student->getLevelsPlayed()->get(2);
        $levelPlayed->setIsCompleted(true);

        $studentId = UserController::newStudent($entityManager, $userPasswordHasher, 'Morissette', 'Yves', '123456', $room2, 'morissette-yves.pdf', 'xxx@xxx.com');
        $student = $entityManager->getRepository(User::class)->find($studentId);
        $student->setStatus('accepted');
        $student->setRoles(['ROLE_STUDENT']);
        $levelPlayed = $student->getLevelsPlayed()->get(2);
        $levelPlayed->incrementErrorCount();$levelPlayed->incrementErrorCount();$levelPlayed->incrementErrorCount();



        // Création Admin
        $admin = new User();
        $admin->setLastName('admin');
        $admin->setFirstName('');
        $admin->setUsername(strtolower(substr($admin->getLastName(), 0, 7).substr($admin->getFirstName(), 0, 1)));
        $admin->setRoles(['ROLE_ADMIN', 'ROLE_TEACHER', 'ROLE_STUDENT']);
        $admin->setStatus('accepted');
        $admin->setSchoolName('iut2');
        $admin->setPassword($userPasswordHasher->hashPassword($admin,'123456'));
        $admin->setConsentForm('Base/ConsentForm/Base/Formulaire-de-Consentement-Organizing-History.pdf');
        $admin->setCreationDate(new DateTime());
        $admin->setEmail('xxx@xxx.com');
        $entityManager->persist($admin);
        $entityManager->flush();

        $roomId = RoomController::newRoom($entityManager, 'admin', 'description', $admin);
        $room = $entityManager->getRepository(Room::class)->find($roomId);
        $admin->setRoom($room);
        $room->addStudent($admin);
        $entityManager->persist($admin);
        $entityManager->persist($room);
        $entityManager->flush();

        RoomController::addLevel($entityManager, $roomId, $level1);
        RoomController::addLevel($entityManager, $roomId, $level2);
        $entityManager->persist($admin);
        $entityManager->flush();

        return new Response('Everything created with success');
    }

    #[Route('/prototype/json-test')]
    function jsonResponseTest(EntityManagerInterface $entityManager)
    {
        return new JsonResponse(['Test' => 'Ca marche !']);
    }

    #[Route('/prototype/generate-level-0')]
    function generateLevel0(EntityManagerInterface $entityManager)
    {
        //Level
        $levelId = LevelController::newLevel($entityManager, 'tests', 'tests');

        //Difficulty
        $difficultyId = DifficultyController::newDifficulty($entityManager, '1box', '1box', LevelController::getLevel($entityManager, $levelId));

        $sceneTestId = SceneController:: newScene($entityManager, 'Test', "Test");

        $charaPosTestId = CharacterPositionController::newCharacterPosition(
            $entityManager, "_", 41, 15.5, 0, 0, SceneController::getScene($entityManager, $sceneTestId));

        $action1Id = ActionController:: newAction($entityManager, 'level0 context0', SceneController::getScene($entityManager, $sceneTestId));

        $usaId = HistoricCharacterController:: newHistoricCharacter($entityManager, 'États-Unis', 'USA');

        ActionController::addHistoricCharacter($entityManager, $action1Id,
            HistoricCharacterController::getHistoricCharacter($entityManager, $usaId), $charaPosTestId, '');

        DifficultyController::addAction($entityManager, $difficultyId, ActionController::getAction($entityManager, $action1Id), '1');

        return new Response('Level 0 created with success');
    }

    #[Route('/prototype/generate-level-1')]
    function generateLevel1(EntityManagerInterface $entityManager)
    {
        //Level
        $levelId = LevelController::newLevel($entityManager, 'ww1', 'La Première Guerre mondiale (1914 - 1918)');

        //Difficulty
        $difficultyId = DifficultyController::newDifficulty($entityManager, 'ww1-1', 'La Première Guerre mondiale (1914 - 1918) - 1', LevelController::getLevel($entityManager, $levelId));

        //Scenes
        $sceneWarId = SceneController:: newScene($entityManager, 'Guerre', "War");
        $sceneAllianceId = SceneController:: newScene($entityManager, 'Alliance', "Alliance");
        $sceneAssassinationId = SceneController:: newScene($entityManager, 'Assassinat', "Assassination");
        $sceneArmisticeId = SceneController:: newScene($entityManager, 'Armistice', "Armistice");
        $sceneAccuseId = SceneController:: newScene($entityManager, 'Accuse', "Accuse");

        //CharacterPositions
        $charaPosAllianceRightId = CharacterPositionController::newCharacterPosition(
            $entityManager, "alliance-right", 60, 15.5, -65,0, SceneController::getScene($entityManager, $sceneAllianceId));
        $charaPosAllianceLeftId = CharacterPositionController::newCharacterPosition(
            $entityManager, "alliance-left", 9, 15.5, 13, 0, SceneController::getScene($entityManager, $sceneAllianceId));
        $charaPosAssassinationId = CharacterPositionController::newCharacterPosition(
            $entityManager, "assassination", 59.5, 11, 0, 0, SceneController::getScene($entityManager, $sceneAssassinationId));
        $charaPosAccuseLeftId = CharacterPositionController::newCharacterPosition(
            $entityManager, "accuse-left", 8, 15.5, 5, 0, SceneController::getScene($entityManager, $sceneAccuseId));
        $charaPosAccuseRightId = CharacterPositionController::newCharacterPosition(
            $entityManager, "accuse-right", 60, 15.5, 0, -7, SceneController::getScene($entityManager, $sceneAccuseId));
        $charaPosWarLeftId = CharacterPositionController::newCharacterPosition(
            $entityManager, "war-left", 5, 15, -17, -6, SceneController::getScene($entityManager, $sceneWarId));
        $charaPosWarRightId = CharacterPositionController::newCharacterPosition(
            $entityManager, "war-right", 63, 15, 17, -6, SceneController::getScene($entityManager, $sceneWarId));
        $charaPosArmisticeLeftId = CharacterPositionController::newCharacterPosition(
            $entityManager, "armistice-left", 5, 10.5, -6, 2, SceneController::getScene($entityManager, $sceneArmisticeId));
        $charaPosArmisticeRightId = CharacterPositionController::newCharacterPosition(
            $entityManager, "armistice-right", 65, 10.5, 6, 2, SceneController::getScene($entityManager, $sceneArmisticeId));

        //Actions
        $action2Id = ActionController:: newAction($entityManager, "Depuis sa création en 1871, la Triple Alliance réunis l'Allemagne, l'Italie et l'Empire Austro-Hongrois. En 1896, François Ferdinand devient l'héritier du trone", SceneController::getScene($entityManager, $sceneAllianceId));
        $action3Id = ActionController:: newAction($entityManager, "Le 28 Juin 1914, L'archeduc François Ferdinand est assassiné à Sarajevo par un nationaliste serbe qui s'opposait à l'annexion des Austro-Hongrois", SceneController::getScene($entityManager, $sceneAssassinationId));
        $action1Id = ActionController:: newAction($entityManager, "En 1904, la Triple Entente s'engage a protéger la Serbie pour justifier une attaque contre la Triple Alliance qui s'étend trop vers l'Europe de l'Est et menace la Russie.", SceneController::getScene($entityManager, $sceneAllianceId));
        $action4Id = ActionController:: newAction($entityManager, "Suite à l'assassinat de François Ferdinand, la triple Alliance accuse la Serbie de nuire à l'Empire Austro-Hongrois.", SceneController::getScene($entityManager, $sceneAccuseId));
        $action5Id = ActionController:: newAction($entityManager, "Les alliances militaires entrainent la Première Guerre Mondiale le 3 Août 1914 qui implique la majorité des grandes puissances d'Europe. Une guerre qui durera 4 ans et causera 18 millions de morts.", SceneController::getScene($entityManager, $sceneWarId));
        $action6Id = ActionController:: newAction($entityManager, "Le 11 Septembre 1918 à 11h11, les dirigeants des pays participants signent un armistice dans le train du Maréchal Foch. L'Allemagne est condamné et cela met fin à la Premiere Guerre Mondiale.", SceneController::getScene($entityManager, $sceneArmisticeId));

        //HistoricCharacters
        $tripleEntenteId = HistoricCharacterController:: newHistoricCharacter($entityManager, 'Triple Entente', 'TripleEntente');
        $serbiaId = HistoricCharacterController:: newHistoricCharacter($entityManager, 'Serbie', 'Serbia');
        $tripleAllianceId = HistoricCharacterController:: newHistoricCharacter($entityManager, 'Triple Alliance', 'TripleAlliance');
        $ferdinandId = HistoricCharacterController:: newHistoricCharacter($entityManager, 'François Ferdinand', 'Ferdinand');

        //Add HistoricCharacters to Actions
        ActionController::addHistoricCharacter($entityManager, $action1Id,
            HistoricCharacterController::getHistoricCharacter($entityManager, $tripleEntenteId), $charaPosAllianceLeftId.'|'.$charaPosAllianceRightId, '');
        ActionController::addHistoricCharacter($entityManager, $action1Id,
            HistoricCharacterController::getHistoricCharacter($entityManager, $serbiaId), $charaPosAllianceRightId.'|'.$charaPosAllianceLeftId, '');

        ActionController::addHistoricCharacter($entityManager, $action2Id,
            HistoricCharacterController::getHistoricCharacter($entityManager, $tripleAllianceId), $charaPosAllianceLeftId.'|'.$charaPosAllianceRightId, '');
        ActionController::addHistoricCharacter($entityManager, $action2Id,
            HistoricCharacterController::getHistoricCharacter($entityManager, $ferdinandId), $charaPosAllianceRightId.'|'.$charaPosAllianceLeftId, '');

        ActionController::addHistoricCharacter($entityManager, $action3Id,
            HistoricCharacterController::getHistoricCharacter($entityManager, $ferdinandId), $charaPosAssassinationId, '');

        ActionController::addHistoricCharacter($entityManager, $action4Id,
            HistoricCharacterController::getHistoricCharacter($entityManager, $tripleAllianceId), $charaPosAccuseLeftId, '');
        ActionController::addHistoricCharacter($entityManager, $action4Id,
            HistoricCharacterController::getHistoricCharacter($entityManager, $serbiaId), $charaPosAccuseRightId, '');

        ActionController::addHistoricCharacter($entityManager, $action5Id,
            HistoricCharacterController::getHistoricCharacter($entityManager, $tripleEntenteId), $charaPosWarLeftId.'|'.$charaPosWarRightId, '');
        ActionController::addHistoricCharacter($entityManager, $action5Id,
            HistoricCharacterController::getHistoricCharacter($entityManager, $tripleAllianceId), $charaPosWarRightId.'|'.$charaPosWarLeftId, '');

        ActionController::addHistoricCharacter($entityManager, $action6Id,
            HistoricCharacterController::getHistoricCharacter($entityManager, $tripleEntenteId), $charaPosArmisticeLeftId.'|'.$charaPosArmisticeRightId, '');
        ActionController::addHistoricCharacter($entityManager, $action6Id,
            HistoricCharacterController::getHistoricCharacter($entityManager, $tripleAllianceId), $charaPosArmisticeRightId.'|'.$charaPosArmisticeLeftId, '');

        //Add Actions to Difficulty
        DifficultyController::addAction($entityManager, $difficultyId, ActionController::getAction($entityManager, $action1Id), '2');
        DifficultyController::addAction($entityManager, $difficultyId, ActionController::getAction($entityManager, $action2Id), '1');
        DifficultyController::addAction($entityManager, $difficultyId, ActionController::getAction($entityManager, $action3Id), '3');
        DifficultyController::addAction($entityManager, $difficultyId, ActionController::getAction($entityManager, $action4Id), '4');
        DifficultyController::addAction($entityManager, $difficultyId, ActionController::getAction($entityManager, $action5Id), '5');
        DifficultyController::addAction($entityManager, $difficultyId, ActionController::getAction($entityManager, $action6Id), '6');

        return new Response('Level 1 created with success');
    }

    #[Route('/prototype/generate-level-2')]
    function generateLevel2(EntityManagerInterface $entityManager)
    {
        //Level
        $level = $entityManager->getRepository(Level::class)->findOneBy(array("levelName" => 'ww1'));

        //Difficulty
        $difficultyId = DifficultyController::newDifficulty($entityManager, 'ww1-2', 'La Première Guerre mondiale (1914 - 1918) - 2', $level);

        //Scenes
        $sceneAlliance = $entityManager->getRepository(Scene::class)->findOneBy(array("sprite" => 'Alliance'));
        $sceneAllianceId = $sceneAlliance->getId();
        $sceneWarId = $entityManager->getRepository(Scene::class)->findOneBy(array("sprite" => "War"))->getId();
        $sceneAssassinationId = $entityManager->getRepository(Scene::class)->findOneBy(array("sprite" => "Assassination"))->getId();
        $sceneAccuseId = $entityManager->getRepository(Scene::class)->findOneBy(array("sprite" => "Accuse"))->getId();
        $sceneArmisticeId = $entityManager->getRepository(Scene::class)->findOneBy(array("sprite" => "Armistice"))->getId();
        $sceneLandingId = SceneController:: newScene($entityManager, 'Renfort', "Landing");
        $sceneFactoryId = SceneController:: newScene($entityManager, 'Usine', "Factory");

        //CharacterPositions
        $charaPosAllianceLeftId = $entityManager->getRepository(CharacterPosition::class)->findOneBy(array('name' =>"alliance-left", 'scene' => $sceneAlliance))->getId();
        $charaPosAllianceRightId = $entityManager->getRepository(CharacterPosition::class)->findOneBy(array('name' =>"alliance-right"))->getId();
        $charaPosAssassinationId = $entityManager->getRepository(CharacterPosition::class)->findOneBy(array('name' =>"assassination"))->getId();
        $charaPosAccuseLeftId = $entityManager->getRepository(CharacterPosition::class)->findOneBy(array('name' =>"accuse-left"))->getId();
        $charaPosAccstudentightId = $entityManager->getRepository(CharacterPosition::class)->findOneBy(array('name' =>"accuse-right"))->getId();
        $charaPosWarLeftId = $entityManager->getRepository(CharacterPosition::class)->findOneBy(array('name' =>"war-left"))->getId();
        $charaPosWarRightId = $entityManager->getRepository(CharacterPosition::class)->findOneBy(array('name' =>"war-right"))->getId();
        $charaPosArmisticeLeftId = $entityManager->getRepository(CharacterPosition::class)->findOneBy(array('name' =>"armistice-left"))->getId();
        $charaPosArmisticeRightId = $entityManager->getRepository(CharacterPosition::class)->findOneBy(array('name' =>"armistice-right"))->getId();
        $charaPosFactoryId = CharacterPositionController::newCharacterPosition(
            $entityManager, "factory", 40, 11, 0, 0, SceneController::getScene($entityManager, $sceneFactoryId));
        $charaPosLandingId = CharacterPositionController::newCharacterPosition(
            $entityManager, "landing", 27, 15, -12, -5, SceneController::getScene($entityManager, $sceneLandingId));

        //Actions
        $action1Id = ActionController:: newAction($entityManager, "En 1904, la Triple Entente s'engage a protéger la Serbie pour justifier une attaque contre la Triple Alliance qui s'étend trop vers l'Europe de l'Est et menace la Russie.", SceneController::getScene($entityManager, $sceneAllianceId));
        $action2Id = ActionController:: newAction($entityManager, "Depuis sa création en 1871, la Triple Alliance réunis l'Allemagne, l'Italie et l'Empire Austro-Hongrois. En 1896, François Ferdinand devient l'héritier du throne", SceneController::getScene($entityManager, $sceneAllianceId));
        $action3Id = ActionController:: newAction($entityManager, "Le 28 Juin 1914, L'archeduc François Ferdinand est assassiné à Sarajevo par un nationaliste serbe qui s'opposait à l'annexion des Austro-Hongrois", SceneController::getScene($entityManager, $sceneAssassinationId));
        $action4Id = ActionController:: newAction($entityManager, "Suite à l'assassinat de François Ferdinand, la triple Alliance accuse la Serbie de nuire à l'Empire Austro-Hongrois.", SceneController::getScene($entityManager, $sceneAccuseId));
        $action5Id = ActionController:: newAction($entityManager, "Les alliances militaires entrainent la Première Guerre Mondiale le 3 Août 1914 qui implique la majorité des grandes puissances d'Europe. Une guerre qui durera 4 ans et causera 18 millions de morts.", SceneController::getScene($entityManager, $sceneWarId));
        $action6Id = ActionController:: newAction($entityManager, "Après que des sous-marins Allemand attaquent des navires marchands americains, les Etats-Unis rejoignent la guerre du côté de la Triple Entante", SceneController::getScene($entityManager, $sceneLandingId));
        $action7Id = ActionController:: newAction($entityManager, "Avec tout les hommes apte de travailler envoyés au front et une mobilisation totale des pays, les femmes ont pris un role très important dans les usines pour la manufacture d'équipement militaire", SceneController::getScene($entityManager, $sceneFactoryId));
        $action8Id = ActionController:: newAction($entityManager, "Le 11 Septembre 1918 à 11h11, les dirigeants des pays participants signent un armistice dans le train du Maréchal Foch. La faute est mise sur l'Allemagne et cela met fin à la Premiere Guerre Mondiale.", SceneController::getScene($entityManager, $sceneArmisticeId));

        //HistoricCharacters
        $tripleEntenteId = $entityManager->getRepository(HistoricCharacter::class)->findOneBy(array('sprite' => 'TripleEntente'))->getId();
        $serbiaId = $entityManager->getRepository(HistoricCharacter::class)->findOneBy(array('sprite' => 'Serbia'))->getId();
        $tripleAllianceId = $entityManager->getRepository(HistoricCharacter::class)->findOneBy(array('sprite' => 'TripleAlliance'))->getId();
        $ferdinandId = $entityManager->getRepository(HistoricCharacter::class)->findOneBy(array('sprite' => 'Ferdinand'))->getId();
        $usaId = HistoricCharacterController:: newHistoricCharacter($entityManager, 'États-Unis', 'USA');
        $civilId = HistoricCharacterController:: newHistoricCharacter($entityManager, 'Civils', 'Civil');

        //Add HistoricCharacters to Actions
        ActionController::addHistoricCharacter($entityManager, $action1Id,
            HistoricCharacterController::getHistoricCharacter($entityManager, $tripleEntenteId), $charaPosAllianceLeftId.'|'.$charaPosAllianceRightId, '');
        ActionController::addHistoricCharacter($entityManager, $action1Id,
            HistoricCharacterController::getHistoricCharacter($entityManager, $serbiaId), $charaPosAllianceRightId.'|'.$charaPosAllianceLeftId, '');

        ActionController::addHistoricCharacter($entityManager, $action2Id,
            HistoricCharacterController::getHistoricCharacter($entityManager, $tripleAllianceId), $charaPosAllianceLeftId.'|'.$charaPosAllianceRightId, '');
        ActionController::addHistoricCharacter($entityManager, $action2Id,
            HistoricCharacterController::getHistoricCharacter($entityManager, $ferdinandId), $charaPosAllianceRightId.'|'.$charaPosAllianceLeftId, '');

        ActionController::addHistoricCharacter($entityManager, $action3Id,
            HistoricCharacterController::getHistoricCharacter($entityManager, $ferdinandId), $charaPosAssassinationId, '');

        ActionController::addHistoricCharacter($entityManager, $action4Id,
            HistoricCharacterController::getHistoricCharacter($entityManager, $tripleAllianceId), $charaPosAccuseLeftId, '');
        ActionController::addHistoricCharacter($entityManager, $action4Id,
            HistoricCharacterController::getHistoricCharacter($entityManager, $serbiaId), $charaPosAccstudentightId, '');

        ActionController::addHistoricCharacter($entityManager, $action5Id,
            HistoricCharacterController::getHistoricCharacter($entityManager, $tripleEntenteId), $charaPosWarLeftId.'|'.$charaPosWarRightId, '');
        ActionController::addHistoricCharacter($entityManager, $action5Id,
            HistoricCharacterController::getHistoricCharacter($entityManager, $tripleAllianceId), $charaPosWarRightId.'|'.$charaPosWarLeftId, '');

        ActionController::addHistoricCharacter($entityManager, $action6Id,
            HistoricCharacterController::getHistoricCharacter($entityManager, $usaId), $charaPosLandingId, '');

        ActionController::addHistoricCharacter($entityManager, $action7Id,
            HistoricCharacterController::getHistoricCharacter($entityManager, $civilId), $charaPosFactoryId, '');

        ActionController::addHistoricCharacter($entityManager, $action8Id,
            HistoricCharacterController::getHistoricCharacter($entityManager, $tripleEntenteId), $charaPosArmisticeLeftId.'|'.$charaPosArmisticeRightId, '');
        ActionController::addHistoricCharacter($entityManager, $action8Id,
            HistoricCharacterController::getHistoricCharacter($entityManager, $tripleAllianceId), $charaPosArmisticeRightId.'|'.$charaPosArmisticeLeftId, '');

        //Add Actions to Difficulty
        DifficultyController::addAction($entityManager, $difficultyId, ActionController::getAction($entityManager, $action1Id), '2|2');
        DifficultyController::addAction($entityManager, $difficultyId, ActionController::getAction($entityManager, $action2Id), '1|1');
        DifficultyController::addAction($entityManager, $difficultyId, ActionController::getAction($entityManager, $action3Id), '3|3');
        DifficultyController::addAction($entityManager, $difficultyId, ActionController::getAction($entityManager, $action4Id), '4|4');
        DifficultyController::addAction($entityManager, $difficultyId, ActionController::getAction($entityManager, $action5Id), '5|6');
        DifficultyController::addAction($entityManager, $difficultyId, ActionController::getAction($entityManager, $action6Id), '7|7');
        DifficultyController::addAction($entityManager, $difficultyId, ActionController::getAction($entityManager, $action7Id), '6|5');
        DifficultyController::addAction($entityManager, $difficultyId, ActionController::getAction($entityManager, $action8Id), '8|8');

        return new Response('Level 2 created with success');
    }

    #[Route('/prototype/generate-level-3')]
    function generateLevel3(EntityManagerInterface $entityManager){
        //Level
        $levelId = LevelController::newLevel($entityManager, 'ww2', 'La Seconde Guerre mondiale (1939 - 1945)');

        //Difficulty
        $difficultyId = DifficultyController::newDifficulty($entityManager, 'ww2-1', "La Seconde Guerre mondiale (1939 - 1945) - 1", LevelController::getLevel($entityManager, $levelId));

        //Scenes
        $sceneTakeoverId = SceneController:: newScene($entityManager, 'Prise du pouvoir', "Takeover");
        $scenePersecutionId = SceneController:: newScene($entityManager, 'Persécution', "Persecution");
        $sceneConquestId = SceneController:: newScene($entityManager, 'Conquète', "Conquest");
        $sceneArmisticeId = $entityManager->getRepository(Scene::class)->findOneBy(array("sprite" => "Armistice"))->getId();
        $sceneAppealId = SceneController:: newScene($entityManager, 'Appel radio', "Appeal");
        $sceneSurrenderId = SceneController:: newScene($entityManager, 'Capitulation', "Surrender");

        //CharacterPositions
        $charaPosTakeoverId = CharacterPositionController::newCharacterPosition(
            $entityManager, "takeover", 36, 5, -50, -10, SceneController::getScene($entityManager, $sceneTakeoverId));
        $charaPosPersecutionId = CharacterPositionController::newCharacterPosition(
            $entityManager, "persecution", 5, 20, 0, 9, SceneController::getScene($entityManager, $scenePersecutionId));
        $charaPosConquestId = CharacterPositionController::newCharacterPosition(
            $entityManager, "conquest", 35, 14, -33, -10, SceneController::getScene($entityManager, $sceneConquestId));
        $charaPosArmisticeLeftId = $entityManager->getRepository(CharacterPosition::class)->findOneBy(array('name' =>"armistice-left"))->getId();
        $charaPosArmisticeRightId = $entityManager->getRepository(CharacterPosition::class)->findOneBy(array('name' =>"armistice-right"))->getId();
        $charaPosAppealId = CharacterPositionController::newCharacterPosition(
            $entityManager, "appeal", 20, 16.5, 0, 0, SceneController::getScene($entityManager, $sceneAppealId));
        $charaPosSurrenderId = CharacterPositionController::newCharacterPosition(
            $entityManager, "surrender", 34, 15, 0, -6, SceneController::getScene($entityManager, $sceneSurrenderId));

        //Actions
        $actionTakeoverId = ActionController:: newAction($entityManager, "Après la crise économique à la fin des années 20, l’Allemagne désespéré pour un changement radical, a élu le parti nazi ce qui a mis Adolf Hitler comme chancelier de l’Allemagne en 1933.", SceneController::getScene($entityManager, $sceneTakeoverId));
        $actionPersecutionId = ActionController:: newAction($entityManager, "Sous le régime nazi, le peuple allemand a mis la faute de leurs problèmes sur les “asociales” (handicapés, étrangers, Homosexuels et surtout les Juifs) qui ont vécu un traitement inhumain.", SceneController::getScene($entityManager, $scenePersecutionId));
        $actionConquestId = ActionController:: newAction($entityManager, "Après un réarmement militaire et le déploiement d’une nouvelle stratégie militaire (la blitzkrieg) la France a été pris par surprise par une  invasion à travers la Belgique et capitule après seulement 6 semaines de combats.", SceneController::getScene($entityManager, $sceneConquestId));
        $actionArmisticeId = ActionController:: newAction($entityManager, "Pour humilier la France, l’Allemagne fait signer l’armistice au maréchal Pétain dans le même wagon qui a détruit l’Allemagne à la fin de la Première Guerre Mondial en 1918.", SceneController::getScene($entityManager, $sceneArmisticeId));
        $actionAppealId = ActionController:: newAction($entityManager, "Pendant l’occupation Allemande de la France, le général de Gaule organisait la résistance française depuis Londres qui s’occupait de saboter les forces allemandes.", SceneController::getScene($entityManager, $sceneAppealId));
        $actionSurrenderId = ActionController:: newAction($entityManager, "Le 6 juin 1944, le jour du débarquement, plus de 150,000 soldats ont été déployé sur les plages de Normandie. Cumulé avec une bataille constante de leur front Est et les renforts américains, l’Allemagne s’écroula et Berlin s’est fait capturer le 2 mai 1945.", SceneController::getScene($entityManager, $sceneSurrenderId));

        //HistoricCharacters
        $hitlerId = HistoricCharacterController:: newHistoricCharacter($entityManager, 'Hitler', 'Hitler');
        $petainId = HistoricCharacterController:: newHistoricCharacter($entityManager, 'Philippe Pétain', 'Petain');
        $deGaulleId = HistoricCharacterController:: newHistoricCharacter($entityManager, 'Charles de Gaulle', 'DeGaulle');
        //$germanyId = HistoricCharacterController:: newHistoricCharacter($entityManager, 'Allemagne', 'Germany');

        //Add HistoricCharacters to Actions
        ActionController::addHistoricCharacter($entityManager, $actionTakeoverId,
            HistoricCharacterController::getHistoricCharacter($entityManager, $hitlerId), $charaPosTakeoverId, '');

        ActionController::addHistoricCharacter($entityManager, $actionPersecutionId,
            HistoricCharacterController::getHistoricCharacter($entityManager, $hitlerId), $charaPosPersecutionId, '');

        ActionController::addHistoricCharacter($entityManager, $actionConquestId,
            HistoricCharacterController::getHistoricCharacter($entityManager, $hitlerId), $charaPosConquestId, '');

        ActionController::addHistoricCharacter($entityManager, $actionArmisticeId,
            HistoricCharacterController::getHistoricCharacter($entityManager, $petainId), $charaPosArmisticeLeftId.'|'.$charaPosArmisticeRightId, '');
        ActionController::addHistoricCharacter($entityManager, $actionArmisticeId,
            HistoricCharacterController::getHistoricCharacter($entityManager, $hitlerId), $charaPosArmisticeRightId.'|'.$charaPosArmisticeLeftId, '');

        ActionController::addHistoricCharacter($entityManager, $actionAppealId,
            HistoricCharacterController::getHistoricCharacter($entityManager, $deGaulleId), $charaPosAppealId, '');

        ActionController::addHistoricCharacter($entityManager, $actionSurrenderId,
            HistoricCharacterController::getHistoricCharacter($entityManager, $hitlerId), $charaPosSurrenderId, '');

        //Add Actions to Difficulty
        DifficultyController::addAction($entityManager, $difficultyId, ActionController::getAction($entityManager, $actionTakeoverId), '1');
        DifficultyController::addAction($entityManager, $difficultyId, ActionController::getAction($entityManager, $actionPersecutionId), '2');
        DifficultyController::addAction($entityManager, $difficultyId, ActionController::getAction($entityManager, $actionConquestId), '3');
        DifficultyController::addAction($entityManager, $difficultyId, ActionController::getAction($entityManager, $actionArmisticeId), '4');
        DifficultyController::addAction($entityManager, $difficultyId, ActionController::getAction($entityManager, $actionAppealId), '5');
        DifficultyController::addAction($entityManager, $difficultyId, ActionController::getAction($entityManager, $actionSurrenderId), '6');

        return new Response('Level 3 created with success');
    }
}