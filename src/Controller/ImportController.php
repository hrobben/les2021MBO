<?php

namespace App\Controller;

use App\Service\FileUploader;
use Psr\Log\LoggerInterface;
use App\Entity\Speler;
use App\Repository\SpelerRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ImportController extends AbstractController
{
    #[Route('/import', name: 'import')]
    public function index(): Response
    {
        return $this->render('import/index.html.twig', [
            'controller_name' => 'ImportController',
            'filename' => null,
            'jsonstring' => null,
        ]);
    }

    #[Route('/doUpload', name: 'upload')]
    public function doUpload(Request $request, string $uploadDir, FileUploader $uploader, LoggerInterface $logger, SpelerRepository $spelerRepository)
    {
        $token = $request->get("token");

        if (!$this->isCsrfTokenValid('upload', $token)) {
            $logger->info("CSRF failure");

            return new Response("Operation not allowed", Response::HTTP_BAD_REQUEST,
                ['content-type' => 'text/plain']);
        }

        $file = $request->files->get('myfile');

        if (empty($file)) {
            return new Response("No file specified",
                Response::HTTP_UNPROCESSABLE_ENTITY, ['content-type' => 'text/plain']);
        }

        $filename = $file->getClientOriginalName();
        $uploader->upload($uploadDir, $file, $filename);


        $content = utf8_encode(file_get_contents($uploadDir.'/'.$filename));  // load with UTF8
        $xml = simplexml_load_string($content);

        $json = json_encode($xml);
        $data = json_decode($json,TRUE);

        // we gaan hier de data lezen en in de dbase plaatsen.
        $entityManager = $this->getDoctrine()->getManager();
        foreach($data as $aanmelding) {
            foreach($aanmelding as $speler) {
                $zoekSpeler = $spelerRepository->findBy(['achternaam'=>$speler['spelerachternaam']]);
                if ($zoekSpeler) {
                    foreach ($zoekSpeler as $item) {
/*                        if ($item->setVoornaam() <> $speler['spelervoornaam']) {
                            $nieuweSpeler = new Speler();
                            $nieuweSpeler->setAchternaam($speler['spelerachternaam']);
                            $nieuweSpeler->setVoornaam($speler['spelervoornaam']);
                            $nieuweSpeler->setSchool($speler['schoolnaam']);
                            if ($speler['spelertussenvoegsels']) {
                                $nieuweSpeler->setTussenvoegsel($speler['spelertussenvoegsels']);
                            }
                            $entityManager->persist( $nieuweSpeler);
                        }*/
                    }
                } else {
                    $nieuweSpeler = new Speler();
                    $nieuweSpeler->setAchternaam($speler['spelerachternaam']);
                    $nieuweSpeler->setVoornaam($speler['spelervoornaam']);
                    $nieuweSpeler->setSchool($speler['schoolnaam']);
                    if ($speler['spelertussenvoegsels']) {
                        $nieuweSpeler->setTussenvoegsel($speler['spelertussenvoegsels']);
                    }
                    $entityManager->persist( $nieuweSpeler);
                }
            }
        }
        $entityManager->flush();

        return $this->render('import/index.html.twig', [
            'filename' => $filename,
            'jsonstring' => $data,
            'upload_dir' => $uploadDir,
        ]);
    }

    #[Route('/game', name: 'game')]
    public function game(SpelerRepository $spelerRepository)
    {
        $spelers = $spelerRepository->findAll();
        $spelerArray = [];
        foreach ($spelers as $speler) {
            array_push($spelerArray, $speler->getId());
        }
        shuffle($spelerArray);
        dump($spelerArray);

        return $this->render('import/game.html.twig',[
            'controller' => 'import',
            'spelersArray' => $spelerArray,
            'spelers' => $spelers,
        ]);
    }
}
