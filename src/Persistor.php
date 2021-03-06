<?php
namespace persistr {
    use qtil;
    
    class Persistor {
        /**
         * May be interface, class, or trait absolute name
         * @var mixed
         */
        private $sourceAsset;
        
        /**
         * Local persistence model storage
         * @var \persistr\PersistenceModel
         */
        private $persistenceModel;
        
        /**
         * Persistor constructor
         * 
         * Both filter arguments can accept either a single filter or an array of filters
         * @param string $sourceAsset
         * @param \persistr\PersistenceModel $model
         */
        public function __construct($sourceAsset, PersistenceModel $model = null) {
            $this->sourceAsset = $sourceAsset;

            if(is_null($model)) {
                $model = new PersistenceModel();
            }
            $this->persistenceModel = $model;
        }
        
        /**
         * Retrieves source asset, may be an interface, trait, or class absolute name
         * @return string
         */
        function getSourceAsset() {
            return $this->sourceAsset;
        }
        
        /**
         * Array containing persistence models
         * @return array
         */
        function getDataSource() {
            return $this->persistenceModel->getDataSource();
        }
        
        /**
         * Checks if class is used by sourceAsset trait
         * 
         * @param mixed $sourceAsset
         * @return boolean
         */
        public function using($sourceAsset) {
            if(is_object($sourceAsset)) {
                $sourceAsset = get_class($sourceAsset);
            }

            return qtil\ReflectorUtil::classUses($sourceAsset, $this->sourceAsset);
        }
        
        /**
         * Checks if class is extending sourceAsset class
         * @param string $sourceAsset
         * @return boolean
         */
        public function extending($sourceAsset) {
            return in_array($this->sourceAsset,class_parents($sourceAsset)) || $sourceAsset === $this->sourceAsset;
        }
        
        /**
         * Checks if class is implement sourceAsset interface
         * @param string $sourceAsset
         * @return boolean 
         */
        public function implementing($sourceAsset) {
            return in_array($this->sourceAsset,class_implements($sourceAsset));
        }
        
        /**
         * Checks if sourceAsset is class
         * @return boolean
         */
        public function isClass() {
            return class_exists($this->sourceAsset) ? true : false;
        }
        
        /**
         * Checks if sourceAsset is interface
         * @return boolean
         */
        public function isInterface() {
            return interface_exists($this->sourceAsset) ? true : false;
        }
        
        /**
         * Checks if sourceAsset is trait
         * @return boolean
         */
        public function isTrait() {
            return trait_exists($this->sourceAsset) ? true : false;
        }

        /**
         * Access list of output filters
         * @return array
         */
        public function getOutputFilters() {
            return $this->persistenceModel->getOutputFilters();
        }

        /**
         * Access list of data filters
         * @return array
         */
        public function getDataFilters() {
            return $this->persistenceModel->getDataFilters();
        }
        
        /**
         * Checks if persistor has any data filters
         * @return boolean
         */
        public function hasDataFilters() {
            return $this->persistenceModel->hasDataFilters();
        }
        
        /**
         * Checks if persistor has any output filters
         * @return boolean
         */
        public function hasOutputFilters() {
            return $this->persistenceModel->hasOutputFilters();
        }
    }
}

