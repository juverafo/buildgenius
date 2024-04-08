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
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/quiz')]
class QuizController extends AbstractController
{
    #[Route('/', name: 'admin_quiz')]
    #[Route('/update/{id}', name: 'admin_quiz_update')]
    public function quiz(QuizRepository $repository, Request $request, EntityManagerInterface $manager, $id = null): Response
    {
        // AFFICHAGE DES QUIZES
        // récupérer la liste des quizes depuis la base de données
        $quizes = $repository->findAll();

        // MODIFICATION D'UN QUIZ EXISTANTE OU AJOUT D'UN NOUVEAU QUIZ
        if ($id) {
            // Si un identifiant est fourni, cela signifie qu'on veut modifier un quiz existante
            $quiz = $repository->find($id);
        } else {
            // Sinon, on crée une nouvelle instance de Quiz
            $quiz = new Quiz();
        }

        // GÉNÉRATION DU FORMULAIRE
        // Création du formulaire à partir de la classe QuizType
        $form = $this->createForm(QuizType::class, $quiz);

        // GESTION DE LA REQUÊTE
        // Analyse de la requête HTTP
        $form->handleRequest($request);

        // TRAITEMENT DU FORMULAIRE
        // Vérification si le formulaire a été soumis et est valide
        if ($form->isSubmitted() && $form->isValid()) {
            // Récupération des données du formulaire
            $quiz = $form->getData();

            // Persistation des données en base de données
            $manager->persist($quiz);

            // Exécution de la transaction
            $manager->flush();

            // Ajout d'un message flash pour indiquer que le quiz a été ajoutée avec succès
            $this->addFlash('success', 'Le quiz a bien été ajouté');

            // Redirection vers la route admin_quiz
            return $this->redirectToRoute('admin_quiz');
        }

        // RENDU DE LA VUE
        return $this->render('quiz/index.html.twig', [
            'quizes' => $quizes,
            'form' => $form->createView()
        ]);
    }

    // SUPPRESSION DES QUIZ
    #[Route('/delete/{id}', name: 'admin_quiz_delete')]
    public function delete(QuizRepository $repository, EntityManagerInterface $manager, $id = null): Response
    {
        if ($id) {
            // Récupération de la catégorie à supprimer
            $quiz = $repository->find($id);
        }

        // Suppression de la catégorie
        $manager->remove($quiz);
        $manager->flush();

        // Redirection vers la page d'administration des quiz
        return $this->redirectToRoute('admin_quiz');
    }
    #[Route('/question', name: 'question')]
    public function question(QuestionRepository $repository, Request $request, EntityManagerInterface $manager, $id = null): Response
    {
        // AFFICHAGE DES QUESTIONS
        // récupérer la liste des questions depuis la base de données
        $questions = $repository->findAll();

        // MODIFICATION D'UNE QUESTION EXISTANT OU AJOUT D'UNE NOUVELLE QUESTION
        if ($id) {
            // Si un identifiant est fourni, cela signifie qu'on veut modifier une question existante
            $question = $repository->find($id);
        } else {
            // Sinon, on crée une nouvelle instance de Question
            $question = new Question();
        }

        // GÉNÉRATION DU FORMULAIRE
        // Création du formulaire à partir de la classe QuestionType
        $form = $this->createForm(QuestionType::class, $question);

        // GESTION DE LA REQUÊTE
        // Analyse de la requête HTTP
        $form->handleRequest($request);

        // TRAITEMENT DU FORMULAIRE
        // Vérification si le formulaire a été soumis et est valide
        if ($form->isSubmitted() && $form->isValid()) {
            // Récupération des données du formulaire
            $question = $form->getData();

            // Persistation des données en base de données
            $manager->persist($question);

            // Exécution de la transaction
            $manager->flush();

            // Ajout d'un message flash pour indiquer que la question a été ajoutée avec succès
            $this->addFlash('success', 'La question a bien été ajoutée');

            // Redirection vers la route admin_quiz
            return $this->redirectToRoute('question');
        }
        return $this->render('quiz/question.html.twig', [
            'form' => $form->createView(),
            'questions' => $questions
        ]);
    }
    #[Route('/pc', name: 'quiz_pc')]
    public function pc(Request $request, QuestionRepository $questionRepository, AnswerRepository $answerRepository, Security $security): Response
    {
        $questions = $questionRepository->findAll();
        $answers = $answerRepository->findAll();

        // Récupération de l'utilisateur connecté
        $user = $security->getUser();

        // Récupération de l'ID de l'utilisateur
        $userId = $user ? $user->getId() : null;

        // dd($userId);

        if ($request->getMethod() === 'POST') {
            // Récupération des réponses envoyées par l'utilisateur
            $userAnswers = $request->request->all()['answers'];
            dd($userAnswers);
        }

        return $this->render('quiz/pc.html.twig', [
            'questions' => $questions,
            'answers' => $answers,
            'userId' => $userId
        ]);
    }
    #[Route('/support', name: 'quiz_support')]
    public function support(Request $request, QuestionRepository $repository, AnswerRepository $answerrepo, Security $security): Response
    {
        $questions = $repository->findAll();
        $answers = $answerrepo->findAll();

        // Récupération de l'utilisateur connecté
        $user = $security->getUser();

        // Récupération de l'ID de l'utilisateur
        $userId = $user ? $user->getId() : null;

        // dd($userId);

        if ($request->getMethod() === 'POST') {
            // Récupération des réponses envoyées par l'utilisateur
            $userAnswers = $request->request->all()['answers'];
            dd($userAnswers);
        }

        return $this->render('quiz/support.html.twig', [
            'questions' => $questions,
            'answers' => $answers,
            'userId' => $userId
        ]);
    }
    #[Route('/answer', name: 'answer')]
    public function answer(AnswerRepository $repository, Request $request, EntityManagerInterface $manager, $id = null): Response
    {
        // AFFICHAGE DES REPONSES
        // récupérer la liste des réponses depuis la base de données
        $answers = $repository->findAll();

        // MODIFICATION D'UNE REPONSE EXISTANT OU AJOUT D'UNE NOUVELLE REPONSE
        if ($id) {
            // Si un identifiant est fourni, cela signifie qu'on veut modifier une reponse existante
            $answer = $repository->find($id);
        } else {
            // Sinon, on crée une nouvelle instance de Question
            $answer = new Answer();
        }

        // GÉNÉRATION DU FORMULAIRE
        // Création du formulaire à partir de la classe QuestionType
        $form = $this->createForm(AnswerType::class, $answer);

        // GESTION DE LA REQUÊTE
        // Analyse de la requête HTTP
        $form->handleRequest($request);

        // TRAITEMENT DU FORMULAIRE
        // Vérification si le formulaire a été soumis et est valide
        if ($form->isSubmitted() && $form->isValid()) {
            // Récupération des données du formulaire
            $answer = $form->getData();

            // Persistation des données en base de données
            $manager->persist($answer);

            // Exécution de la transaction
            $manager->flush();

            // Ajout d'un message flash pour indiquer que la question a été ajoutée avec succès
            $this->addFlash('success', 'La réponse a bien été ajoutée');

            // Redirection vers la route admin_quiz
            return $this->redirectToRoute('answer');
        }
        return $this->render('quiz/answer.html.twig', [
            'form' => $form->createView(),
            'answers' => $answers
        ]);
    }
}
