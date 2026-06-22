<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ListController extends AbstractController
{
    public function renderBaseList(string $pageName, array $data, string $itemPath, array $columnName, string $id = '',
                                   string $popupAcceptedText ='', string $popupDeniedText ='',  string $listClasses = '',
                                   string $basePath = 'Base.html.twig') : Response
    {
        $columnBase = ['column1' => '', 'column2' => '', 'column3' => '', 'column4' => '', 'column5' => '', 'column6' => ''];
        $columnName = $columnName + $columnBase;
        return $this->render('List/List.html.twig', [
            'pageName' => $pageName,
            'basePath' => $basePath,
            'data' => $data,
            'columnName' => $columnName,
            'id' => $id,
            'listClasses' => $listClasses,
            'itemPath' => $itemPath,
            'popupAcceptedText' => $popupAcceptedText,
            'popupDeniedText' => $popupDeniedText,
            ''
        ]);
    }
}