<?php

namespace Devrun\DoctrineModule\DoctrineForms;

use Devrun;
use Devrun\DoctrineModule\InvalidArgumentException;
use Kdyby\Doctrine\EntityManager;
use Nette;
use Nette\Forms\Container;
use Nette\Forms\Controls\BaseControl;
use Symfony\Component\PropertyAccess\PropertyAccessor;

/**
 * Class EntityFormMapper
 * @package Devrun\DoctrineModule\DoctrineForms
 */
class EntityFormMapper
{

    use Nette\SmartObject;

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var IComponentMapper[]
     */
    private $componentMappers = array();

    /**
     * @var PropertyAccessor
     */
    private $accessor;


    public function __construct(EntityManager $entityManager)
    {
        $this->em = $entityManager;

        $this->componentMappers = array(
            new Devrun\DoctrineModule\Controls\RangeControl($this),
            new Devrun\DoctrineModule\Controls\TextControl($this),
            new Devrun\DoctrineModule\Controls\ToOne($this),
            new Devrun\DoctrineModule\Controls\ToMany($this),
        );
    }


    public function registerMapper(IComponentMapper $mapper)
    {
        array_unshift($this->componentMappers, $mapper);
    }


    /**
     * @return \Symfony\Component\PropertyAccess\PropertyAccessor
     */
    public function getAccessor()
    {
        if ($this->accessor === NULL) {
            $this->accessor = new PropertyAccessor(TRUE);
        }

        return $this->accessor;
    }


    /**
     * @return \Doctrine\ORM\EntityManager|\Kdyby\Doctrine\EntityManager
     */
    public function getEntityManager()
    {
        return $this->em;
    }


    /**
     * @param object                $entity
     * @param BaseControl|Container $formElement
     */
    public function load($entity, $formElement)
    {
        $meta = $this->getMetadata($entity);

        foreach (self::iterate($formElement) as $component) {
            foreach ($this->componentMappers as $mapper) {
                if ($mapper->load($meta, $component, $entity)) {
                    break;
                }
            }
        }

    }


    /**
     * @param object                $entity
     * @param BaseControl|Container $formElement
     */
    public function save($entity, $formElement)
    {
        $meta = $this->getMetadata($entity);

        foreach (self::iterate($formElement) as $component) {
            foreach ($this->componentMappers as $mapper) {
                if ($mapper->save($meta, $component, $entity)) {
                    break;
                }
            }
        }
    }


    /**
     * @param BaseControl|Container $formElement
     *
     * @return array|\ArrayIterator
     * @throws InvalidArgumentException
     */
    private static function iterate($formElement)
    {
        if ($formElement instanceof Container) {
            return $formElement->getComponents();

        } elseif ($formElement instanceof Nette\Forms\IControl) {
            return array($formElement);

        } else {
            throw new InvalidArgumentException('Expected Nette\Forms\Container or Nette\Forms\IControl, but ' . get_class($formElement) . ' given');
        }
    }


    /**
     * @param object $entity
     *
     * @return \Doctrine\ORM\Mapping\ClassMetadata
     * @throws InvalidArgumentException
     */
    private function getMetadata($entity)
    {
        if (!is_object($entity)) {
            throw new InvalidArgumentException('Expected object, ' . $entity ?: gettype($entity) . ' given.');
        }

        return $this->em->getClassMetadata(get_class($entity));
    }

}
