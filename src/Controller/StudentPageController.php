<?php

namespace App\Controller;

use App\Controller\EntityController\PlaysController;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class StudentPageController extends AbstractController
{

    #[Route('/student')]
    public function studentPages(EntityManagerInterface $entityManager) : Response{
        $student = $entityManager->getRepository(User::class)->findOneBy(array('username'=>$this->getUser()->getUserIdentifier()));
        $levelCompleted = $student->getLevelsPlayed();
        $studentRoomLevels = $student->getRoom()->getLevels();
        $data = [];

        foreach ($studentRoomLevels as $i){
            $firstUncompletedDifficulty = PlaysController::getFirstUncompletedDifficulty($entityManager, $student, $i);

            if($firstUncompletedDifficulty != null){
                $levelPath = '/level/'.$i->getLevelName().'/'.$firstUncompletedDifficulty?->getDifficultyName();
                $isLevelEnable = true;
            }
            else{
                $levelPath = '/student';
                $isLevelEnable = false;
            }
            $data[] = ['level' => $i->getDisplayName(),'gameLevel' => $levelPath, 'isLevelEnabled' => $isLevelEnable];
        }
        if ($data == null){
            return $this->render('Student/StudentLevel.html.twig', [
               'pageName' => 'Il n\'y pas encore de Chapitre disponible'
            ]);
        }else {
            $columnName = ['column1' => 'Nom du chapitre',
                'column2' => 'Commencer le chapitre',
            ];

            return $this->forward('\App\Controller\ListController::renderBaseList', [
                'pageName' => 'Chapitre',
                'data' => $data,
                'columnName' => $columnName,
                'itemPath' => 'List/Student/StudentLevelList.html.twig',
                'basePath' => 'Student/StudentLevel.html.twig',
                'className' => '',
            ]);
        }
    }
}