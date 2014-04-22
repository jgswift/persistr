<?php
namespace persistr {
    use qtil;
    
    class Registry extends qtil\Registry {   
        /**
         * List of persisted objects
         * @var array 
         */
        private static $persisted = [];
        
        /**
         * List of persistence models
         * @var array 
         */
        private static $models = [];
        
        /**
         * List of persistors
         * @var array 
         */
        private static $persistors = [];

        /**
         * Main persistence method, all new persisted objects must execute this
         * in the constructor
         * @param mixed $object
         */
        public static function persist($object) {
            self::setup($object);
        }

        /**
         * Retrieves persistor by class
         * @param mixed $className
         * @return mixed
         * @throws \InvalidArgumentException
         */
        public static function persistor($className) {
            if($className instanceof Interfaces\Model) {
                $className = $className->getClassName();
            } elseif(is_object($className)) {
                $className = get_class($className);
            } elseif(!is_string($className)) {
                throw new \InvalidArgumentException();
            }

            return self::$persisted[$className];
        }

        /**
         * Checks if class is persisted
         * @param string $className
         * @return boolean
         */
        public static function persisted($className) {
            return array_key_exists($className,self::$persisted);
        }

        /**
         * Factory method to add persistence layer to class, interface, or trait
         * @param string $sourceAsset
         * @return \persistr\Persistor
         */
        public static function persistClass($sourceAsset,Persistor $persistor=null) {
            foreach(self::$persistors as $p) {
                if($p->isTrait() && $p->using($sourceAsset)) {
                    $persistor = $p;
                } elseif($p->isClass() && $p->extending($sourceAsset)) {
                    $persistor = $p;
                } elseif($p->isInterface() && $p->implementing($sourceAsset)) {
                    $persistor = $p;
                }
            }
            
            if($persistor instanceof Persistor) {
                self::$persisted[$sourceAsset] = $persistor;
                return $persistor;
            }
        }
        
        /**
         * Helper method
         * Retrieves persistor or builds default filter
         * @param string $className
         */
        public static function getPersistorByName($className) {
            if(self::persisted($className)) {
                $persistor = self::persistor($className);
            } else {
                $persistor = self::persistClass($className);
            }
            
            if(is_null($persistor)) {
                $persistor = self::createBlankPersistor($className);
            }
            
            return $persistor;
        }

        /**
         * Helper function
         * Retrieves persistence model by class name
         * @param mixed $className
         * @return persistr\Interfaces\Model
         * @throws Exception
         */
        public static function getModelByName($className) {
            $persistor = self::getPersistorByName($className);
            
            if($persistor instanceof Persistor) {
                $datasource = $persistor->getDataSource();
                $model = $datasource[$className];
            } else {
                throw new Exception('Persistor invalid ("'.$className.'")');
            }
            
            if(empty($model)) {
                if(@class_exists($className)) {
                    $parents = class_parents($className);

                    if(!empty($parents)) {
                        return self::getModelByName($parents[array_keys($parents)[0]]);
                    } else {
                        throw new Exception('Model not found for object type("'.$className.'")');
                    }
                }
            }
            
            return $model;
        }
        
        /**
         * Helper method to create blank model if none found
         * @param string $className
         * @return persistr\Model\Blank
         */
        private static function createBlankPersistor($className) {
            $datasource = new qtil\Collection();

            $persistor = new Persistor($className, $datasource);
            self::register($persistor);

            $model = new Model\Blank($className);

            $datasource->insert($className,$model);
            
            return $persistor;
        }

        /**
         * Helper function
         * Retrieves data filters by model
         * @param persistr\Interfaces\Model $model
         * @param persistr\Object\Registry $registry
         * @return array
         */
        private static function getDataFiltersByModel(Interfaces\Model $model, persistr\Object\Registry $registry = null) {
            $className = $model->getClassName();

            if(self::persisted($className)) {
                $persistor = self::persistor($className);
            } else {
                $persistor = self::persistClass($className);
            }

            if(is_null($registry)) {
                $registry = $model->getRegistry();
            }

            $filter = null;
            if(!$registry->hasDataFilters() && $persistor->hasDataFilters()) {
                $filters = $persistor->getDataFilters();
                foreach($filters as $filter) {
                    if(is_object($filter)) {
                        $registry->addDataFilter(clone $filter);
                    }
                }
            } else {
                $filter = $registry->getDataFilters();
            }

            return $filter;
        }

        /**
         * Performs setup method to prepare object for persistence
         * @param mixed $object
         * @throws Exception
         */
        public static function setup($object) {
            $fullClassName = get_class($object);

            $model = self::getModelByName($fullClassName);
            
            if(!is_null($model)) {
                $objectRegistry = $model->getRegistry();
                $objectRegistry->addInstance($object);
                
                if($objectRegistry->hasDataFilters()) {
                    $filters = self::getDataFiltersByModel($model);

                    self::filter($filters,$object);
                }
            } else {
                throw new Exception('Model not found : object ('.$fullClassName.').');
            }
        }
        
        /**
         * Helper method
         * Performs initialization filtering
         * Defaults to blank object
         * @param array $filters
         * @param mixed $object
         */
        protected static function filter(array $filters,$object) {
            if(!empty($filters)) {
                foreach($filters as $filter) {
                    if(\is_callable($filter)) {
                        $filter([],$object);
                    }
                }
            }
        }

        /**
         * Adds persistor to registry
         * @param \persistr\Persistor $persistor
         */
        public static function register(Persistor $persistor) {
            $sourceAsset = $persistor->getSourceAsset();
            
            self::$persistors[$sourceAsset] = $persistor;
        }
        
        /**
         * Removes all persistors from registry
         */
        public static function clearPersistors() {
            self::$persistors = [];
            self::$models = [];
            self::$persisted = [];
        }

        /**
         * Removes all references in registery to object
         * @param mixed $object
         */
        public static function free($object) {
            $filter = self::modelRegistry($object)->filter();

            if(!is_null($filter)) {
                $filter->free($object);
            }
        }

        /**
         * Helper function
         * Retrieves object registry by model
         * @param mixed $object
         * @return persistr\Object\Registry
         */
        private static function modelRegistry($object) {
            $model = self::getModel($object);
            return $model->getRegistry();
        }

        /**
         * Checks if object has data filters
         * @param mixed $object
         * @return boolean
         */
        public static function filtered($object) {
            $model = self::getModel($object);
            if(is_null($model)) {
                return false;
            }

            $registry = $model->getRegistry();
            if(is_null($registry)) {
                return false;
            }

            return ($registry->hasDataFilters());
        }

        /**
         * Helper method
         * Retrieves persistence model by object
         * @param mixed $object
         * @return persistr\Interfaces\Model
         * @throws Exception
         */
        public static function getModel($object) {
            $className = get_class($object);
            if(isset(self::$models[$className])) {
                return self::$models[$className];
            }

            $model = self::getModelByName($className);

            if(!is_null($model)) {
                self::setModel($object,$model);
                return $model;
            }
        }

        /**
         * Sets persistence model for object
         * newModel may be model itself or the models absolute name
         * @param mixed $object
         * @param mixed $newModel
         */
        public static function setModel($object, $newModel) {
            $className = get_class($object);

            if(is_string($newModel)) {
                $newModel = self::getModelByName($newModel);
            }

            self::$models[$className] = $newModel;
        }
    }
}

