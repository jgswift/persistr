<?php
namespace persistr\Tests {
    use persistr, qtil;
    
    class ClassPersistenceTest extends PersistenceTest {
        
        protected function setUp()
        {
            $datafilter = new Mock\MockDataFilter();
            $outputfilter = new Mock\MockOutputFilter();
            
            $this->datasource = $datasource = new qtil\Collection();
            
            $persistor = new persistr\Persistor('persistr\Tests\Mock\MockPersistedObject', $datasource, $datafilter, $outputfilter);
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