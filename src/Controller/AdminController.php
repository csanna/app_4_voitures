<?php

namespace App\Controller;

use App\Entity\Voiture;
use App\Form\VoitureType;
use App\Entity\RechercheVoiture;
use App\Form\RechercheVoitureType;
use App\Repository\VoitureRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminController extends AbstractController
{
    /**
     * @Route("/admin", name="admin")
     */
    public function index(VoitureRepository $repo, PaginatorInterface $paginatorInterface, Request $request)
    {
        $rechercheVoiture = new RechercheVoiture();

        $form = $this->createForm(RechercheVoitureType::class, $rechercheVoiture);
        $form->handleRequest($request);
        
        $voitures = $paginatorInterface->paginate(
            $repo->findAllWithPagination($rechercheVoiture),
            $request->query->getInt('page', 1), /*page number*/
            6 /*limit per page*/
        );
        return $this->render('voiture/voitures.html.twig',[
            "voitures" => $voitures,
            "form" => $form->createView(),
            "admin" => true
        ]);
    }

    /**
     * @Route("/admin/creation", name="creationVoiture")
     * @Route("/admin/{id}", name="modifVoiture", methods="GET|POST")
     */
    public function modification(Voiture $voiture = null, Request $request){
        if(!$voiture){
            $voiture = new Voiture();
        }

        $objectManager = $this->getDoctrine()->getManager();
        
        $form = $this->createForm(VoitureType::class,$voiture);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $objectManager->persist($voiture);
            $objectManager->flush();
            $this->addFlash('success', "L'action a été effectuée.");
            return $this->redirectToRoute("admin");
        }

        return $this->render('admin/modification.html.twig',[
            "voiture" => $voiture,
            "form" => $form->createView()
        ]);
    }
    
     /**
     * @Route("/admin/{id}", name="supVoiture", methods="SUP")
     */
    public function suppression(Voiture $voiture = null, Request $request){
        $objectManager = $this->getDoctrine()->getManager();

        if($this->isCsrfTokenValid("SUP".$voiture->getId(), $request->get("_token"))){
            $objectManager->remove($voiture);
            $objectManager->flush();
            $this->addFlash('success', "L'action a été effectuée.");
            return $this->redirectToRoute("admin");
        }
    }    
}
