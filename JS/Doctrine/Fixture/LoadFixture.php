<?php

namespace JS\Doctrine\Fixture;

use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManager;

class LoadFixture
{

    private $fixtures = [];
    private $entityManager;

    /**
     * @var \Doctrine\Common\DataFixtures\Loader
     */
    private $loader;

    /**
     * @var \Doctrine\Common\DataFixtures\Executor\ORMExecutor
     */
    private $executor;

    public function __construct(EntityManager $entityManager, $fixtures = [])
    {
        $this->fixtures = $fixtures;
        $this->entityManager = $entityManager;
    }

    public function loadEntities()
    {
        if (!$this->executor)
        {
            $load = new Loader();
            if (count($this->fixtures) == 0)
            {
                throw new \RuntimeException("Nenhuma fixture adicionada");
            }

            foreach ($this->fixtures as $fixture)
            {
                if (class_exists($fixture))
                {
                    $load->addFixture(new $fixture);
                } else
                {
                    throw new \InvalidArgumentException(sprintf("Fixture %s não é uma fixture válida", $fixture));
                }
            }
            $purger = new ORMPurger();
            $this->executor = new ORMExecutor($this->entityManager, $purger);
            $this->executor->execute($load->getFixtures());
            $this->loader = $load;
        }
        return $this->loader;
    }

    /**
     * @return \Doctrine\Common\DataFixtures\AbstractFixture
     */
    public function getFixture($className)
    {
        $this->loadEntities();
        foreach ($this->loader->getFixtures() as $fixture)
        {
            if ($fixture instanceof $className)
            {
                return $fixture;
            }
        }
    }

    public function get($reference)
    {
        $this->loadEntities();
        return $this->executor->getReferenceRepository()->getReference($reference);
    }

    public function getLoader()
    {
        return $this->loader;
    }

    public function getExecutor()
    {
        return $this->executor;
    }

}
