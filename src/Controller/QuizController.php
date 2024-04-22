<?php

namespace App\Controller;

use App\Entity\Answer;
use App\Entity\Question;
use App\Entity\Quiz;
use App\Form\AnswerType;
use App\Form\QuestionType;
use App\Form\QuizType;
use App\Repository\AnswerRepository;
use App\Repository\QuestionRepository;
use App\Repository\QuizRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

// Contrôleur pour gérer les actions liées aux quiz
#[Route('/quiz')]
class QuizController extends AbstractController
{
    // Action pour afficher et gérer les quiz (pour les administrateurs)
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/', name: 'admin_quiz')]
    #[Route('/update/{id}', name: 'admin_quiz_update')]
    public function quiz(QuizRepository $repository, Request $request, EntityManagerInterface $manager, $id = null): Response
    {
        // Récupération de la liste des quiz depuis la base de données
        $quizes = $repository->findAll();

        // Gestion de la modification d'un quiz existant ou de l'ajout d'un nouveau quiz
        if ($id) {
            $quiz = $repository->find($id); // Modification d'un quiz existant
        } else {
            $quiz = new Quiz(); // Création d'un nouveau quiz
        }

        // Création du formulaire pour le quiz
        $form = $this->createForm(QuizType::class, $quiz);

        // GESTION DE LA REQUÊTE
        // Analyse de la requête HTTP
        $form->handleRequest($request);

        // Traitement du formulaire
        // Vérification si le formulaire a été soumis et est valide
        if ($form->isSubmitted() && $form->isValid()) {
            $quiz = $form->getData(); // Récupération des données du formulaire

            $manager->persist($quiz); // Persistation en base de données

            $manager->flush(); // Exécution de la transaction

            // Ajout d'un message flash pour indiquer le succès de l'opération
            $this->addFlash('success', 'Le quiz a bien été ajouté');

            // Redirection vers la liste des quiz
            return $this->redirectToRoute('admin_quiz');
        }

        // Rendu de la vue avec les quiz et le formulaire
        return $this->render('quiz/index.html.twig', [
            'quizes' => $quizes,
            'form' => $form->createView()
        ]);
    }

