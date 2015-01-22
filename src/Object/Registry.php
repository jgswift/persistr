<?php
namespace persistr\Object {
    use persistr, kfiltr, qtil;
    
    class Registry extends qtil\Registry {
        use kfiltr\Hook, qtil\Factory;

        /**
         * List of original objects registered
         * @var array 
         */
        protected $cache = [];
        
        /**
         * List of objects registered current state
         * @var array 
         */
        protected $instances = [];
        
        /**
         * List of object keys
         * @var array 
         */
        protected $keys = [];
        
        /**
         * Name of class objects are persisted by
         * @var string 
         */
        protected $typeName;
        
        /**
         * Instance of persistor
         * @var persistr\Persistor 
         */
        protected $persistor;
        
        /**
         * List of callable data filters
         * @var array 
         */
        protected static $datafilters = [];
        
        /**
         * List of callable output filters
         * @var array 
         */
        protected static $outputfilters = [];
        
        /**
         * Constructor for empty object registry
         * @param mixed $model
         * @param string $typeName
         * @throws persistr\Exception
         */
        public function __construct($model, $typeName) {
            $typeName = str_replace('/','\\',$typeName);
            $this->typeName = $typeName;

            if($model instanceof persistr\Model\Blank) {
                return;
            }
            
            if(persistr\Registry::persisted($typeName)) {
                $persistor = persistr\Registry::persistor($typeName);
            } else {
                $persistor = persistr\Registry::getPersistorByName($typeName);
            }

            if($persistor instanceof persistr\Persistor) {
                $this->persistor = $persistor;

                $dataFilters = [];
                $outputFilters = [];

                if($persistor->hasDataFilters()) {
                    $dataFilterClasses = $persistor->getDataFilters();
                    foreach($dataFilterClasses as $filterClass) {
                        if(@class_exists($filterClass)) {
                            $filter = new $filterClass($typeName);
                            $dataFilters[] = $filter;
                        }
                    }
                }

                if($persistor->hasOutputFilters()) {
                    $outputFilterClasses = $persistor->getOutputFilters();

                    if(is_array($outputFilterClasses)) {
                        foreach($outputFilterClasses as $filterClass) {
                            if(@class_exists($filterClass)) {
                                $filter = new $filterClass($typeName);
                                $outputFilters[] = $filter;
                            }
                        }
                    }
                }

                self::$datafilters[$typeName] = $dataFilters;
                self::$outputfilters[$typeName] = $outputFilters;
            } else {
                throw new persistr\Exception('Class not persisted, cannot instantiate '.$typeName);
            }
        }

        /**
         * Helper function to get persistor data filters by type name
         * @param string $typeName
         * @return array an array of callable filters
         */
        static function getDataFiltersByType($typeName) {
            if(array_key_exists($typeName,self::$datafilters)) {
                return self::$datafilters[$typeName];
            }
        }

        /**
         * Helper function to get persistor output filters by type name
         * @param string $typeName
         * @return array an array of callable output filters
         */
        static function getOutputFiltersByType($typeName) {
            if(array_key_exists($typeName,self::$outputfilters)) {
                return self::$outputfilters[$typeName];
            }
        }

        /**
         * Retrieves callable data filters by object
         * @param object $object
         * @return array
         */
        function getDataCallbacks($object=null) {
            $isObject = is_object($object);
            $typeName = $this->typeName;
            $default = function($data = null) use ($typeName, $isObject) {
                $filters = self::getDataFiltersByType($typeName);
                $result = null;
                foreach($filters as $filter) {
                    if($isObject) {
                        $result = $filter($data,$this);
                    } else {
                        $result = $filter($data);
                    }
                }

                return $result;
            };

            if(is_object($object)) {
                return $default->bindTo($object);
            }

            return $default;
        }

        /**
         * Retrieves callable output filters by object
         * @param mixed $object
         * @return mixed
         */
        function getOutputCallbacks($object=null) {
            $typeName = $this->typeName;
            $output = function($value = null) use($typeName) {
                $filters = self::getOutputFiltersByType($typeName);
                $result = null;

                foreach($filters as $filter) {
                    $result = $filter($value, $this);
                }
                
                return $result;
            };

            if(is_object($object)) {
                return $output->bindTo($object);
            }
            return $output;
        }

        /**
         * Retrieves registry persistor
         * @return persistr\Persistor
         */
        function getPersistor() {
            return $this->persistor;
        }

