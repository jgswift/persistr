<?php
namespace persistr {
    trait Persistent {
        /**
         * Default persistence constructor
         * Constructor must call persist method
         * An object must be persisted before it can be bound to
         */
        function __construct() {
            $this->persist(func_get_args());
        }

        /**
         * Global implementation of Interfaces\Persistent
         * @param array $args
         */
        protected function persist(array $args = []) {
            Registry::persist($this,['args'=>$args]);
        }

        /**
         * Global implementation of Interfaces\Persistent
         * @return persistr\Interfaces\Model
         */
        function getModel() {
            return Registry::getModel($this);
        }

        /**
         * Global implementation of Interfaces\Persistent
         * @param persistr\Interfaces\Model $model
         */
        function setModel(Interfaces\Model $model) {
            Registry::setModel($this, $model);
        }

        /**
         * Retrieves property dynamically if persistence available
         * @param string $name
         * @return mixed
         */
        function __get($name) {
            if(Object\Binding\Registry::isBound($this, $name)) {
                $binding = Object\Binding\Registry::getBinding($this, $name);
                return $this->$name = $binding($this);
            }
        }
    }
}