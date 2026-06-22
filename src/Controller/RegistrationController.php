<?php

namespace App\Controller;


use App\Controller\EntityController\UserController;
use App\Entity\Room;
use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Form\StudentRegistrationFormType;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {

        $teacher = new User();
        $form = $this->createForm(RegistrationFormType::class, $teacher);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password

            $this->encodeThePlainPassword($teacher, $userPasswordHasher, $form, $entityManager);
            $teacher->setRoles(['ROLE_PENDING_TEACHER']);
            $teacher->setCreationDate(new \DateTime());

            $entityManager->persist($teacher);
            $entityManager->flush();
            // do anything else you need here, like send an email

            return $this->redirectToRoute('app_main_homepage');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
            'pageName' => 'Demander un compte professeur',
        ]);
    }

    public function getTeacher(EntityManagerInterface $entityManager){
        return $entityManager->getRepository(User::class)->findOneBy(array('username'=>$this->getUser()->getUserIdentifier()));
    }

    public function getCurrentRoom(EntityManagerInterface $entityManager, $roomId){
        return $entityManager->getRepository(Room::class)->find($roomId);
    }

    #[Route('/teacher/{slug}/manage-students/create-student', name: 'app_register_student')]
    public function registerUser(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager,$slug = null): Response
    {
        $student = new User();
        $form = $this->createForm(StudentRegistrationFormType::class, $student);
        $form->handleRequest($request);
        $pdfError = '';
        if ($form->isSubmitted() && $form->isValid()) {
            // Vérifie si le fichier a bien été uploadé sans erreurs
            if(!empty($_FILES)) {
                $file_name = $_FILES['student_registration_form']['name']['consentForm'];
                $file_extention = strrchr($file_name, ".");
                $file_tmp_name = $_FILES['student_registration_form']['tmp_name']['consentForm'];
                $file_dest = 'ConsentForm/' . $file_name;
                $authorised_exention = array('.pdf', '.PDF');
                if (in_array($file_extention, $authorised_exention)) {
                        move_uploaded_file($file_tmp_name, $file_dest);
                        // Fichier envoyé avec succès
                        // encode the plain password
                        $this->encodeThePlainPassword($student, $userPasswordHasher, $form, $entityManager);
                        $student->setRoom($this->getCurrentRoom($entityManager,$slug));
                        $student->setSchoolName($this->getTeacher($entityManager)->getSchoolName());
                        $student->setCreationDate(new DateTime());
                        $student->setRoles(['ROLE_PENDING_STUDENT']);
                        $student->setConsentForm($file_name);

                        $entityManager->persist($student);
                        $entityManager->flush();

                        $password = strtolower($student->getLastName()).'.'.strtolower($student->getFirstName());
                        UserController::changePassword($entityManager,$userPasswordHasher,$student->getId(),$password);

                        foreach ($this->getCurrentRoom($entityManager,intval($slug))->getLevels() as $level){
                            UserController::addLevelPlayed($entityManager,$student->getId(),$level);
                        }
                        // do anything else you need here, like send an email
                        echo '<script src="/JS/Base/PageManager.js"></script>';
                        echo "<script> subToPath('/create-student'); </script>";

                } else {
                    $pdfError = "Seul les fichier pdf sont autorisée";
                }
            }
        }

        return $this->render('registration/registerStudent.html.twig', [
            'registrationForm' => $form->createView(),
            'pageName' => 'Inscrire un élève',
            'pdfError' => $pdfError,
        ]);
    }

    /**
     * @param User $user
     * @param UserPasswordHasherInterface $userPasswordHasher
     * @param \Symfony\Component\Form\FormInterface $form
     * @param EntityManagerInterface $entityManager
     * @return void
     */
    private function encodeThePlainPassword(User $user, UserPasswordHasherInterface $userPasswordHasher, \Symfony\Component\Form\FormInterface $form, EntityManagerInterface $entityManager): void
    {
        $user->setPassword(
            $userPasswordHasher->hashPassword(
                $user,
                $form->get('plainPassword')->getData()
            )
        );
        $user->setUsername(strtolower(substr($user->getLastName(), 0, 7) . substr($user->getFirstName(), 0, 1)));
        if ($entityManager->getRepository(User::class)->findOneBy(array("username" => $user->getUsername()))) {
            $username_increment = 2;
            while ($entityManager->getRepository(User::class)->findOneBy(array("username" => $user->getUsername() . $username_increment))) {
                $username_increment++;
            }
            $user->setUsername($user->getUsername() . $username_increment);
        }
    }
}
