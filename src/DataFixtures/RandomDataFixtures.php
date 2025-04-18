<?php

namespace App\DataFixtures;

use App\Entity\Avis;
use App\Entity\Centre;
use App\Entity\Chat;
use App\Entity\Contrat;
use App\Entity\Demande;
use App\Entity\Event;
use App\Entity\Historique;
use App\Entity\Notification;
use App\Entity\Participation;
use App\Entity\Plannificationtache;
use App\Entity\Poubelle;
use App\Entity\Tache;
use App\Entity\Traitement;
use App\Repository\CentreRepository;
use App\Repository\PoubelleRepository;
use App\Repository\UserRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class RandomDataFixtures extends Fixture
{
    private $faker;
    private $userRepository;
    private $centreRepository;
    private $poubelleRepository;

    public function __construct(
        UserRepository $userRepository,
        CentreRepository $centreRepository,
        PoubelleRepository $poubelleRepository
    ) {
        $this->userRepository = $userRepository;
        $this->centreRepository = $centreRepository;
        $this->poubelleRepository = $poubelleRepository;
        $this->faker = Factory::create('fr_FR');
    }

    public function load(ObjectManager $manager): void
    {
        // Create 5 Centres
        $centres = $this->createCentres($manager);

        // Create 5 Poubelles
        $poubelles = $this->createPoubelles($manager, $centres);

        // Create 5 Historiques
        $this->createHistoriques($manager, $poubelles);

        // Create 5 Avis
        $this->createAvis($manager);

        // Create 5 Events
        $events = $this->createEvents($manager);

        // Create 5 Participations
        $this->createParticipations($manager, $events);

        // Create 5 Taches
        $taches = $this->createTaches($manager, $centres);

        // Create 5 Plannificationtaches
        $this->createPlannificationtaches($manager, $taches);

        // Create 5 Demandes
        $demandes = $this->createDemandes($manager, $centres);

        // Create 5 Traitements
        $this->createTraitements($manager, $demandes);

        // Create 5 Contrats
        $this->createContrats($manager, $centres);

        // Create 5 Chats
        $this->createChats($manager);

        // Create 5 Notifications
        $this->createNotifications($manager);

        $manager->flush();
    }

    private function createCentres(ObjectManager $manager): array
    {
        $centres = [];

        for ($i = 0; $i < 5; $i++) {
            $centre = new Centre();
            $centre->setNom($this->faker->company . ' Recyclage');
            $centre->setLongitude($this->faker->longitude(9.0, 11.5)); // Tunisia longitude range
            $centre->setAltitude($this->faker->latitude(30.0, 37.5)); // Tunisia latitude range
            $centre->setTelephone($this->faker->numerify('########')); // 8 digits
            $centre->setEmail($this->faker->companyEmail);

            $manager->persist($centre);
            $centres[] = $centre;
        }

        return $centres;
    }

    private function createPoubelles(ObjectManager $manager, array $centres): array
    {
        $poubelles = [];

        for ($i = 0; $i < 5; $i++) {
            $poubelle = new Poubelle();
            $poubelle->setCentre($centres[array_rand($centres)]);
            $poubelle->setAdresse($this->faker->address);
            $poubelle->setNiveau($this->faker->numberBetween(0, 100));
            $poubelle->setEtat($this->faker->randomElement(['Fonctionnel', 'En panne']));
            $poubelle->setDateInstallation($this->faker->dateTimeBetween('-2 years', 'now'));
            $poubelle->setHauteurTotale($this->faker->numberBetween(100, 200));

            $manager->persist($poubelle);
            $poubelles[] = $poubelle;
        }

        return $poubelles;
    }

    private function createHistoriques(ObjectManager $manager, array $poubelles): void
    {
        for ($i = 0; $i < 5; $i++) {
            $historique = new Historique();
            $historique->setDateEvenement($this->faker->dateTimeBetween('-6 months', 'now'));
            $historique->setTypeEvenement($this->faker->randomElement(['Collecte', 'Traitement', 'Recyclage', 'Maintenance']));
            $historique->setDescription($this->faker->sentence(10));
            $historique->setQuantiteDechets($this->faker->randomFloat(2, 5, 500));
            $historique->setPoubelle($poubelles[array_rand($poubelles)]);

            $manager->persist($historique);
        }
    }

    private function createAvis(ObjectManager $manager): void
    {
        for ($i = 0; $i < 5; $i++) {
            $avis = new Avis();
            $avis->setNom($this->faker->name);
            $avis->setAvis($this->faker->sentence(10));
            $avis->setDescription($this->faker->paragraph(3));
            $avis->setNote($this->faker->numberBetween(1, 5));

            $manager->persist($avis);
        }
    }

    private function createEvents(ObjectManager $manager): array
    {
        $events = [];

        for ($i = 0; $i < 5; $i++) {
            $event = new Event();
            $event->setTitle($this->faker->sentence(4));
            $event->setDescription($this->faker->paragraph(2));
            $event->setImageName('default_event.jpg'); // Default image
            $event->setRemainingPlaces($this->faker->numberBetween(10, 100));
            $event->setLocation($this->faker->city);
            $event->setDate($this->faker->dateTimeBetween('+1 week', '+6 months'));

            $manager->persist($event);
            $events[] = $event;
        }

        return $events;
    }

    private function createParticipations(ObjectManager $manager, array $events): void
    {
        for ($i = 0; $i < 5; $i++) {
            $participation = new Participation();
            $participation->setFirstName($this->faker->firstName);
            $participation->setLastName($this->faker->lastName);
            $participation->setEmail($this->faker->email);
            // Check if methods exist before calling them
            if (method_exists($participation, 'setPhone')) {
                $participation->setPhone($this->faker->phoneNumber);
            }
            if (method_exists($participation, 'setAddress')) {
                $participation->setAddress($this->faker->address);
            }
            if (method_exists($participation, 'setCity')) {
                $participation->setCity($this->faker->city);
            }
            if (method_exists($participation, 'setZipCode')) {
                $participation->setZipCode($this->faker->postcode);
            }
            if (method_exists($participation, 'setCountry')) {
                $participation->setCountry('Tunisia');
            }
            $participation->setEvent($events[array_rand($events)]);
            if (method_exists($participation, 'setReminderSent')) {
                $participation->setReminderSent(false);
            }

            $manager->persist($participation);
        }
    }

    private function createTaches(ObjectManager $manager, array $centres): array
    {
        $taches = [];
        $users = $this->userRepository->findByRole('ROLE_EMPLOYEE');

        // If no employees found, use a default ID
        $employeeId = !empty($users) ? $users[array_rand($users)]->getId() : 1;

        for ($i = 0; $i < 5; $i++) {
            $tache = new Tache();
            // Make sure we have a valid centre ID
            $centreId = $centres[array_rand($centres)]->getId();
            if ($centreId === null) {
                $centreId = 1; // Default ID if null
            }
            $tache->setIdCentre($centreId);
            $tache->setIdEmploye($employeeId);
            $tache->setAltitude($this->faker->latitude(30.0, 37.5)); // Tunisia latitude range
            $tache->setLongitude($this->faker->longitude(9.0, 11.5)); // Tunisia longitude range
            $tache->setMessage($this->faker->sentence(10));
            $tache->setEtat($this->faker->randomElement(['Terminé', 'En cours', 'En attente']));

            $manager->persist($tache);
            $taches[] = $tache;
        }

        return $taches;
    }

    private function createPlannificationtaches(ObjectManager $manager, array $taches): void
    {
        for ($i = 0; $i < 5; $i++) {
            $plannificationtache = new Plannificationtache();
            $plannificationtache->setPriorite($this->faker->randomElement(['haute', 'moyenne', 'basse']));
            $plannificationtache->setDateLimite($this->faker->dateTimeBetween('now', '+3 months'));
            $plannificationtache->setIdTache($taches[array_rand($taches)]);

            $manager->persist($plannificationtache);
        }
    }

    private function createDemandes(ObjectManager $manager, array $centres): array
    {
        $demandes = [];
        $users = $this->userRepository->findByRole('ROLE_CITOYEN');

        for ($i = 0; $i < 5; $i++) {
            $demande = new Demande();
            $demande->setAdresse($this->faker->address);
            $demande->setEmailUtilisateur($this->faker->email);
            $demande->setMessage($this->faker->sentence(15));
            $demande->setType($this->faker->randomElement(['Reclamation', 'Demande']));

            // Set user if available
            if (!empty($users)) {
                $demande->setUtilisateur($users[array_rand($users)]);
            }

            $demande->setCentre($centres[array_rand($centres)]);

            $manager->persist($demande);
            $demandes[] = $demande;
        }

        return $demandes;
    }

    private function createTraitements(ObjectManager $manager, array $demandes): void
    {
        for ($i = 0; $i < 5; $i++) {
            $traitement = new Traitement();
            $traitement->setStatus($this->faker->randomElement(['Traité', 'En cours de traitement', 'Refusé']));
            $traitement->setDateTraitement($this->faker->dateTimeBetween('-1 month', 'now'));
            $traitement->setCommentaire($this->faker->paragraph(2));
            $traitement->setDemande($demandes[array_rand($demandes)]);

            $manager->persist($traitement);
        }
    }

    private function createContrats(ObjectManager $manager, array $centres): void
    {
        $users = $this->userRepository->findByRole('ROLE_EMPLOYEE');

        // If no employees found, skip
        if (empty($users)) {
            return;
        }

        for ($i = 0; $i < 5; $i++) {
            $contrat = new Contrat();
            $contrat->setEmploye($users[array_rand($users)]);

            $dateDebut = $this->faker->dateTimeBetween('-1 year', 'now');
            $dateFin = clone $dateDebut;
            $dateFin->modify('+1 year');

            $contrat->setDateDebut($dateDebut);
            $contrat->setDateFin($dateFin);
            $contrat->setSignaturePath('signatures/signature_' . $i . '.png');
            $contrat->setCentre($centres[array_rand($centres)]);

            $manager->persist($contrat);
        }
    }

    private function createChats(ObjectManager $manager): void
    {
        for ($i = 0; $i < 5; $i++) {
            $chat = new Chat();
            $chat->setUser($this->faker->name);
            $chat->setMessage($this->faker->sentence(10));

            $manager->persist($chat);
        }
    }

    private function createNotifications(ObjectManager $manager): void
    {
        for ($i = 0; $i < 5; $i++) {
            $notification = new Notification();
            $notification->setMessage($this->faker->sentence(8));
            $notification->setIsRead($this->faker->boolean(30)); // 30% chance of being read

            $manager->persist($notification);
        }
    }
}
