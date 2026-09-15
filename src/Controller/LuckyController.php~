<?php
// src/Controller/LuckyController.php
namespace App\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Facebook\Facebook;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class LuckyController extends AbstractController
{
    #[Route('/')]
    public function number(): Response
    {
        $number = random_int(0, 100);
	try{
	$fb = new Facebook([
          'app_id' => $_ENV["FACEBOOK_APP_ID"],
          'app_secret' => $_ENV['FACEBOOK_APP_SECRET'],
          'default_graph_version' => 'v2.10',
          ]);
        
        $helper = $fb->getRedirectLoginHelper();
        
        $permissions = ['email']; // Optional permissions
        $loginUrl = $helper->getLoginUrl('https://localhost.com:8000/fb-callback.php', $permissions);
	}catch (Exception $e){
        $loginUrl = '/fb-callback.php';
	};
        
	/*echo '<a href="' . $loginUrl . '">Log in with Facebook!</a>';*/


        /*return new Response(
            '<html><body>Lucky number: '.$number.'<div class=\'actions\'><a href=\'/task\'>ajouter une partition de musique à envoyer</a><a href="/postsomething">post a link on facebook</a><a href="' . $loginUrl . '">Log in with Facebook!</a></div></body></html>'
	);*/
        return $this->render('task/lucky.html.twig', ['number' => $number, 'loginurl' => $loginUrl]);
	
    }
}
