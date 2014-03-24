<?php
namespace persistr\Tests {
    use persistr;
    
    class PersistenceTest extends persistrTestCase {
        
        protected $datasource;

        function testObjectPersistCreate() {
            $object = new Mock\MockPersistedObject();
            
            $this->assertInstanceOf('persistr\Tests\Mock\MockPersistedObject',$object);
        }
        
        function testObjectBindingClosure() {
            $object = new Mock\MockPersistedObject();
            
            $model = $object->getModel();
            
            $model->bindTo($object,'foo',function() {
                return 'bar';
            });
            
            $closure = persistr\Object\Binding\Registry::getBinding($object,'foo');
            
            $this->assertInstanceOf('Closure',$closure);
        }
        
        function testObjectBinding() {
            $object = new Mock\MockPersistedObject();
            
            $model = $object->getModel();
            
            $model->bindTo($object,'foo',function() {
                return 'bar';
            });
            
            $isBound = persistr\Object\Binding\Registry::isBound($object,'foo');
            
            $this->assertEquals(true,$isBound);
        }
               
        function testObjectLocalLazyBinding() {
            $object = new Mock\MockPersistedObject();
            
            $model = $object->getModel();
            
            $model->bindTo($object,'foo',function() {
                return 'bar';
            });
            
            $value = $object->foo;
            
            $this->assertEquals('bar',$value);
        }
        
        function testObjectGlobalLazyBinding() {
            $object = new Mock\MockPersistedObject();
            
            $model = $object->getModel();
            
            $model->bind('foo',function() {
                return 'bar';
            });
            
            $value = $object->foo;
            
            $this->assertEquals('bar',$value);
        }
    }
}