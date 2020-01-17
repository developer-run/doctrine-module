<?php
/**
 * This file is part of doctrine-module
 * Copyright (c) 2019
 *
 * @file    FlushListener.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace Devrun\DoctrineModule\Listeners;

use Kdyby\Doctrine\EntityManager;
use Kdyby\Events\Subscriber;
use Nette\Application\IPresenter;
use Nette\Application\IResponse;
use Nette\Application\Responses\TextResponse;

class FlushListener implements Subscriber
{

    /** @var EntityManager */
    private $entityManager;

    private $autoFlush;

    /**
     * FlushListener constructor.
     *
     * @param bool          $autoFlush
     * @param EntityManager $entityManager
     */
    public function __construct(bool $autoFlush, EntityManager $entityManager)
    {
        $this->autoFlush     = $autoFlush;
        $this->entityManager = $entityManager;
    }


    /**
     * @param IPresenter $presenter
     * @param IResponse|TextResponse $response
     *
     * @throws \Exception
     */
    public function onShutdown(IPresenter $presenter, IResponse $response = null)
    {
        if (!$this->autoFlush || $this->checkChangeSet()) {
            return;
        }

        /*
        if ($response instanceof TextResponse) {
            $html = (string)$response->getSource();
            if ($this->checkChangeSet()) {
                return;
            }
        }
        */
    }


    /**
     * @return bool
     * @throws \Exception
     */
    private function checkChangeSet()
    {
        $uow = $this->entityManager->getUnitOfWork();
        if ($uow->getScheduledEntityInsertions() || $uow->getScheduledEntityUpdates() || $uow->getScheduledEntityDeletions()) {
            $this->entityManager->flush();
            return true;
        }

        return false;
    }


    function getSubscribedEvents()
    {
        return [
            'Nette\Application\UI\Presenter::onShutdown'
        ];

    }
}