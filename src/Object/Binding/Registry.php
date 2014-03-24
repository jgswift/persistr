<?php
namespace persistr\Object\Binding {
    use persistr, qtil;
    
    class Registry extends qtil\Registry {
        /**
         * Global list of bindings
         * @var array
         */
        protected static $bindings = [];
        
        /**
         * Retrieves binding by object and binding name
         * @param object $object
         * @param string $name
         * @return callable
         */
        public static function getBinding($object, $name) {
            $uid = self::identify($object);

            return self::$bindings[$uid][$name];
        }
        
        /**
         * Binds callable to type by name
         * @param string $typeName
         * @param string $name
         * @param callable $callback
         */
        public static function bind($typeName, $name, callable $callback = null) {
            $model = persistr\Registry::getModelByName($typeName);
            
            $objectRegistry = $model->getRegistry();
            
            $instances = $objectRegistry->getInstances();
            
            if(!empty($instances)) {
                foreach($instances as $instance) {
                    self::bindTo($instance,$name,$callback);
                }
            }
        }

        /**
         * Binds callable to object
         * Name may be an key pair array of callables
         * @param object $object
         * @param mixed $name
         * @param callable $callback
         * @throws persistr\Exception
         */
        public static function bindTo($object, $name, callable $callback = null) {
            if(is_null($callback) && is_array($name)) {
                $bindings = $name;
                foreach($bindings as $name => $callback) {
                    self::bind($object, $name, $callback);
                }
            }

            if(is_null($callback)) {
                throw new persistr\Exception('Empty callback, binding invalid');
            }

            if(!is_string($name)) {
                throw new persistr\Exception('Empty name, binding invalid');
            }

            $uid = self::identify($object);

            if(is_string($uid)) {
                if(!array_key_exists($uid, self::$bindings)) {
                    self::$bindings[$uid] = [];
                }

                self::$bindings[$uid][$name] = $callback;
            }
        }

        /**
         * Retrieves bindings by object
         * @param object $object
         * @return array
         */
        public static function getBindings($object) {
            $uid = self::identify($object);

            if(array_key_exists($uid, self::$bindings)) {
                return self::$bindings[$uid];
            }
        }

        /**
         * Checks if object binds property
         * @param object $object
         * @param string $name
         * @return boolean
         */
        public static function isBound($object, $name) {
            $uid = self::identify($object);

            if($uid === false) {
                return false;
            }

            if(array_key_exists($uid, self::$bindings) &&
               array_key_exists($name, self::$bindings[$uid])) {
                return true;
            }

            return false;
        }
    }
}