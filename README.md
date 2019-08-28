# Extra for Symfony Dependency Injection Component

## Deprecated

Please use [paysera/lib-dependency-injection](https://github.com/paysera/lib-dependency-injection) which provides more features than this library.

## What's that?

Extra features for easier integration with Symfony Dependency Injection component.

Currently contains only compiler passes for registering tagged services with some another service - no need to write
custom class in each and every case.

### `AddTaggedCompilerPass`

To register tagged services to some other service. Optionally passes attributes of the tag, too. 

```php
class SomeBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new AddTaggedCompilerPass(
            'some_bundle.registry',
            'my_provider',
            'addProvider',
            array(      // this parameter is optional and defines attributes to pass from tag
                'name',                 // required attribute
                'theme' => 'default',   // optional attribute
            )
        ));
    }
}
```

```php
class Registry
{
    // first - tagged service. Others (optional) in the order as they come in the attributes array
    public function addProvider(ProviderInterface $provider, $name, $theme)
    {
        $this->providers[$name] = $provider;    // or whatever
    }
}
```

```xml
<service id="awesome_provider" class="Acme/AwesomeProvider">
    <tag name="my_provider" name="awesome"/>
</service>
<service id="nice_provider" class="Acme/NiceProvider">
    <tag name="my_provider" name="nice" theme="not a default one"/>
</service>
```

### `AddTaggedIdCompilerPass`

Same, but first parameter to the method is not the service itself, but it's ID.

Gives some laziness if there are many tagged services and you don't use them all quite often.

You should probably stick to using lazy services, though.

### `AddTaggedByPriorityCompilerPass`

Same as `AddTaggedCompilerPass`, but calls the method in the order defined in the `priority` attribute.

Attribute to use can be changed with `$compilerPass->setPriorityAttribute('some_other')`.

Lower the priority, earlier the call.

If priority is not provided, defaults to `0`.

```xml
<service id="awesome_provider" class="Acme/AwesomeProvider">
    <tag name="my_provider" name="awesome" priority="9001"/>
</service>
<service id="nice_provider" class="Acme/NiceProvider">
    <tag name="my_provider" name="nice" theme="not a default one" priority="-1"/>
</service>
<service id="another_provider" class="Acme/AnotherProvider">
    <tag name="my_provider" name="another"/>
</service>
```

Resolves to:

```php
$registry->addProvider($niceProvider, 'nice', 'not a default one'); // priority -1 - smallest
$registry->addProvider($anotherProvider, 'another', 'default');     // priority defaults to 0
$registry->addProvider($awesomeProvider, 'awesome', 'default');     // priority is over 9000
```

### Restrictions

Does not work well with several same tags on single service. Example:

```xml
<service id="awesome_provider" class="Acme/AwesomeProvider">
    <tag name="my_provider" name="awesome"/>
    <tag name="my_provider" name="this will not be processed"/>
</service>
```

## Installing

```
composer require maba/dependency-injection-extra
```

## Running tests

[![Travis status](https://travis-ci.org/mariusbalcytis/dependency-injection-extra.svg?branch=master)](https://travis-ci.org/mariusbalcytis/dependency-injection-extra)
[![Coverage Status](https://coveralls.io/repos/mariusbalcytis/dependency-injection-extra/badge.svg?branch=master&service=github)](https://coveralls.io/github/mariusbalcytis/dependency-injection-extra?branch=master)

```
composer update
vendor/bin/phpunit
```
