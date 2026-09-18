<?php

declare(strict_types=1);
namespace App\Controller;
use App\Entity\FbPagePost;
use App\Entity\IgMedia;


use Symfony\Contracts\HttpClient\HttpClientInterface;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Polyfill\Intl\Icu\Exception\RuntimeException;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;



use App\Form\Type\TaskType;
use App\Form\Type\IgMediaType;
use Symfony\Component\Mime\Address;
use Symfony\Component\String\Slugger\SluggerInterface;


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


class InstagramController extends AbstractController
{
    public function __construct(private HttpClientInterface $client) {}

    #[Route('/ig_upload_vid', name: 'app_ig_upload_vid')]
    public function upload(
        Request $request,
	EntityManagerInterface $entityManager,
        SluggerInterface $slugger,
        #[Autowire('%kernel.project_dir%\public\uploads\brochures')] string $brochuresDirectory
    ): Response
    {
        $product = new IgMedia();
        $form = $this->createForm(IgMediaType::class, $product);
        $form->handleRequest($request);
	$result="ok"  . strval($form->isSubmitted()) . "FORM ERRORS:" . json_encode($form->getErrors());// . strval($form->errors());

        if ($form->isSubmitted()) {// && $form->isValid()) {
            /** @var UploadedFile $brochureFile */
            $brochureFile = $form->get('media_name')->getData();

            // this condition is needed because the 'brochure' field is not required
            // so the PDF file must be processed only when a file is uploaded
            if ($brochureFile) {
		    echo "yes";
                $originalFilename = pathinfo($brochureFile->getClientOriginalName(), PATHINFO_FILENAME);
		echo "yes1";
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugger->slug($originalFilename);
		echo "yes2";
                $newFilename = $safeFilename.'-'.uniqid().'.'.$brochureFile->guessExtension();
		
		echo "yes3";

                // Move the file to the directory where brochures are stored
		$sizeobject=strval($brochureFile->getSize());
                try {
                    $brochureFile->move($brochuresDirectory, $newFilename);
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
			echo "oops";
			echo $e->getMessage();
                }

                // updates the 'brochureFilename' property to store the PDF file name
                // instead of its contents
                $product->setMediaName($newFilename);
                $token = $_ENV["IG_API_TOKEN"];
                $token1 = $_ENV["ID_APP_IG"];
                $token2 = $_ENV["IG_ACCOUNT_ID"];
		echo "yes4";
		echo $sizeobject;

                $headerparams= [
                        'Authorization' => 'OAuth ' . $token,
                        'offset' => '0',
                        'file_size' => $sizeobject,
                    ];

                //$ch = curl_init();
                //curl_setopt($ch, CURLOPT_URL,            'https://rupload.facebook.com/ig-api-upload/v26.0/' . $product->getContainerId() );
                //curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1 );
                //curl_setopt($ch, CURLOPT_POST,           1 );
                ////$result .= str_replace("%5C","\\",__DIR__) . '\public\uploads\brochures/' . $newFilename; 
                //$result .= $brochuresDirectory . '\\' . $newFilename; 
                //curl_setopt($ch, CURLOPT_POSTFIELDS,     file_get_contents($brochuresDirectory . '\\' . $newFilename) ); 
                //curl_setopt($ch, CURLOPT_HTTPHEADER,     $headerparams); 
                //


                //if(curl_errno($ch) !== CURLE_OK){
                //    throw new RuntimeException(curl_error($ch) . $result);
                //}
                echo 'https://rupload.facebook.com/ig-api-upload/v26.0/' . $product->getContainerId();
                $response = $this->client->request('POST', 'https://rupload.facebook.com/ig-api-upload/v26.0/' . $product->getContainerId(), [
                    'headers' => $headerparams,
		    //'body' => file_get_contents($brochuresDirectory . '\\' . $newFilename),
		    'body' => fopen($brochuresDirectory . '\\' . $newFilename, 'r'),
		]);
		echo ($brochuresDirectory . '\\' . $newFilename);
		echo "yes5";


                $y=$response->getContent();
		echo "yes6";
		

		
            }

	}    

	$entityManager->persist($product);
	$entityManager->flush();
	$id=$product->getId();
        // ... persist the $product variable or any other work

        return $this->redirectToRoute('app_ig_media_list', ["id" => $id, "result" => $y]);
    }

    #[Route('/ig_media/see/{id}', name: 'app_ig_media_list')]
    public function ig_pic_show(EntityManagerInterface $entityManager, int $id): Response
    {
        $product = $entityManager->getRepository(IgMedia::class)->find($id);

        if (!$product) {
            throw $this->createNotFoundException(
                'No product found for id '.$id
            );
        }

        //return new Response('Check out this great product: '.$product->getTimeSignature());

        // or render a template
        // in the template, print things with {{ product.name }}
        return $this->render('ig/seeigpost.html.twig', ['product_container_id' => $product->getContainerId(), 'product_media_name' => $product->getMediaName()]);
    }

    #[Route('/ig_media/create', name: 'ig_create_container')]
    public function create_container(Request $request): Response
    {
        $token = $_ENV["IG_API_TOKEN"];
        $token1 = $_ENV["ID_APP_IG"];
        $token2 = $_ENV["IG_ACCOUNT_ID"];

	$url="https://assets.simpleviewinc.com/simpleview/image/upload/c_fill,f_jpg,g_xy_center,h_920,q_65,w_639,x_3225,y_2956/v1/clients/milwaukee/VM_BronzeFonz_4_c543fb20-68d6-4804-9543-1c6eb505e440.jpg";
        $data = ["image_url" => $url];
        $response = $this->client->request('POST', 'https://graph.instagram.com/v26.0/' . $token2 . "/media", [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $token,
            ],
	    'json' => $data,
        ]);

        $y=$response->getContent();
	$container_id=json_decode($y, true)["id"];
        $product = new IgMedia();
	$product->setContainerId($container_id);
        $form = $this->createForm(IgMediaType::class, $product);


        //return new Response('Check out this great product: '.$product->getTimeSignature());

        // or render a template
        // in the template, print things with {{ product.name }}
	
        return $this->render('ig/createcontainer.html.twig', ['container_id' => $container_id, 'product' => $y, "form" => $form]);
    }
}
