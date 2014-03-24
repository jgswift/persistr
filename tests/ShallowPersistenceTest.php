<?php
namespace persistr\Tests {
    use persistr;
    
    class ShallowPersistenceTest extends PersistenceTest {
        
        protected function setUp()
        {
            
        }

        protected function tearDown()
        {
            persistr\Registry::clearPersistors();
        }
    }
}