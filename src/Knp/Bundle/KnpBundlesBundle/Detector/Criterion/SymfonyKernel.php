<?php

namespace Knp\Bundle\KnpBundlesBundle\Detector\Criterion;

use Symfony\Component\Finder\Finder;
use Knp\Bundle\KnpBundlesBundle\Git\Repo;

/**
 * Criterion that checks if the repository contains a Symfony kernel
 *
 * @author Antoine HÃ©rault <antoine.herault@gmail.com>
 */
class SymfonyKernel implements CriterionInterface
{
    /**
     * {@inheritDoc}
     */
    public function matches(Repo $repo)
    {
        $directory = $repo->getGitRepo()->getDir();

        $finder = new Finder();
        $finder->files()->name('*Kernel.php')->in($directory)->depth('< 3');

        $candidates = $finder->getIterator();

        foreach($candidates as $candidate) {
            if(preg_match('/public function registerBundles/s', file_get_contents($candidate))) {
                return true;
            }
        }

        return false;
    }
}
