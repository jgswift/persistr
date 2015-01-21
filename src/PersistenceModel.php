<?php
namespace persistr {
    use qtil;
    
    class PersistenceModel {
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
         * @param mixed $datasource
         * @param mixed $datafilter
         * @param mixed $outputfilter
         */
        public function __construct($datasource = null, $datafilter=null, $outputfilter=null) {
            if(is_null($datasource)) {
                $datasource = new qtil\Collection;
            }
            
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
         * Array containing persistence models
         * @return array
         */
        function getDataSource() {
            return $this->datasource;
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