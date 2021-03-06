<?php

namespace JobQueue\Application\Console;

use JobQueue\Application\Utils\CommandTrait;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class ShowTask extends ManagerCommand
{
    use CommandTrait;

    public function configure()
    {
        $this
            ->setName('show')
            ->setDescription('Show a task information')
            ->addArgument('identifier', InputArgument::REQUIRED, 'Task UUID identifier')
        ;
    }

    /**
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $task = $this->queue->find($input->getArgument('identifier'));

        $this->formatTaskBlock($task, $output);

        return 0;
    }
}
