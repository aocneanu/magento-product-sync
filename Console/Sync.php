<?php

namespace LaravelEnso\MagentoProductSync\Console;

use LaravelEnso\MagentoProductSync\Service\Sync as Service;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Sync extends Command
{
    private $_state;

    public function __construct(State $state)
    {
        $this->_state = $state;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('enso:products:sync');
        $this->setDescription('Sync products from API');

        parent::configure();
    }
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->_state->setAreaCode(Area::AREA_ADMINHTML);

        (new Service(new ProgressBar($output, 100)))
            ->handle();
    }
}