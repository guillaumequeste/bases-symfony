1) Installer Symfony
2) Créer un projet
3) Lancer le serveur
4) Proxy en local
5) Console, composer
6) Création controller
7) Création entity
8) Création de la base de données
9) Debug

(Création de Pin manuellement)
10) Injection de dépendances (ajouter des pins dans la base ded données)
11) Create Pin
12) Résumé

13) Afficher les pins sur la page d'accueil
14) Noms de route
15) Création de formulaire
16) Twig : tags, filtres, fonctions, tests, opérateurs

17) Upgrade PHP
18) Upgrade Symfony

19) Détail d'un pin
20) Générer une erreur 404
21) Code plus concis


1) - curl -sS https://get.symfony.com/cli/installer | bash     (https://symfony.com/download)
    - echo $0   (pour voir ce qu'on utilise : bash ou zshrc) (ici bash)
    - open ~/.bash_profile   (ou open ~/.zshrc)
    - Copier la ligne dans le fichier bash_profile :
        export PATH="$HOME/.symfony/bin:$PATH"
    - source ~/.bash_profile   (ou source ~/.zshrc)
    - symfony
    - symfony -V


2) - Micro-framework (on installe le minimum, on pourra rajouter après des bundles) :
        symfony new pinterest-clone

   NORMALEMENT ON UTILISE CECI :
   - monolithique (on installe tout) :
        symfony new pinterst-clone --full


3) - symfony serve (ou symofony server:start)
        - symfony helper
   - symfony server:ca:install (pour installer le certificat de sécurité 'https')
        - symfony serve --no-tls (pour utiliser 'http')
        - symfony serve -d (en mode daemon, en background, on peut taper des commandes dans la console)
        - symfony server:list (liste des serveurs démarrés)


4) - symfony proxy:domain:attach pinterest-clone   (http://pinterest-clone.wip, Work In Progress)
   On peut installer le certificat

   - symfony serve
   - symfony proxy:start
   cliquer sur l'icone wifi en haut
        -> ouvrir les préférences Réseau...
        -> avancé
        -> proxys
        -> cocher 'Découverte auto proxy'
        -> cocher 'Configuration de proxy automatique'
            URL : http://localhost:7080/proxy.pac
    On peut (normalement) ouvrir 'https://pinterest-clone.wip
    (Si ça ne marche pas, aller dans les paramètres wifi, enlever l'URL, valider, remettre, valider)

    - symfony open:local (pour ouvrir le navigateur tout seul)


5) - symfony console   (ou 'php bin/console' ou 'bin/console' pour Mac et Linux)
   - composer require maker --dev   (ou 'composer req maker' car require est la seule commande qui commence par req)
                                    ('maker' est un alias pour 'maker-bundle)
                                    (flex.symfony.com)
                                    ('composer remove maker --dev' pour l'enlever)
                                    (permet d'utiliser 'make:controller', 'make:form', 'make:entity'...)


6) - symfony console make:controller
   On nous demande d'installer 'doctrine/annotations'
   - composer req doctrine/annotations
   - symfony console make:controller
        -> PinsController
     (On aurait pu taper directement 'symfony console make:controller PinsController')
     ('rm -rf src/Controller/*.php' va supprimer tous les fichiers php du dossier 'src/Controller')
     EN MONOLITHIQUE, IL CREE LE CONTROLLER ET LA VUE QUI VA AVEC ('templates/pins/index.html.twig')
   - composer req twig-bundle
   - créer un dossier 'templates' avec dedans un dossier 'pins' avec un fichier 'index.html.twig'
     (On peut configurer les routes via 'routes.yaml', ici on utilise les annotations)


7) - composer req orm
     ('composer unpack orm' pour diviser les bundles dans 'composer.json', 'composer update' pour revenir à l'état d'avant)
   - symfony console make:entity
        -> Pin
        -> title
        -> string
        -> 255
        -> no
        -> description
        -> text
        -> no
        -> 'entrée'
    Dans le fichier 'src/Entity/Pin.php', ajouter l'annotation suivante : '@ORM\Table(name="pins")' pour nommer la table 'pins'


8) Ouvrir MAMP.
   Ouvrir phpmyadmin : 'localhost:8888/phpmyadmin'.
   'Comptes utilisateurs' -> 'Nouvel utilisateur' -> 'Ajouter un compte utilisateur'.
        -> Nom d'utilisateur : pinterest_clone_dev
        -> Nom d'hôte : (tout hôte) %
        -> Mot de passe : pinterest_clone_dev
        -> Saisir à nouveau : pinterest_clone_dev
   'Base de données pour ce compte d'utilisateur' -> cocher 'Créer une base portant son nom et donner à cet utilisateur tous les privilèges sur cette base'.
   'Privilèges globaux' -> 'Tout cocher'
   'Exécuter'
   Créer un fichier '.env.local' et y mettre :
        DATABASE_URL=mysql://...   (reprendre ce qu'il y a dans le fichier '.env')
   Ce fichier '.env.local' prend le dessus sur le fichier '.env' et il n'est pas commité sur github.
   - symfony console make:migration
   - symfony console doctrine:migrations:migrate
   La table 'pins' est créée dans la base de données 'pinterest_clone_dev'.


9) - composer req debug --dev
        (Si erreur : 'symfony console cache:clear)


10) 'PinsController.php' :
        use App\Entity\Pin;
        use Doctrine\ORM\EntityManagerInterface;
        use Symfony\Component\HttpFoundation\Response;
        use Symfony\Component\Routing\Annotation\Route;
        use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
        public function index(EntityManagerInterface $em): Response
        {
            $pin = new Pin;
            $pin->setTitle('Title 4');
            $pin->setDescription('Description 4');

            $em->persist($pin);
            $em->flush();
            
            return $this->render('pins/index.html.twig');
        }
   Pour remettre à 1 l'id lors de la prochaine création :
        -> vider la table, 2 solutions :
            - dans phpmyadmin directement
            - via la console :
                - symfony console doctrine:query:sql "delete from pins"
        -> dans phpmyadmin, se mettre dans la table
        -> cliquer en haut sur 'Opérations' -> 'Options pour cette table' -> 'AUTO_INCREMENT' : 1


