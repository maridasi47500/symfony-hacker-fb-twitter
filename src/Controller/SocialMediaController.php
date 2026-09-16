<?php

declare(strict_types=1);
namespace App\Controller;
use App\Entity\FbPagePost;


use Facebook\Facebook;
use Symfony\Contracts\HttpClient\HttpClientInterface;


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

    #[Route('/facebook_media/delete/{id}', name: 'fb_post_delete')]
    public function fb_post_delete(Request $request, EntityManagerInterface $entityManager, int $id): Response
    {
        $product = $entityManager->getRepository(FbPagePost::class)->find($id);
        $token = $_ENV["MYFAVFBPAGEACCESSTOKEN"];
        $data = ["access_token" => $token];
        $response = $this->client->request('DELETE', 'https://graph.facebook.com/v26.0/' . $product->getPostId(), [
            'headers' => [
                'Content-Type' => 'application/json',
                //'Authorization' => 'Bearer ' . $token,
            ],
	    'json' => $data,
        ]);

        $y=$response->getContent();

        if (!$product) {
            throw $this->createNotFoundException(
                'No product found for id '.$id
            );
        }

        //return new Response('Check out this great product: '.$product->getTimeSignature());

        // or render a template
        // in the template, print things with {{ product.name }}
        $entityManager->remove($product);
        $entityManager->flush();
	
        return $this->render('task/seefbpostdeleted.html.twig', ['product' => $product]);
    }
    #[Route('/facebook_media/{id}', name: 'fb_post_success')]
    public function fb_post_show(EntityManagerInterface $entityManager, int $id): Response
    {
        $product = $entityManager->getRepository(FbPagePost::class)->find($id);

        if (!$product) {
            throw $this->createNotFoundException(
                'No product found for id '.$id
            );
        }

        //return new Response('Check out this great product: '.$product->getTimeSignature());

        // or render a template
        // in the template, print things with {{ product.name }}
        return $this->render('task/seefbpost.html.twig', ['product' => $product]);
    }
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

    public function __construct(private HttpClientInterface $client) {}
    #[Route('/fbpostupdate', name: 'fb_post_update')]
    public function fb_post_update(EntityManagerInterface $entityManager, Request $request): Response
    {

        $texttopost = $request->query->get('texttopost');

	
        $id = $request->query->get('myid');
        $product = $entityManager->getRepository(FbPagePost::class)->find($id);
	if (empty ($texttopost) || strlen($texttopost) == 0){
		$texttopost=$product->getContent();
	}
        $product->setContent($texttopost);
        $entityManager->flush();
        $token = $_ENV["MYFAVFBPAGEACCESSTOKEN"];
        $data = ["access_token" => $token, "message" => $texttopost];
        $response = $this->client->request('POST', 'https://graph.facebook.com/v26.0/' . $product->getPostId(), [
        //$response = $this->client->request('POST', 'https://graph.facebook.com/v26.0/me/feed', [
            'headers' => [
                'Content-Type' => 'application/json',
                //'Authorization' => 'Bearer ' . $token,
            ],
	    'json' => $data,
        ]);

        $y=$response->getContent();
	
	echo $y;

        //return $this->render('task/fblogin.html.twig', ['product' => "qdjfh", "y" => $y]);
	// check if the route works or
        return $this->redirectToRoute('fb_post_success', ["id" => $id]);

    }

    #[Route('/fbpostcontent', name: 'fb_post_content')]
    public function fb_post_content(EntityManagerInterface $entityManager, Request $request): Response
    {
        $texttopost = $request->query->get('texttopost');
	if (empty ($texttopost) || strlen($texttopost) == 0){
		$texttopost="et voilà ! j'ai posté quelque chose, comment vas tu?";
	}
        $token = $_ENV["MYFAVFBPAGEACCESSTOKEN"];
        $data = ["access_token" => $token, "message" => $texttopost];
        $response = $this->client->request('POST', 'https://graph.facebook.com/v26.0/' . $_ENV['MYFAVFBPAGEID'] . '/feed', [
        //$response = $this->client->request('POST', 'https://graph.facebook.com/v26.0/me/feed', [
            'headers' => [
                'Content-Type' => 'application/json',
                //'Authorization' => 'Bearer ' . $token,
            ],
	    'json' => $data,
        ]);

        $y=$response->getContent();
	
	echo $y;
	$postid=json_decode($y, true)["id"];
	$mypost=new FbPagePost();
	$mypost->setContent($texttopost);
	$mypost->setPostId($postid);
	$entityManager->persist($mypost);
	$entityManager->flush();
	$myid=$mypost->getId();

        //return $this->render('task/fblogin.html.twig', ['product' => "qdjfh", "y" => $y]);
	// check if the route works or
        return $this->redirectToRoute('fb_post_success', ["id" => $myid]);

    }
    #[Route('/getfacebookpageid', name: 'fb_page_id')]
    public function fb_page_id(): Response
    {
        $token = $_ENV["GRAPHAPIACCESSTOKEN"];
        $token1 = $_ENV["GRAPHAPIACCESSTOKENAPP"];
        $token2 = $_ENV["GRAPHAPIACCESSTOKENPAGE"];
        $token3 = $_ENV["GRAPHAPIACCESSTOKENUSER"];
	$user_id = $_ENV['FB_USER_ID'];
	$token4 = $_ENV['APP_SECRET'];

	//$user_id = $_ENV['FACEBOOK_APP_ID'];

	//$response = $this->client->request('GET', 'https://graph.facebook.com/v26.0/' . $user_id . '/accounts?access_token=' . $token3, [
	//$response = $this->client->request('GET', 'https://graph.facebook.com/v26.0/' . $user_id . '/accounts', [
	$response = $this->client->request('GET', 'https://graph.facebook.com/v26.0/me/accounts', [
		//access_token=' . $token3, [
            'headers' => [
                'Authorization' => 'Bearer ' . $token3,
            ],
        ]);

        $y=$response->getContent();
	
	echo $y;
        return $this->render('task/pageid.html.twig', [ "pageidstring" => $y, "pageaccesstoken" => json_decode($y, true)["data"][0]["access_token"], "pagename" => json_decode($y, true)["data"][0]["name"], "pageid" => json_decode($y, true)["data"][0]["id"]]);
    }
    #[Route('/fblogin', name: 'fb_simple_login')]
    public function fb_login(): Response
    {
        $token = $_ENV["GRAPHAPIACCESSTOKEN"];

        $response = $this->client->request('GET', 'https://graph.facebook.com/v26.0/me?fields=id,name', [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
            ],
        ]);

        $y=$response->getContent();
	
	echo $y;
        return $this->render('task/fblogin.html.twig', ['product' => "qdjfh", "y" => $y]);
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