    // Action pour supprimer un quiz (pour les administrateurs)
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/delete/{id}', name: 'admin_quiz_delete')]
    public function delete(QuizRepository $repository, EntityManagerInterface $manager, $id = null): Response
    {
        // Récupération du quiz à supprimer
        if ($id) {
            $quiz = $repository->find($id);
        }

        // Suppression du quiz
        $manager->remove($quiz);
        $manager->flush();

        // Redirection vers la liste des quiz
        return $this->redirectToRoute('admin_quiz');
    }
    // Action pour gérer les questions (pour les administrateurs)
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/question', name: 'question')]
    public function question(QuestionRepository $repository, Request $request, EntityManagerInterface $manager, $id = null): Response
    {
        // Récupération de la liste des questions depuis la base de données
        $questions = $repository->findAll();

        // Gestion de la modification d'une question existante ou de l'ajout d'une nouvelle question
        if ($id) {
            $question = $repository->find($id); // Modification d'une question existante
        } else {
            $question = new Question(); // Création d'une nouvelle question
        }

        // Création du formulaire pour la question
        $form = $this->createForm(QuestionType::class, $question);
        $form->handleRequest($request);

        // TRAITEMENT DU FORMULAIRE
        // Vérification si le formulaire a été soumis et est valide
        if ($form->isSubmitted() && $form->isValid()) {
            $question = $form->getData(); // Récupération des données du formulaire

            $manager->persist($question); // Persistation des données en base de données

            $manager->flush(); // Exécution de la transaction

            // Ajout d'un message flash pour indiquer le succès de l'opération
            $this->addFlash('success', 'La question a bien été ajoutée');

            // Redirection vers la gestion des questions
            return $this->redirectToRoute('question');
        }
        // Rendu de la vue avec les questions et le formulaire
        return $this->render('quiz/question.html.twig', [
            'form' => $form->createView(),
            'questions' => $questions
        ]);
    }
    // Action pour permettre aux utilisateurs de répondre au quiz "PC"
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Route('/pc', name: 'quiz_pc')]
    public function pc(Request $request, QuestionRepository $questionRepository, AnswerRepository $answerRepository, EntityManagerInterface $manager): Response
    {
        // Récupération de toutes les questions et réponses
        $questions = $questionRepository->findAll();
        $answers = $answerRepository->findAll();

        // Récupération de l'utilisateur connecté
        $user = $this->getUser();

        // Traitement des réponses soumises par l'utilisateur
        if ($request->getMethod() === 'POST') {
            // Vérifie si le nombre de réponses envoyées par l'utilisateur est inférieur à 10
            if (count($request->request->all()['answers']) < 10) {
                // Ajoute un message flash d'avertissement
                $this->addFlash('danger', 'Veuillez répondre à toutes les questions, merci!');
                // Redirige l'utilisateur vers la page du quiz PC
                return $this->redirectToRoute('quiz_pc');
            }
            // Récupère toutes les réponses envoyées par l'utilisateur
            $userAnswers = $request->request->all()['answers'];
            // Parcourt chaque réponse de l'utilisateur
            foreach ($userAnswers as $answer) {
                // Divise la réponse pour obtenir l'identifiant de la réponse
                $array = explode('-', $answer);
                // Récupère l'ID de la réponse à partir du tableau obtenu
                $answerId = substr($array[1], 11);
                // Récupère la réponse à partir de son ID
                $answer = $answerRepository->find($answerId);
                // Ajoute la réponse à l'utilisateur
                $user->addAnswer($answer);
                // Persiste les modifications de l'utilisateur dans la base de données
                $manager->persist($user);
                // Enregistre les changements dans la base de données
                $manager->flush();
                // Ajoute un message flash d'avertissement
                $this->addFlash('success', 'Merci d\'avoir répondu aux questions. Je vous contacterai dès que possible.');
                // Redirige l'utilisateur vers la page d'accueil
                return $this->redirectToRoute('app_home');
            }
        }
        // Rendu de la vue pour le quiz "PC"
        return $this->render('quiz/pc.html.twig', [
            'questions' => $questions,
            'answers' => $answers,
            'user' => $user
        ]);
    }
    // Action pour permettre aux utilisateurs de répondre au quiz "Support"
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Route('/support', name: 'quiz_support')]
    public function support(Request $request, QuestionRepository $questionRepository, AnswerRepository $answerRepository, EntityManagerInterface $manager): Response
    {
        // Récupération de toutes les questions et réponses
        $questions = $questionRepository->findAll();
        $answers = $answerRepository->findAll();

        // Récupération de l'utilisateur connecté
        $user = $this->getUser();

        // Traitement des réponses soumises par l'utilisateur
        if ($request->getMethod() === 'POST') {
            // Vérifie si l'utilisateur a répondu à moins de 10 questions
            if (count($request->request->all()['answers']) < 10) {
                // Ajoute un message flash d'avertissement
                $this->addFlash('danger', 'Veuillez répondre à toutes les questions, merci!');
                // Redirige l'utilisateur vers la page du quiz PC
                return $this->redirectToRoute('quiz_pc');
            }
            // Récupère toutes les réponses envoyées par l'utilisateur
            $userAnswers = $request->request->all()['answers'];
            // Parcourt chaque réponse de l'utilisateur
            foreach ($userAnswers as $answer) {
                // Divise la réponse pour obtenir l'identifiant de la réponse
                $array = explode('-', $answer);
                // Récupère l'ID de la réponse à partir du tableau obtenu
                $answerId = substr($array[1], 11);
                // Récupère la réponse à partir de son ID
                $answer = $answerRepository->find($answerId);
                // Ajoute la réponse à l'utilisateur
                $user->addAnswer($answer);
                // Persiste les modifications de l'utilisateur dans la base de données
                $manager->persist($user);
                // Enregistre les changements dans la base de données
                $manager->flush();
                // Ajoute un message flash d'avertissement
                $this->addFlash('success', 'Merci d\'avoir répondu aux questions. Je vous contacterai dès que possible.');
                // Redirige l'utilisateur vers la page d'accueil
                return $this->redirectToRoute('app_home');
            }
        }
        // Rendu de la vue pour le quiz "Support"
        return $this->render('quiz/support.html.twig', [
            'questions' => $questions,
            'answers' => $answers,
            'user' => $user
        ]);
    }

    // Action pour gérer les réponses (pour les administrateurs)
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/answer', name: 'answer')]
    public function answer(AnswerRepository $repository, Request $request, EntityManagerInterface $manager, $id = null): Response
    {
        // Récupération de la liste des réponses depuis la base de données
        $answers = $repository->findAll();

        // Gestion de la modification d'une réponse existante ou de l'ajout d'une nouvelle réponse
        if ($id) {
            $answer = $repository->find($id); // Modification d'une réponse existante
        } else {
            $answer = new Answer(); // Création d'une nouvelle réponse
        }

        // Création du formulaire pour la réponse
        $form = $this->createForm(AnswerType::class, $answer);

        // Analyse de la requête HTTP
        $form->handleRequest($request);

        // Traitement du formulaire
        // Vérification si le formulaire a été soumis et est valide
        if ($form->isSubmitted() && $form->isValid()) {

            $answer = $form->getData(); // Récupération des données du formulaire

            $manager->persist($answer); // Persistation des données en base de données

            $manager->flush(); // Exécution de la transaction

            // Ajout d'un message flash pour indiquer le succès de l'opération
            $this->addFlash('success', 'La réponse a bien été ajoutée');

            // Redirection vers la gestion des réponses
            return $this->redirectToRoute('answer');
        }
        // Rendu de la vue avec les réponses et le formulaire
        return $this->render('quiz/answer.html.twig', [
            'form' => $form->createView(),
            'answers' => $answers
        ]);
    }

    #[Route('/results', name: 'quiz_results')]
    public function quiz_results(Request $request)
    {

        $userAnswers = $request->request->all();

        return $this->render('admin/quiz_results.html.twig', [
            'userAnswers' => $userAnswers
        ]);
    }
}
