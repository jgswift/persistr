<?php
namespace persistr\Model {
    use persistr;
    
    final class Blank implements persistr\Interfaces\Model {
        
        /**
         * Class model is bound to
         * @var string 
         */
        private $className;
        
        /**
         * object registry
         * @var persistr\Object\Registry 
         */
        private static $registry;
        
        /**
         * Generic model constructor
         * Models should not typically accept class name datum in constructor
         * @param string $className
         */
        function __construct($className) {
            $this->className = $className;
            if(empty(self::$registry)) {
                self::$registry = new persistr\Object\Registry($this,'persistr\Interfaces\Persistent');
            }
        }
        
        /**
         * Retrieve local classname
         * @return string
         */
        public function getClassName() {
            return $this->className;
        }

        /**
         * Retrieves local object registry
         * @return persistr\Object\Registry
         */
        public function getRegistry() {
            return self::$registry;
        }
        
        /**
         * Helper method
         * Binds callable to object property
         * @param string $attribute
         * @param callable $callable
         * @return persistr\Model\Blank
         */
        public function bind($attribute, callable $callable=null) {
            persistr\Object\Binding\Registry::bind(self::$registry->getTypeName(), $attribute, $callable);
            return $this;
        }
        
        /**
         * Helper method
         * Binds callable to specific object instance rather than whole model
         * @param object $object
         * @param string $attribute
         * @param callable $callable
         * @return persistr\Model\Blank
         */
        public function bindTo($object,$attribute,callable $callable=null) {
            persistr\Object\Binding\Registry::bindTo($object, $attribute, $callable);
            return $this;
        }
    }
}