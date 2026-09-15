<?php

declare(strict_types=1);
namespace App\Controller;

use Facebook\Facebook;
use App\Form\Type\TaskType;
use Symfony\Component\Mime\Address;


use App\Entity\ScoreToSend;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController; 


use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;


class SocialMediaController extends AbstractController
{

    #[Route('/social/{id}', name: 'social_success')]
    public function social_show(EntityManagerInterface $entityManager, int $id): Response
    {
        $product = $entityManager->getRepository(ScoreToSend::class)->find($id);

        if (!$product) {
            throw $this->createNotFoundException(
                'No product found for id '.$id
            );
        }

        //return new Response('Check out this great product: '.$product->getTimeSignature());

        // or render a template
        // in the template, print things with {{ product.name }}
        return $this->render('task/show.html.twig', ['product' => $product]);
    }
    #[Route('/postsomething', name: 'post_link_fb')]
    public function fb_post(): Response
    {
	$fb = new Facebook([
          'app_id' => $_ENV['FACEBOOK_APP_ID'],
          'app_secret' => $_ENV['FACEBOOK_APP_ID'],
          'default_graph_version' => 'v2.10',
          ]);
        $linkData = [
          'link' => 'http://www.example.com',
          'message' => 'User provided message',
          ];
        
        try {
          // Returns a `Facebook\FacebookResponse` object
          $response = $fb->post('/me/feed', $linkData, $_ENV['GRAPHAPIACCESSTOKEN']);
        } catch(Facebook\Exceptions\FacebookResponseException $e) {
          echo 'Graph returned an error: ' . $e->getMessage();
          exit;
        } catch(Facebook\Exceptions\FacebookSDKException $e) {
          echo 'Facebook SDK returned an error: ' . $e->getMessage();
          exit;
        }
        
        $graphNode = $response->getGraphNode();
        
        echo 'Posted with id: ' . $graphNode['id'];
        return $this->render('task/postlink.html.twig', ['graphnodeid' => $graphNode['id']]);
	
    }
    #[Route('/callback.php', name: 'mycallback')]
    public function fb_callback(): Response
    {
	$fb = new Facebook\Facebook([
          'app_id' => $_ENV['FACEBOOK_APP_ID'],
          'app_secret' => $_ENV['FACEBOOK_APP_ID'],
          'default_graph_version' => 'v2.10',
          ]);
        
        $helper = $fb->getRedirectLoginHelper();
        
        try {
          $accessToken = $helper->getAccessToken();
        } catch(Facebook\Exceptions\FacebookResponseException $e) {
          // When Graph returns an error
          echo 'Graph returned an error: ' . $e->getMessage();
          exit;
        } catch(Facebook\Exceptions\FacebookSDKException $e) {
          // When validation fails or other local issues
          echo 'Facebook SDK returned an error: ' . $e->getMessage();
          exit;
        }
        
        if (! isset($accessToken)) {
          if ($helper->getError()) {
            header('HTTP/1.0 401 Unauthorized');
            echo "Error: " . $helper->getError() . "\n";
            echo "Error Code: " . $helper->getErrorCode() . "\n";
            echo "Error Reason: " . $helper->getErrorReason() . "\n";
            echo "Error Description: " . $helper->getErrorDescription() . "\n";
          } else {
            header('HTTP/1.0 400 Bad Request');
            echo 'Bad request';
          }
          exit;
        }
        
        // Logged in
        echo '<h3>Access Token</h3>';
        var_dump($accessToken->getValue());
        
        // The OAuth 2.0 client handler helps us manage access tokens
        $oAuth2Client = $fb->getOAuth2Client();
        
        // Get the access token metadata from /debug_token
        $tokenMetadata = $oAuth2Client->debugToken($accessToken);
        echo '<h3>Metadata</h3>';
        var_dump($tokenMetadata);
        
        // Validation (these will throw FacebookSDKException's when they fail)
        $tokenMetadata->validateAppId($config['app_id']);
        // If you know the user ID this access token belongs to, you can validate it here
        //$tokenMetadata->validateUserId('123');
        $tokenMetadata->validateExpiration();
        
        if (! $accessToken->isLongLived()) {
          // Exchanges a short-lived access token for a long-lived one
          try {
            $accessToken = $oAuth2Client->getLongLivedAccessToken($accessToken);
          } catch (Facebook\Exceptions\FacebookSDKException $e) {
            echo "<p>Error getting long-lived access token: " . $e->getMessage() . "</p>\n\n";
            exit;
          }
        
          echo '<h3>Long-lived</h3>';
          var_dump($accessToken->getValue());
        }
        
        $_SESSION['fb_access_token'] = (string) $accessToken;
        
        // User is logged in with a long-lived access token.
        // You can redirect them to a members-only page.
        //header('Location: https://example.com/members.php');
        return $this->redirectToRoute('task/facebookmember.html.twig');
    }



}
