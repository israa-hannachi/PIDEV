<?php

namespace App\Command;

use App\Entity\AIModelAdjustment;
use App\Repository\AIPredictionRepository;
use App\Repository\AIModelAdjustmentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:ai:train',
    description: 'Trains the AI model by comparing past predictions with actual results.',
)]
class TrainAICommand extends Command
{
    private $entityManager;
    private $predictionRepository;
    private $adjustmentRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        AIPredictionRepository $predictionRepository,
        AIModelAdjustmentRepository $adjustmentRepository
    ) {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->predictionRepository = $predictionRepository;
        $this->adjustmentRepository = $adjustmentRepository;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('AI Model Training Feedback Loop');

        // 1. Get unevaluated predictions for past events
        $predictions = $this->predictionRepository->findBy(['evaluated' => false]);
        $evaluatedCount = 0;

        foreach ($predictions as $prediction) {
            $event = $prediction->getEvent();
            if ($event->getDateFin() < new \DateTime()) {
                // Event is finished, we can evaluate
                $actualAttendance = ($event->getInscrits() / max(1, $event->getCapacite())) * 100;
                $prediction->setActualValue($actualAttendance);
                $prediction->setEvaluated(true);
                
                $accuracy = 100 - abs($prediction->getPredictedValue() - $actualAttendance);
                $prediction->setAccuracyPercentage($accuracy);

                // 2. Adjust factors
                $factors = $prediction->getFactors();
                foreach ($factors as $type => $value) {
                    $adjustment = $this->adjustmentRepository->findOneBy([
                        'factorType' => $type
                    ]);

                    if (!$adjustment) {
                        $adjustment = new AIModelAdjustment();
                        $adjustment->setFactorType($type);
                        $adjustment->setFactorValue('weighted_avg');
                        $adjustment->setSampleSize(0);
                    }

                    // Simple moving average adjustment
                    $bias = $actualAttendance / max(1, $prediction->getPredictedValue());
                    $currentMultiplier = $adjustment->getAdjustmentMultiplier() ?? 1.0;
                    $sampleSize = $adjustment->getSampleSize() ?? 0;
                    
                    $newMultiplier = (($currentMultiplier * $sampleSize) + $bias) / ($sampleSize + 1);
                    
                    $adjustment->setAdjustmentMultiplier($newMultiplier);
                    $adjustment->setSampleSize($sampleSize + 1);
                    $adjustment->setLastUpdated(new \DateTimeImmutable());

                    $this->entityManager->persist($adjustment);
                }
                
                $evaluatedCount++;
            }
        }

        $this->entityManager->flush();

        $io->success("Training complete: {$evaluatedCount} predictions evaluated and model factors adjusted.");

        return Command::SUCCESS;
    }
}
