<?php
/**
 * This file is part of doctrine-module
 * Copyright (c) 2020
 *
 * @file    PositionListener.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace Devrun\DoctrineModule\Listeners;

use Devrun\DoctrineModule\Entities\PositionTrait;
use Devrun\Utils\Debugger;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\NonUniqueResultException;
use Kdyby\Events\Subscriber;
use Tracy\ILogger;

class PositionListener implements Subscriber
{

    /**
     * Stores the current user into createdBy and updatedBy properties
     *
     * @param LifecycleEventArgs $eventArgs
     */
    public function prePersist(LifecycleEventArgs $eventArgs)
    {
        $em  = $eventArgs->getEntityManager();
        $uow = $em->getUnitOfWork();


        /** @var PositionTrait $entity */
        $entity = $eventArgs->getEntity();
        if (($classMetadata = $em->getClassMetadata(get_class($entity))) instanceof ClassMetadata) {

            if ($this->isPositionable($classMetadata)) {

                try {
                    $info = $em->getRepository($classMetadata->getName())->createQueryBuilder('e')
                               ->select('COUNT(e.id) as num, MAX(e.position) as max_positions')
                               ->where('e.category = ?1')->setParameter(1, $entity->category)
                               ->getQuery()
                               ->setMaxResults(1)
                               ->getOneOrNullResult();

                    $maxPositions = intval($info['max_positions']);

                    if (!$entity->position) {
                        $entity->position = $maxPositions + 1;
                    }

                } catch (NonUniqueResultException $e) {
                    Debugger::log($e, ILogger::EXCEPTION);
                }
            }
        }
    }


    /**
     * Return is timeStable entity
     *
     * @param ClassMetadata $class
     *
     * @return bool is timeStable entity
     */
    private function isPositionable(ClassMetadata $class)
    {
        $className = version_compare(PHP_VERSION, '5.5.0')
            ? PositionTrait::class
            : PositionTrait::getPositionTraitName();

        return in_array($className, $class->getReflectionClass()->getTraitNames());
    }


    /**
     * Returns an array of events this subscriber wants to listen to.
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [
            Events::prePersist,
        ];

    }

}