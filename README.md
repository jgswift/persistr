persistr
====
PHP 5.5+ lightweight persistence layer

[![Build Status](https://travis-ci.org/jgswift/persistr.png?branch=master)](https://travis-ci.org/jgswift/persistr)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/jgswift/persistr/badges/quality-score.png?s=96ef5b2c0baacd1db5f5bbccd23057de138f2822)](https://scrutinizer-ci.com/g/jgswift/persistr/)

## Installation

Install via [composer](https://getcomposer.org/):
```sh
php composer.phar require jgswift/persistr:dev-master
```

## Usage

persistr is a lightweight php package which implements a loose persistence layer.

persistr does not necessarily use annotations or otherwise any kind of model metadata when defining models. However, that does not preclude the inclusion of a formal modeling component

Additionally, persistr Models may be solely relied on for any data source transactions.  It is typically bad practice to put platform-dependant code in the Persistent interface implementation.  Such code is only appropriate on the model itself.

persistr assumes you know how to interact with your respective data-source and is not a full-on database abstraction layer

persistr makes the distinction between persistence implementations for objects based on signature criteria such as the presence of traits, interfaces, or even simply an individual class name alone

When persistence is applied to an individual class, all inheriting classes naturally have the same extensions by default

The following is a default example with a blank model using an interface/trait pair signature (the interface is the distinct element by default)
```php
<?php
class MyUser implements persistr\Interfaces\Persistent {
    use persistr\Persistent;

}

$user = new MyUser;

$model = $user->getModel();

$model->bind('foo',function() {
    return 'bar';
});

$value = $user->foo;

var_dump($value); // returns 'bar'
```

It is not necessary to specify the interface on class above, as the signature is already recognized simply given a trait.  Multiple identification techniques are available, namely ```trait```, ```interface```, ```class```.

Below is an example of setting up a custom class-based persistence interface.

First we start by defining a model class, input/output filters, and finally register it to the persistence layer.

```php
// MODEL CLASS
class MyUserModel implements persistr\Interfaces\Model {
    private $className;
    private static $registry;

    function __construct($className) {
        $this->className = $className;
        if(empty(self::$registry)) {
            self::$registry = new persistr\Object\Registry($this,$className);
        }
    }

    public function getClassName() {
        return $this->className;
    }

    public function getRegistry() {
        return self::$registry;
    }

    public function bind($attribute, callable $callable=null) {
        persistr\Object\Binding\Registry::bind(self::$registry->getTypeName(), $attribute, $callable);
        return $this;
    }

    public function bindTo($object,$attribute,callable $callable=null) {
        persistr\Object\Binding\Registry::bindTo($object, $attribute, $callable);
        return $this;
    }
}

// PERSISTENCE REGISTRATION
$persistor = new persistr\Persistor('MyUser');
persistr\Registry::register($persistor);

$model = new MyUserModel('MyUser');

$persistor->getDataSource()->insert('MyUser',$model);
```

Now when a MyUser object is instantiated, the given MyUserModel model will be used to map the object.