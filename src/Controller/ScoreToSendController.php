<?php
// src/Controller/ScoreToSendController.php
namespace App\Controller;

use App\Entity\ScoreToSend;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
// ...

class ScoreToSendController extends AbstractController
{
    #[Route('/ScoreToSend', name: 'create_ScoreToSend')]
    public function createScoreToSend(ValidatorInterface $validator): Response
    {
        $ScoreToSend = new ScoreToSend();

        // ... update the ScoreToSend data somehow (e.g. with a form) ...

        $errors = $validator->validate($ScoreToSend);
        if (count($errors) > 0) {
            return new Response((string) $errors, 400);
        }

        // ...
    }
}
