<?php

declare(strict_types=1);

namespace Millon\PhpRefactoring\Command;

use LogicException;
use Millon\PhpRefactoring\Entity\Person;
use Millon\PhpRefactoring\Service\Contracts\ComissionCalculatorInterface;
use Millon\PhpRefactoring\Service\Exception\CalculationException;
use RuntimeException;
use SplFileObject;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\SerializerInterface;
use Throwable;

#[AsCommand(
    name: 'app:calculate-comissions',
    description: 'Console command to calculate commissions for already made transactions',
)]
final class CalculateComissionsCommand extends Command
{
    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly ComissionCalculatorInterface $commisionCalculator,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument(
                'filename',
                InputArgument::REQUIRED,
                'Filename of the file with financial data stored as json strings.'
            )
        ;
    }

    /**
     * @throws LogicException|RuntimeException|CalculationException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $filename = $input->getArgument('filename');

        $file = $this->open($filename, $io);
        $this->proccess($file, $io);

        return Command::SUCCESS;
    }

    /**
     * @throws LogicException|RuntimeException
     */
    private function open(mixed $filename, SymfonyStyle $io): SplFileObject
    {
        try {
            return new SplFileObject($filename, 'r');
        } catch (LogicException $e) {
            $io->error(sprintf('File "%s" is a directory', $filename));
        } catch (RuntimeException $e) {
            $io->error(sprintf('File "%s" cannot be found', $filename));
        }

        throw $e;
    }

    /**
     * Process file and print results line by line
     *
     * @throws CalculationException
     */
    private function proccess(SplFileObject $file, SymfonyStyle $io): void
    {
        // Read file line by line
        foreach ($file as $line) {
            // If line is empty, skip it
            if (empty($line)) {
                continue;
            }

            // TODO handle deserialize & calculate errors separately and gracefully
            $person = $this->serializer->deserialize($line, Person::class, JsonEncoder::FORMAT);
            $comission = $this->commisionCalculator->calculate($person);

            $io->writeln($comission, OutputInterface::OUTPUT_PLAIN);
        }
    }
}
