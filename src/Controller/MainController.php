<?php

namespace App\Controller;

use http\Env\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{
    #[Route('/')]
    public function homePage()
    {
        $empty = '';

       return $this->render('Homepage/Homepage.html.twig', [
       ]);
    }
    #[Route('/Password' , methods: 'POST')]
    public function changePasswordPage() : Response{

        return $this->render('Password/ChangePassword.html.twig', [
            'pageName' => 'Modifier son mot de passe',
            ]);
    }
    #[Route('/terms-of-use')]
    public function termsOfUsePage()
    {
        return $this->render('registration/TermsOfUse.html.twig', [
            'pageName' => 'Conditions utilisation'
        ]);
    }

    #[Route('/contact-form')]
    public function contactFormPage()
    {
        return $this->render('Base/ContactForm.html.twig', [
            'pageName' => 'Formulaire de contact'
        ]);
    }
}