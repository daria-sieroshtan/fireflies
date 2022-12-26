<?php

namespace App\Command;

namespace App\Command;

use App\Fireflies\Swarm;
use App\Fireflies\SwarmRenderer;
use App\Fireflies\VideoMaker;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Helper\ProgressBar;


#[AsCommand(
    name: 'fireflies:render',
    description: 'Render fireflies synchronisation magic',
    hidden: false,
)]
class RenderMagicCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->addArgument('field_size', InputArgument::OPTIONAL, 'Field size in pixels', 400)
            ->addArgument('fireflies_num', InputArgument::OPTIONAL, 'Number of fireflies', 100)
            ->addArgument('fireflies_sync_factor', InputArgument::OPTIONAL, 'Factor by which nearby fireflies sync', 1)
            ->addArgument('fireflies_period', InputArgument::OPTIONAL, 'Default (before sync) firefly period (in seconds)', 10)
            ->addArgument('duration', InputArgument::OPTIONAL, 'Duration of the simulation (in seconds)', 5)
            ->addArgument('fps', InputArgument::OPTIONAL, 'FPS of the output movie', 10)
            ->addOption("play", )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln("Creating swarm...");
        $swarm = new Swarm(
            $input->getArgument("field_size"),
            $input->getArgument("fireflies_num"),
            $input->getArgument("fireflies_sync_factor"),
            $input->getArgument("fireflies_period"),
        );

        $output->writeln("Running simulation...");
        $swarmRenderer = new SwarmRenderer($input->getArgument("field_size"));
        $numSteps = $input->getArgument("duration") * $input->getArgument("fps");
        $progressBar = new ProgressBar($output, $numSteps);
        for ($stepIndex = 1; $stepIndex < $numSteps; $stepIndex++) {
            $swarm->step();
            $swarmRenderer->renderSwarm($swarm);
            $progressBar->advance();
        }
        $progressBar->finish();
        $progressBar->clear();

        $output->writeln("Making pretty video...");
        $outFile = VideoMaker::makeVideo($input->getArgument("fps"), $swarmRenderer, $input->getOption("play"));
//        $swarmRenderer->cleanUp();

        $output->writeln(sprintf("All done! Your video is stored in %s", $outFile));
        return Command::SUCCESS;
    }
}
