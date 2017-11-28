<?php

namespace Teamleader\Uuidifier\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Teamleader\Uuidifier\Uuidifier;

class Encode extends Command
{
    protected function configure()
    {
        $this->setName('uuidifier:encode');
        $this->setDescription('Encode a prefix and int to a uuid string');
        $this->addArgument(
            'prefix',
            InputArgument::REQUIRED,
            'The prefix, often the type of the identifier you want to encode'
        );
        $this->addArgument(
            'id',
            InputArgument::REQUIRED,
            'The integer identifier you want to encode'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $uuidifier = new UuidIfier();
        $prefix = $input->getArgument('prefix');
        $id = (int) $input->getArgument('id');
        $uuid = $uuidifier->encode(
            $prefix,
            $id
        );
        $output->writeln((string) $uuid);
    }
}
