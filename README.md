persistr
====
PHP 5.5+ lightweight persistence layer

[![Build Status](https://travis-ci.org/jgswift/persistr.png?branch=master)](https://travis-ci.org/jgswift/persistr)

## Installation

Install via [composer](https://getcomposer.org/):
```sh
php composer.phar require jgswift/persistr:dev-master
```

## Usage

persistr is a lightweight php package which implements a loose persistence layer somewhat similar to A/R

persistr does not necessarily use annotations or otherwise any kind of model metadata when defining models. However, that does not preclude the inclusion of a formal modeling component

Additionally, persistr Models may be solely relied on for any data source transactions.  It is typically bad practice to put platform-dependant code in the Persistent interface implementation.  Such code is only appropriate on the model itself.

persistr assumes you know how to interact with your respective data-source and is not a full-on database abstraction layer

persistr makes the distinction between persistence implementations for objects based on signature criteria such as the presence of traits, interfaces, or even simply an individual class name alone

When persistence is applied to an individual class, all inheriting class naturally have the same extensions by default

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