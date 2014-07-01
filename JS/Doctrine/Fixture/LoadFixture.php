<?php

namespace JS\Doctrine\Fixture;

use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManager;

class LoadFixture {

    private $fixtures = [];
    private $entityManager;

    /**
     * @var \Doctrine\Common\DataFixtures\Loader
     */
    public $loader;

    public function __construct(EntityManager $entityManager, $fixtures = []) {
        $this->fixtures = $fixtures;
        $this->entityManager = $entityManager;
    }

    public function loadEntities() {
        if (!$this->loader) {
            $load = new Loader();
            if (count($this->fixtures) == 0) {
                throw new \RuntimeException("Nenhuma fixture adicionada");
            }

            foreach ($this->fixtures as $fixture) {
                if (class_exists($fixture)) {
                    $load->addFixture(new $fixture);
                } else {
                    throw new \InvalidArgumentException(sprintf("Fixture %s não é uma fixture válida", $fixture));
                }
            }
            $purger = new ORMPurger();
            $executor = new ORMExecutor($this->entityManager, $purger);
            $executor->execute($load->getFixtures());
            $this->loader = $load;
        }
        return $this->loader;
    }

    /**
     * @return \Doctrine\Common\DataFixtures\AbstractFixture
     */
    public function getFixture($className) {
        $this->loadEntities();
        foreach ($this->loader->getFixtures() as $fixture) {
            if ($fixture instanceof $className) {
                return $fixture;
            }
        }
    }

}
