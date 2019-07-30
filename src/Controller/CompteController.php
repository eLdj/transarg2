<?php

namespace App\Controller;


use App\Entity\Compte;
use App\Form\CompteType;
use App\Entity\Partenaire;
use App\Repository\CompteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("api/compte")
 */
class CompteController extends AbstractController
{
    
    /**
     * @Route("/new", name="compte_new", methods={"GET","POST"})
     * @IsGranted("ROLE_CAISSIER")
     */
    public function new(Request $request, EntityManagerInterface $entityManager,SerializerInterface $serializer, ValidatorInterface $validator)
    {
        $valeurs = json_decode($request->getContent());
       
        if(isset($valeurs->montant))
        {
           $compte = new Compte();
           $compte->setMontant($valeurs->montant);
           $compte->setDateDepot(new \DateTime());             
           $partenaire = $this->getDoctrine()->getRepository(Partenaire::class)->find($valeurs->partenaire);
           $compte->setPartenaire($partenaire);

           $errors = $validator->validate($compte);

           if(count($errors))
           {
               $errors = $serializer->serialize($errors,'json');

               return new Response($errors, 500, [
                   'Content-Type' => 'application/json'
               ]);
           }
                    
           $entityManager->persist($compte);
           $entityManager->flush();

           $data = [
            'status' => 201,
            'message' => 'L\'utilisateur a été créé'
        ];
        return new JsonResponse($data, 201);
        }  
        $datas= [
                
            'status' => 500,
            'message' => 'erreur '
        ];
        return new JsonResponse($datas, 500);
    }


      /**
     * @Route("/modifcompte/{id}", name="modif_compte",methods={"PUT"})
     */
    public function modif(Request $request, SerializerInterface $serializer, Compte $compte, ValidatorInterface $validateur, EntityManagerInterface $entityManager)
    {
        $partenaireModif=$entityManager->getRepository(Compte::class)->find($compte->getId());

        $donnee=json_decode($request->getContent());

        foreach($donnee as $cle=>$valeur)
        {
            if($cle && !empty($valeur))
            {
                $nom = ucfirst($cle);
                $setter = 'set'.$nom;
                $partenaireModif->$setter($valeur);
            }
        }

        $errors = $validateur->validate($partenaireModif);
        
        if(count($errors))
        {
            $errors = $serializer->serialize($errors, 'json');
            return new Response($errors, 500, [
                'Content-Type' => 'application/json'
            ]);
        }

        $entityManager->flush();
        $data = [
            'statuse' => 200,
            'messages' => 'Les données partenaire ont bien étés mises à jour'
        ];
        return new JsonResponse($data);
    }


}
