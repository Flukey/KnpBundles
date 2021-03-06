<?php

namespace Knp\Bundle\KnpBundlesBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Knp\Bundle\KnpBundlesBundle\Updater\Updater;

/**
 * Update local database from web searches
 */
class KbPopulateCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setDefinition(array())
            ->addOption('limit', 'l', InputOption::VALUE_OPTIONAL, 'The maximal number of new bundles considered by the update', 1000)
            ->addOption('no-publish', null, InputOption::VALUE_NONE, 'Prevent the command from publishing a message to RabbitMQ producer')
            ->setName('kb:populate')
        ;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \InvalidArgumentException When the target directory does not exist
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {   
        $container = $this->getContainer();

        $em = $container->get('knp_bundles.entity_manager');

        $updater = $container->get('knp_bundles.updater');

        if (!$input->getOption('no-publish')) {
            // manually set RabbitMQ producer
            $updater->setBundleUpdateProducer($container->get('old_sound_rabbit_mq.update_bundle_producer'));
        }

        $updater->setOutput($output);
        $updater->setUp();

        $bundles = $em->getRepository('Knp\Bundle\KnpBundlesBundle\Entity\Bundle')->findAll();

        $bundles = $updater->searchNewBundles((int) $input->getOption('limit'));
        $updater->createMissingBundles($bundles);
        $updater->updateBundlesData();
        $updater->updateUsers();
    }
}
