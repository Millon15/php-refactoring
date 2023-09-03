<?php

declare(strict_types=1);

namespace Millon\PhpRefactoring\Command;

use Millon\PhpRefactoring\Entity\Person;
use Millon\PhpRefactoring\Service\Contracts\ComissionCalculatorInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\SerializerInterface;

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

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $filename = $input->getArgument('filename');

        // Open file
        try {
            $file = new \SplFileObject($filename, 'r');
        } catch (\LogicException $e) {
            $io->error(sprintf('File "%s" is a directory', $filename));
        } catch (\RuntimeException $e) {
            $io->error(sprintf('File "%s" cannot be found', $filename));
        } finally {
            if (isset($e)) {
                return Command::FAILURE;
            }
        }

        // Process file and print results line by line
        foreach ($this->proccess($file) as $proccesedLine) {
            $io->writeln($proccesedLine, OutputInterface::OUTPUT_PLAIN);
        }

        return Command::SUCCESS;
    }

    private function proccess(\SplFileObject $file): iterable
    {
        // Read file line by line
        foreach ($file as $line) {
            // If line is empty, skip it
            if (empty($line)) {
                continue;
            }

            $person = $this->serializer->deserialize($line, Person::class, JsonEncoder::FORMAT);

            yield $this->commisionCalculator->calculate($person);
        }
    }
}
