<?php

namespace App\Command;

use App\DataFixtures\RandomDataFixtures;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:generate-random-data',
    description: 'Generate random data for entities',
)]
class GenerateRandomDataCommand extends Command
{
    private $randomDataFixtures;
    private $entityManager;

    public function __construct(
        RandomDataFixtures $randomDataFixtures,
        EntityManagerInterface $entityManager
    ) {
        parent::__construct();
        $this->randomDataFixtures = $randomDataFixtures;
        $this->entityManager = $entityManager;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Generating Random Data');
        $io->progressStart(10); // 10 entity types

        try {
            $this->randomDataFixtures->load($this->entityManager);
            $io->progressAdvance(10);
            $io->progressFinish();

            $io->success('Random data generated successfully!');
            $io->table(
                ['Entity', 'Count'],
                [
                    ['Centre', '5'],
                    ['Poubelle', '5'],
                    ['Historique', '5'],
                    ['Avis', '5'],
                    ['Event', '5'],
                    ['Participation', '5'],
                    ['Tache', '5'],
                    ['Plannificationtache', '5'],
                    ['Demande', '5'],
                    ['Traitement', '5'],
                    ['Contrat', '5'],
                    ['Chat', '5'],
                    ['Notification', '5'],
                ]
            );

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->progressFinish();
            $io->error('An error occurred: ' . $e->getMessage());
            $io->error($e->getTraceAsString());

            return Command::FAILURE;
        }
    }
}
