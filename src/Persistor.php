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
         * Data source from which to obtain model definitions
         * @var mixed 
         */
        private $datasource;
        
        /**
         * List of data filters used by persistence interface
         * @var mixed 
         */
        private $datafilter;
        
        /**
         * List of output filters used by persistence interface
         * @var array 
         */
        private $outputfilter;

        /**
         * Persistor constructor
         * 
         * Both filter arguments can accept either a single filter or an array of filters
         * @param string $sourceAsset
         * @param mixed $datasource
         * @param mixed $datafilter
         * @param mixed $outputfilter
         */
        public function __construct($sourceAsset, $datasource, $datafilter=null, $outputfilter=null) {
            $this->sourceAsset = $sourceAsset;

            if(qtil\ArrayUtil::isIterable($datasource)) {
                $this->datasource = $datasource;
            }

            if(!is_null($datafilter) && !qtil\ArrayUtil::isIterable($datafilter)) {
                $datafilter = [$datafilter];
            }

            if(!is_null($outputfilter) && !qtil\ArrayUtil::isIterable($outputfilter)) {
                $outputfilter = [$outputfilter];
            }

            $this->datafilter = $datafilter;
            $this->outputfilter = $outputfilter;
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
            return $this->datasource;
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
            return @class_exists($this->sourceAsset) ? true : false;
        }
        
        /**
         * Checks if sourceAsset is interface
         * @return boolean
         */
        public function isInterface() {
            return @interface_exists($this->sourceAsset) ? true : false;
        }
        
        /**
         * Checks if sourceAsset is trait
         * @return boolean
         */
        public function isTrait() {
            return @trait_exists($this->sourceAsset) ? true : false;
        }

        /**
         * Access list of output filters
         * @return array
         */
        public function getOutputFilters() {
            return $this->outputfilter;
        }

        /**
         * Access list of data filters
         * @return array
         */
        public function getDataFilters() {
            return $this->datafilter;
        }
        
        /**
         * Checks if persistor has any data filters
         * @return boolean
         */
        public function hasDataFilters() {
            return !empty($this->datafilter);
        }
        
        /**
         * Checks if persistor has any output filters
         * @return boolean
         */
        public function hasOutputFilters() {
            return !empty($this->outputfilter);
        }
    }
}

