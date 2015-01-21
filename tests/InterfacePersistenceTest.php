<?php
namespace persistr\Tests {
    use persistr, qtil;
    
    class InterfacePersistenceTest extends PersistenceTest {
        
        protected function setUp()
        {
            $datafilter = new Mock\MockDataFilter();
            $outputfilter = new Mock\MockOutputFilter();
            
            $this->datasource = $datasource = new qtil\Collection();
            
            $persistenceModel = new \persistr\PersistenceModel($datasource,$datafilter,$outputfilter);
            
            $persistor = new persistr\Persistor('persistr\Interfaces\Persistent', $persistenceModel);
            persistr\Registry::register($persistor);
            
            $modelClass = 'persistr\Tests\Mock\MockPersistedObject';
            $mockModel = new Mock\MockModel($modelClass);
            
            $this->datasource->insert($modelClass,$mockModel);
        }

        protected function tearDown()
        {
            persistr\Registry::clearPersistors();
        }
    }
}