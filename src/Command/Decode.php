<?php

namespace Teamleader\Uuidifier\Command;

use Ramsey\Uuid\Uuid;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Teamleader\Uuidifier\Uuidifier;

class Decode extends Command
{
    protected function configure()
    {
        $this->setName('uuidifier:decode');
        $this->setDescription('Decode a uuid into an int');
        $this->addArgument(
            'uuid',
            InputArgument::REQUIRED,
            'The uuid'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $uuidifier = new UuidIfier();
        $uuid = $input->getArgument('uuid');
        $int = $uuidifier->decode(Uuid::fromString($uuid));
        $output->writeln($int);
    }
}
