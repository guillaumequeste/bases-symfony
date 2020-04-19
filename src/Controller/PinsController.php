<?php

namespace App\Controller;

use App\Entity\Pin;
use App\Repository\PinRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PinsController extends AbstractController
{
    /**
     * @Route("/", name="app_home", methods={"GET"})
     */
    public function index(EntityManagerInterface $em): Response
    {   
        $repo = $em->getRepository(Pin::class);

        $pins = $repo->findAll();
        
        return $this->render('pins/index.html.twig', ['pins' => $pins]);
        // return $this->render('pins/index.html.twig', compact('pins'));   équivalent
    }

    // On peut mettre :
    // public function index(PinRepository $repo): Response
    // {
    //      return $this->render('pins/index.html.twig', ['pins' => $repo->findAll()]);
    // }



    /**
     * @Route("/pins/{id<[0-9]+>}", name="app_pins_show")
     */
    public function show(Pin $pin): Response
    {
        return $this->render('pins/show.html.twig', compact('pin'));
    }



    /**
     * @Route("/pins/create", name="app_pins_create", methods={"GET", "POST"})
     */
    public function create(Request $request, EntityManagerInterface $em)
    {   
        $pin = new Pin;
        // $pin->setTitle('Cool');   //(met une valeur 'Cool' par défaut dans le champ)
        // $pin->setDescription('Pas cool');   //(met une valeur 'Pas cool' par défaut dans le champ)

        $form = $this->createFormBuilder($pin)
            ->add('title', null, [
                // 'required' => false,
                'attr' => ['autofocus' => true
            ]])
            // ->add('title', TextType::class, [
                // 'required' => false,
                // 'attr' => ['autofocus' => true
            // ]])
            // use Symfony\Component\Form\Extension\Core\Type\TextType;

            ->add('description', null, ['attr' => ['rows' => 10, 'cols' => 50]])
            // ->add('description', TextareaType::class, ['attr' => ['rows' => 10, 'cols' => 50]])
            // use Symfony\Component\Form\Extension\Core\Type\TextareaType;

            // ->add('submit', SubmitType::class, ['label' => 'Create Pin'])
            // use Symfony\Component\Form\Extension\Core\Type\SubmitType;

            ->getForm()
        ;

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($pin);
            $em->flush();

            return $this->redirectToRoute('app_pins_show', ['id' => $pin->getId()]);
            // return $this->redirectToRoute('app_home');
        }
        
        return $this->render('pins/create.html.twig', [
            'monFormulaire' => $form->createView()
        ]);
    }
}

