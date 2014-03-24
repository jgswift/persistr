<?php
namespace persistr\Interfaces {
    interface Model {
        /**
         * Returns absolute path of class this model models
         */
        function getClassName();
        
        /**
         * Retrieves object registry that handles managing this object in memory
         */
        function getRegistry();
        
        /**
         * Binds callable to local object property
         * @param string $attribute
         * @param callable $callable
         */
        function bind($attribute,callable $callable=null);
        
        /**
         * Binds callable to global object property
         * @param object $object
         * @param string $attribute
         * @param callable $callable
         */
        function bindTo($object,$attribute,callable $callable=null);
    }
}
