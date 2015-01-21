<?php
namespace persistr\Tests {
    use persistr, qtil;
    
    class NonfilteredPersistenceTest extends PersistenceTest {
        
        protected function setUp()
        {
            $persistor = new persistr\Persistor('persistr\Tests\Mock\MockPersistedObject');
            persistr\Registry::register($persistor);
            
            $modelClass = 'persistr\Tests\Mock\MockPersistedObject';
            $mockModel = new Mock\MockModel($modelClass);
            
            $persistor->getDataSource()->insert($modelClass,$mockModel);
        }

        protected function tearDown()
        {
            persistr\Registry::clearPersistors();
        }
    }
}