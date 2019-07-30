<?php

namespace App\Controller;

use App\Entity\Partenaire;
use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class PartenaireController extends AbstractController
{
  
/**
* @Route ("/ajoutpar",name="ajoutpar" ,methods={"POST"})
* @Route("/modifpart/{id}", name="modif_partenaire",methods={"PUT"})
*/
public function Add(Request $request, EntityManagerInterface $entityManager,SerializerInterface $serializer, ValidatorInterface $validator ){
    $valeurs = json_decode($request->getContent());
    if(isset($valeurs->nomEntreprise,$valeurs->ninea,$valeurs->adresse,$valeurs->raisonSocilale,$valeurs->email,$valeurs->numeroCompte)){
        $partenaire= new Partenaire();
        $partenaire->setNomEntreprise($valeurs->nomEntreprise);
        $partenaire->setNinea($valeurs->ninea);
        $partenaire->setAdresse($valeurs->adresse);
        $partenaire->setRaisonSocilale($valeurs->raisonSocilale);
        $partenaire->setEmail($valeurs->email);
        $partenaire->setNumeroCompte($valeurs->numeroCompte);
        $user = $entityManager->getRepository(Utilisateur::class)->find($valeurs->utilisateur);
        $partenaire->setUtilisateur($user);
        $partenaire->setMontantCompte($valeurs->montantCompte);
        $partenaire->setStatut($valeurs->statut);
        $entityManager->persist($partenaire);
        $entityManager->flush();
        
        $errors = $validator->validate($partenaire);


        if(count($errors))
        {
            $errors = $serializer->serialize($errors,'json');  
            $datas= [
                
                'status' => 500,
                'message' => 'erreur '
            ];
            return new JsonResponse($datas, 500);
            
           
        }
        $datas= [
            
            'status' => 201,
            'message' => 'Partenaire enregistré'
            ];
            return new JsonResponse($datas, 201);
       
        }
        
    }

            /**
             * @Route("/show/{id}", name="author_show")
             */
            public function findPartenaire(Partenaire $partenaire)
            {
                $data =  $this->get('serializer')->serialize($partenaire, 'json');

                $response = new Response($data);
                $response->headers->set('Content-Type', 'application/json');

                return $response;
            }

}