        /**
         * Retrieves registry data filters
         * @return array
         */
        function getDataFilters() {
            return self::$datafilters[$this->typeName];
        }

        /**
         * Retrieves registry output filters
         * @return type
         */
        function getOutputFilters() {
            return self::$outputfilters[$this->typeName];
        }

        /**
         * adds data filter or array of data filters to registry
         * @param mixed $datafilter
         */
        function addDataFilter($datafilter) {

            if(is_array( $datafilter)) {
                self::$datafilters[$this->typeName] = array_merge(self::$datafilters[$this->typeName], $datafilter);
            } else {
                self::$datafilters[$this->typeName][] = $datafilter;
            }
        }

        /**
         * adds data filter or array of output filters to registry
         * @param mixed $outputfilter
         */
        function addOutputFilter($outputfilter) {
            if(is_array($outputfilter)) {
                self::$outputfilters[$this->typeName] = array_merge(self::$outputfilters[$this->typeName], $outputfilter);
            } else {
                self::$outputfilters[$this->typeName][] = $outputfilter;
            }
        }

        /**
         * 
         * @param array $datafilters
         */
        function setDataFilters(array $datafilters) {
            self::$datafilters[$this->typeName] = $datafilters;
        }

        /**
         * 
         * @param array $outputfilters
         */
        function setOutputFilters(array $outputfilters) {
            self::$outputfilters[$this->typeName] = $outputfilters;
        }

        /**
         * Checks if persistence layer has data filters
         * @return boolean
         */
        function hasDataFilters() {
            return array_key_exists($this->typeName,self::$datafilters) && (count(self::$datafilters[$this->typeName]) > 0);
        }

        /**
         * Checks if persistence layer has output filters
         * @return boolean
         */
        function hasOutputFilters() {
            return array_key_exists($this->typeName,self::$outputfilters) && (count(self::$outputfilters[$this->typeName]) > 0);
        }

        /**
         * Retrieves name of type
         * @return string
         */
        function getTypeName() {
            return $this->typeName;
        }

        /**
         * Retrieves all known primary key values
         * @return array
         */
        function getKeys() {
            return $this->keys;
        }

        /**
         * Retrieves primary key value by object uid
         * @param string $uid
         * @return string
         */
        function getKey($uid) {
            return array_search($uid, $this->keys);
        }

        /**
         * adds key to primary key list
         * @param mixed $key
         * @param string $uid
         */
        function addKey($key, $uid) {
            $this->keys[$key] = $uid;
        }

        /**
         * Retrieves object instance by uid
         * @param string $uid
         * @return mixed
         */
        function getInstance($uid) {
            if(!isset($this->instances[$uid])) {
                return null;
            }

            return $this->instances[$uid];
        }

        /**
         * Add instance to persistence layer
         * @param mixed $object
         * @throws persistr\Exception
         */
        function addInstance($object) {
            if(!($object instanceof $this->typeName)) {
                throw new persistr\Exception('Cannot store instance of '. get_class( $object ).' in manager for '.$this->typeName );
            }
            
            $uid = self::identify($object);
            
            $this->instances[$uid] = &$object;
        }
        
        /**
         * Retrieve all instances of object persisted by layer
         * @return array
         */
        function getInstances() {
            return $this->instances;
        }

        /**
         * Adds copy of object to backup for later compariosn
         * @param mixed $object
         * @param string $uid
         */
        function addCopy($object, $uid) {
            $cacheObject = (array)$object;

            foreach($cacheObject as $k => $v) {
                $k = trim($k);
                if(strpos($k,'*') === 0) {
                    unset($cacheObject[$k]);
                    $k = trim(substr($k,1));
                    $cacheObject[$k] = $v;
                }
            }
            
            $this->cache[$uid] = (array)$cacheObject;
        }

        /**
         * Retrieves registered object by uid
         * @param string $uid
         * @return mixed
         */
        function getCopy($uid) {
            if(is_null($uid)) {
                return null;
            }

            return $this->cache[$uid];
        }

        /**
         * Checks if object uid is registered
         * @param string $uid
         * @return boolean
         */
        function hasCopy($uid) {
            return array_key_exists($uid, $this->cache);
        }

        /**
         * Removes all references to object in persistence system
         * @param mixed $object
         */
        function free($object) {
            if(array_key_exists($object, $this->cache)) {
                unset($this->cache[$object]);
            } elseif(is_object($object)) {
                $uid = self::identify($object);
                $this->free($uid);
            }
        }
    }
}
