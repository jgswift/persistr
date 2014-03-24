<?php
namespace persistr\Interfaces {
    interface Persistent {
        /**
         * method to retrieve model used for object persistence
         */
        public function getModel();
        
        /**
         * method to set model used by object in persistence layer
         * @param \persistr\Interfaces\Model $model
         */
        public function setModel(Model $model);
    }
}