11) - composer req security-csrf
    Créer le fichier 'templates/pins/create.html.twig' et créer le formulaire.
    Créer la fonction create dans le fichier 'PinsController.php'.


12) 'src/Controller/PinsController.php' :
        /**
        * @Route("/pins/create", name="app_pins_create", methods={"GET", "POST"})
        */
        public function create(Request $request, EntityManagerInterface $em)
        {   
            if ($request->isMethod('POST')) {
                $data = $request->request->all();

                if ($this->isCsrfTokenValid('pins_create', $data['_token'])) {
                    $pin = new Pin;
                    $pin->setTitle($data['title']);
                    $pin->setDescription($data['description']);
                    $em->persist($pin);
                    $em->flush();
                }

                return $this->redirectToRoute('app_home');
                // return $this->redirectToRoute($this->generateUrl('app_home'));
                // return $this->redirect('https://www.google.fr');
            }
            
            return $this->render('pins/create.html.twig');
        }
    
    'templates/pins/create.html.twig' :
        <form action="" method="POST">
            <input type="hidden" name="_token" value="{{ csrf_token('pins_create') }}">
            <div>
                <label for="title">Title</label><br>
                <input type="text" id="title" name="title">
            </div>
            <div>
                <label for="description">Description</label><br>
                <textarea id="description" rows="5" cols="60" name="description"></textarea>
            </div>
            <input type="submit" value="Create Pin">
        </form>


13) 'PinsController.php' (function index) :
        use App\Entity\Pin;
        use Doctrine\ORM\EntityManagerInterface;
        use Symfony\Component\HttpFoundation\Response;
        use Symfony\Component\Routing\Annotation\Route;
        use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
        public function index(EntityManagerInterface $em): Response
        {
            $pin = new Pin;
            $pin->setTitle('Title 4');
            $pin->setDescription('Description 4');

            $em->persist($pin);
            $em->flush();
            
            return $this->render('pins/index.html.twig');
        }
    Modifier la vue 'templates/pins/index.html.twig' :
        {% for pin in pins %}
            <article>
                <h1>{{ pin.title }}</h1>
                <p>{{ pin.description }}</p>
            </article>
        {% endfor %}


14) - symfony console debug:router   (pour voir les routes créées et surtout voir les noms (à gauche) pour les mettre dans 'path(nom de route)', noms que l'on a donnés dans le controller (name))
    - symfony console router:match /pins/2   (pour voir ce que symfony affiche en fonction de la route)


15) - composer req form
    'PinsController.php' (function create) :
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

                return $this->redirectToRoute('app_home');
            }
            
            return $this->render('pins/create.html.twig', [
                'monFormulaire' => $form->createView()
            ]);
        }

    'templates/pins/create.html.twig' :
        {{ form_start(monFormulaire) }}
            {{ form_widget(monFormulaire) }}
            <input type="submit" value="Create Pin">
        {{ form_end(monFormulaire) }}
    

16) https://twig.symfony.com/


17) https://php-osx.liip.ch/
    et taper la ligne de commande de la version de PHP que l'on veut installer


18) Exemple de 4.4 à 5.0 :
    Aller dans 'composer.json' et remplacer tous les 'symfony/..."4.4.*"' par 'symfony/..."5.0.*"'
    - composer update "symfony/*"


19) 'PinsController.php' :
        /**
        * @Route("/pins/{id<[0-9]+>}")
        */
        public function show(PinRepository $repo, int $id): Response
        {
            $pin = $repo->find($id);
            return $this->render('pins/show.html.twig', compact('pin'));
        }
    
    'templates/pins/show.html.twig' :
        {% extends 'base.html.twig' %}
        {% block title %}Detail {% endblock %}
        {% block body %}
            <h1>{{ pin.title }}</h1>
        {% endblock %}


20) 'PinsController.php' :
        /**
        * @Route("/pins/{id<[0-9]+>}", name="app_pins_show")
        */
        public function show(PinRepository $repo, int $id): Response
        {
            $pin = $repo->find($id);
            if (!$pin) {
                throw $this->createNotFoundException('Pin #' . $id . ' not found');
            }
            return $this->render('pins/show.html.twig', compact('pin'));
        }


21) - composer req annotation
    'PinsController.php' :
        /**
        * @Route("/pins/{id<[0-9]+>}", name="app_pins_show")
        */
        public function show(Pin $pin): Response
        {
            return $this->render('pins/show.html.twig', compact('pin'));
        }