# DTO

[![Author][ico-author]][link-author]
[![PHP Version][ico-php]][link-php]
[![Build Status][ico-actions]][link-actions]
[![Coverage Status][ico-scrutinizer]][link-scrutinizer]
[![Quality Score][ico-code-quality]][link-code-quality]
[![Latest Version][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![PSR-12][ico-psr12]][link-psr12]
[![Total Downloads][ico-downloads]][link-downloads]

This package was inspired by [Lachlan Krautz][link-lachlan]' excellent [data-transfer-object][link-repo].

A data transfer object (DTO) is an object that carries data between processes. DTO does not have any behaviour except for storage, retrieval, serialization and deserialization of its own data. DTOs are simple objects that should not contain any business logic but rather be used for transferring data.

## Install

Via Composer

``` bash
composer require cerbero/dto
```

## Usage

* [Instantiate a DTO](#instantiate-a-dto)
* [Declare properties](#declare-properties)
* [Default values](#default-values)
* [Interact with values](#interact-with-values)
* [Available flags](#available-flags)
   * [NONE](#none)
   * [IGNORE_UNKNOWN_PROPERTIES](#ignore_unknown_properties)
   * [MUTABLE](#mutable)
   * [PARTIAL](#partial)
   * [CAST_PRIMITIVES](#cast_primitives)
   * [CAMEL_CASE_ARRAY](#camel_case_array)
* [Default flags](#default-flags)
* [Interact with flags](#interact-with-flags)
* [Manipulate properties](#manipulate-properties)
* [Interact with properties](#interact-with-properties)
* [Convert into array](#convert-into-array)
* [Listen to events](#listen-to-events)
* [Convert into string](#convert-into-string)

### Instantiate a DTO

DTOs can be instantiated like normal classes or via the factory method `make()`. The parameters are optional and include the data to carry and the flags that dictate how the DTO should behave:

``` php
use const Cerbero\Dto\PARTIAL;

$data = [
    'name' => 'John',
    'address' => [
        'street' => 'King Street',
    ],
];

$dto = new SampleDto($data, PARTIAL);

$dto = SampleDto::make($data, PARTIAL);
```

In the example above, `$data` is an array containing the properties declared in the DTO and `PARTIAL` is a flag that let the DTO be instantiated even though it doesn't have all its properties set (we will see flags in more detail later).

Keys in the array `$data` can be either snake case or camel case, the proper case is automatically detected to match DTO properties.


### Declare properties

Properties can be declared in a DTO by using doc comment tags:

```php
use Cerbero\Dto\Dto;
use Sample\Dtos\AddressDto;

/**
 * A sample user DTO.
 *
 * @property string $name
 * @property bool $isAdmin
 * @property mixed $something
 * @property \DateTime|null $birthday
 * @property UserDto[] $friends
 * @property AddressDto $address
 */
class UserDto extends Dto
{
    //
}
```

Either `@property` or `@property-read` can be used, followed by the expected data type and the desired property name. When expecting more than one type, we can separate them with a pipe `|` character, e.g. `\DateTime|null`.

A collection of types can be declared by adding the suffix `[]` to the data type, e.g. `UserDto[]`. It's important to declare the fully qualified name of classes, either in the doc comment or as a `use` statement.

Primitive types can be specified too, e.g. `string`, `bool`, `int`, `array`, etc. The pseudo-type `mixed` allow any type.


### Default values

While values can be set when instatiating a DTO, default values can also be defined in the DTO class:

```php
use Cerbero\Dto\Dto;

/**
 * A sample user DTO.
 *
 * @property string $name
 */
class UserDto extends Dto
{
    protected static $defaultValues = [
        'name' => 'John',
    ];
}

// $user1->name will return: John
$user1 = new UserDto();

// $user2->name will return: Jack
$user2 = new UserDto(['name' => 'Jack']);
```

Please note that in the above example default values are overridden by the values passed during the DTO creation.

### Interact with values

DTO property values can be accessed in several ways, but a `Cerbero\Dto\Exceptions\UnknownDtoPropertyException` is thrown if a requested property is not set:

```php
// as an object
$user->address->street;

// as an array
$user['address']['street'];

// via dot notation
$user->get('address.street');

// via nested DTO
$user->address->get('street');
```

To check whether properties have a value, the following methods can be called:

```php
// as an object
isset($user->address->street);

// as an array
isset($user['address']['street']);

// via dot notation
$user->has('address.street');

// via nested DTO
$user->address->has('street');
```

Please note that the above methods will return FALSE also if the property value is set to NULL (just like the default PHP behaviour). To check whether a property has actually been set, we can call `$user->hasProperty('address.street')` (we will see properties in more details later).

The outcome of setting a value depends on the flags set in a DTO. DTOs are immutable by default, so a new instance gets created when setting a value. Values can be changed in the same DTO instance only if a `MUTABLE` flag is set:

```php
// throw Cerbero\Dto\Exceptions\ImmutableDtoException if immutable
$user->address->street = 'King Street';

// throw Cerbero\Dto\Exceptions\ImmutableDtoException if immutable
$user['address']['street'] = 'King Street';

// set the new value in the same instance if mutable or in a new instance if immutable
$user->set('address.street', 'King Street');

// set the new value in the same instance if mutable or in a new instance if immutable
$user->address->set('street', 'King Street');
```

Same applies when unsetting a value but only `PARTIAL` DTOs can have values unset, otherwise a `Cerbero\Dto\Exceptions\UnsetDtoPropertyException` is thrown:

```php
// throw Cerbero\Dto\Exceptions\ImmutableDtoException if immutable
unset($user->address->street);

// throw Cerbero\Dto\Exceptions\ImmutableDtoException if immutable
unset($user['address']['street']);

// unset the new value in the same instance if mutable or in a new instance if immutable
$user->unset('address.street');

// unset the new value in the same instance if mutable or in a new instance if immutable
$user->address->unset('street');
```


### Available flags

Flags determine how a DTO behaves and can be set when instantiating a new DTO. They support bitwise operations, so we can combine multiple behaviours via `PARTIAL | MUTABLE`.

#### NONE

The flag `Cerbero\Dto\NONE` is simply a placeholder and doesn't alter the behaviour of a DTO in any way.

#### IGNORE_UNKNOWN_PROPERTIES

The flag `Cerbero\Dto\IGNORE_UNKNOWN_PROPERTIES` lets a DTO ignore extra data that is not part of its properties. If this flag is not provided, a `Cerbero\Dto\Exceptions\UnknownDtoPropertyException` is thrown when trying to set a property that is not declared.

#### MUTABLE

The flag `Cerbero\Dto\MUTABLE` lets a DTO override its property values without creating a new DTO instance, as DTOs are immutable by default. If not provided, a `Cerbero\Dto\Exceptions\ImmutableDtoException` is thrown when trying to alter a property without calling `set()` or `unset()`, e.g. `$dto->property = 'foo'` or `unset($dto['property'])`.

#### PARTIAL

The flag `Cerbero\Dto\PARTIAL` lets a DTO be instantiated without some properties. If not provided, a `Cerbero\Dto\Exceptions\MissingValueException` is thrown when properties are missing or when unsetting a property.

#### CAST_PRIMITIVES

The flag `Cerbero\Dto\CAST_PRIMITIVES` lets a DTO cast property values if they don't match the expected primitive type. If not provided, a `Cerbero\Dto\Exceptions\UnexpectedValueException` is thrown when trying to set a value with a wrong primitive type.

#### CAMEL_CASE_ARRAY

The flag `Cerbero\Dto\CAMEL_CASE_ARRAY` lets all DTO properties preserve their camel case names when a DTO is converted into an array.


### Default flags

While flags can be set when instatiating a DTO, default flags can also be defined in the DTO class:

```php
use Cerbero\Dto\Dto;

use const Cerbero\Dto\PARTIAL;
use const Cerbero\Dto\IGNORE_UNKNOWN_PROPERTIES;
use const Cerbero\Dto\MUTABLE;

/**
 * A sample user DTO.
 *
 * @property string $name
 */
class UserDto extends Dto
{
    protected static $defaultFlags = PARTIAL | IGNORE_UNKNOWN_PROPERTIES;
}

// $user->getFlags() will return: PARTIAL | IGNORE_UNKNOWN_PROPERTIES | MUTABLE
$user = UserDto::make($data, MUTABLE);
```

Default flags are combined with the flags passed during the DTO creation, which means that in the code above `$user` has the following flags set: `PARTIAL`, `IGNORE_UNKNOWN_PROPERTIES` and `MUTABLE`.


### Interact with flags

Default flags in a DTO can be retrieved by calling the static method `getDefaultFlags()`, whilst flags belonging to a DTO instance can be read via `getFlags()`:

```php
// PARTIAL | IGNORE_UNKNOWN_PROPERTIES
UserDto::getDefaultFlags();

// PARTIAL | IGNORE_UNKNOWN_PROPERTIES | MUTABLE
$user->getFlags();
```

To determine whether a DTO has one or more flag set, we can call `hasFlags()`:

```php
$user->hasFlags(PARTIAL); // true
$user->hasFlags(PARTIAL | MUTABLE); // true
$user->hasFlags(PARTIAL | NULLABLE); // false
```

DTO flags can be set again by calling the method `setFlags()`. If the DTO is mutable the flags are set against the current instance, otherwise a new instance of the DTO is created with the given flags:

```php
$user = $user->setFlags(PARTIAL | NULLABLE);
```

In case we want to add one or more flags to the already set ones, we can call `addFlags()`. If the DTO is mutable the flags are added to the current instance, otherwise they are added to a new instance:

```php
$user = $user->addFlags(CAMEL_CASE_ARRAY | CAST_PRIMITIVES);
```

Finally to remove flags, we can call `removeFlags()`. If the DTO is mutable the flags are removed from the current instance, otherwise they are removed from a new instance:

```php
$user = $user->removeFlags(IGNORE_UNKNOWN_PROPERTIES | MUTABLE);
```

Please note that when flags are added, removed or set and affect DTO values, properties are re-mapped to apply the effects of the new flags.


### Manipulate properties

Along with `set()` there are other methods that can be called to manipulate a DTO properties. The method `merge()` joins the properties of a DTO with another DTO or anything iterable, e.g. an array:

```php
$user1 = UserDto::make([
    'name' => 'John',
    'address' => [
        'street' => 'King Street',
    ],
], PARTIAL | IGNORE_UNKNOWN_PROPERTIES);

$user2 = UserDto::make([
    'name' => 'Anna',
    'address' => [
        'unit' => 10,
    ],
], PARTIAL | CAMEL_CASE_ARRAY);

// [
//     'name' => 'Anna',
//     'address' => [
//         'street' => 'King Street',
//         'unit' => 10,
//     ],
// ]
$mergedDto = $user1->merge($user2);

// PARTIAL | IGNORE_UNKNOWN_PROPERTIES | CAMEL_CASE_ARRAY
$mergedDto->getFlags();
```

In the example above, the two DTOs are immutable, so another DTO will be created after they merge. If `$user1` was mutable, its own properties would have changed without creating a new DTO instance. Please also note that even DTO flags are merged.

In order to let a DTO carry only some specific properties, we can call the `only()` method and pass a list of properties to keep:

```php
$result = $user->only(['name', 'address'], CAST_PRIMITIVES);
```

Any optional flag passed as second parameter will be merged with the existing flags of the DTO. The changes will be applied to a new instance if the DTO is immutable or to the same instance if it is mutable.

The `only()` method has also an opposite method called `except` that keeps all the DTO properties except for the ones excluded:

```php
$result = $user->except(['name', 'address'], CAST_PRIMITIVES);
```

Sometimes we may need to quickly alter the data of an immutable DTO. In order to do that while preserving the immutability of the DTO after the altering process, we can call the `mutate()` method:

```php
$user->mutate(function (UserData $user) {
    $user->name = 'Jack';
});
```


### Interact with properties

During the creation of a DTO, properties are internally mapped from the data provided. The properties map is an associative array containing the property names as keys and instances of `Cerbero\Dto\DtoProperty` as values. To retrieve such map (maybe for inspection), we can call the `getPropertiesMap()` method:

```php
// ['name' => Cerbero\Dto\DtoProperty, ...]
$map = $user->getPropertiesMap();
```

There are also methods to retrieve property names, all the `DtoProperty` instances, a singular `DtoProperty` instance and finally a method to determine if a property is set at all (useful for example to avoid false negatives when a property value is NULL):

```php
// ['name', 'isAdmin', ...]
$names = $user->getPropertyNames();

// [Cerbero\Dto\DtoProperty, Cerbero\Dto\DtoProperty, ...]
$properties = $user->getProperties();

// Cerbero\Dto\DtoProperty instance for the property "name"
$nameProperty = $user->getProperty('name');

// TRUE as long as the property "name" is set (even if its value is NULL)
$hasName = $user->hasProperty('name');
```


### Convert into array

As shown above, DTOs can behave like arrays, their values can be set and retrieved in an array fashion. DTO itself is iterable, hence can be used in a loop:

```php
foreach($dto as $propertyName => $propertyValue) {
    // ...
}
```

We can call the method `toArray()` to get an array representation of a DTO and its nested DTOs. The resulting array will have keys in snake case by default, unless the DTO has the `CAMEL_CASE_ARRAY` flag:

```php
// [
//     'name' => 'Anna',
//     'is_admin' => true,
//     'address' => [
//         'street' => 'King Street',
//         'unit' => 10,
//     ],
// ]
$user->toArray();
```

Sometimes we may want a value to be converted when a DTO turns into an array. To do so we can register value converters in the `ArrayConverter`:

```php
use Cerbero\Dto\Manipulators\ArrayConverter;
use Cerbero\Dto\Manipulators\ValueConverter;

class DateTimeConverter implements ValueConverter
{
    public function fromDto($value)
    {
        return $value->format('Y-m-d');
    }

    public function toDto($value)
    {
        return new DateTime($value);
    }
}

ArrayConverter::instance()->setConversions([
    DateTime::class => DateTimeConverter::class,
]);

$user = UserDto::make(['birthday' => '01/01/2000']);
$user->birthday; // instance of DateTime
$user->toArray(); // ['birthday' => '01/01/2000']
```

Please note that conversions registered in `ArrayConverter` will apply to all DTOs, whenever they are turned into arrays. In order to transform values only for a specific DTO, read below about the `Listener` class.

Singular conversions can also be added or removed with the methods `addConversion()` and `removeConversion()`:

```php
ArrayConverter::instance()->addConversion(DateTime::class, DateTimeConverter::class);

ArrayConverter::instance()->removeConversion(DateTime::class);
```


### Listen to events

Whenever a DTO sets or gets one of its property values, a listener may intercept the event and alter the outcome. Every DTO can have one listener associated that can be registered via the `Listener` class:

```php
use Cerbero\Dto\Manipulators\Listener;

class UserDtoListener
{
    public function setName($value)
    {
        return ucwords($value);
    }

    public function getSomething($value)
    {
        return $value === null ? rand() : $value;
    }
}

Listener::instance()->listen([
    UserDto::class => UserDtoListener::class,
]);

$user = UserDto::make(['name' => 'john doe', 'something' => null]);
$user->name; // John Doe
$user->something; // random integer
```

In the example above, `UserDtoListener` listens every time a `UserDto` property is set or accessed and calls the related method if existing. The convention behind listeners method names is concatenating the event (`set` or `get`) to the listened property name in camel case, e.g. `setName` or `getIsAdmin`.

Values returned by listener methods override the actual property values. Listeners are not only meant to alter values but also to run arbitrary logic when a DTO property is read or set.

Singular listeners can also be added or removed with the methods `addListener()` and `removeListener()`:

```php
Listener::instance()->addListener(UserDto::class, UserDtoListener::class);

Listener::instance()->removeListener(UserDto::class);
```


### Convert into string

Finally DTOs can be casted into strings. When that happens, their JSON representation is returned:

```php
// {"name":"John Doe"}
(string) $user;
```

A more explicit way to turn a DTO into a JSON is calling the method `toJson()`, which has the same effect of encoding a DTO via `json_encode()`:

```php
$user->toJson();

json_encode($user);
```

If some DTO values need a special transformation when encoded into JSON, such transformation can be defined in `ArrayConverter` (see the section [Convert into array](#convert-into-array) for more details).

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Testing

``` bash
$ composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CODE_OF_CONDUCT](CODE_OF_CONDUCT.md) for details.

## Security

If you discover any security related issues, please email andrea.marco.sartori@gmail.com instead of using the issue tracker.

## Credits

- [Lachlan Krautz][link-lachlan]
- [Andrea Marco Sartori][link-author]
- [All Contributors][link-contributors]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-author]: https://img.shields.io/static/v1?label=author&message=cerbero90&color=50ABF1&logo=twitter&style=flat-square
[ico-php]: https://img.shields.io/packagist/php-v/cerbero/dto?color=%234F5B93&logo=php&style=flat-square
[ico-version]: https://img.shields.io/packagist/v/cerbero/dto.svg?label=version&style=flat-square
[ico-actions]: https://img.shields.io/github/workflow/status/cerbero90/dto/build?style=flat-square&logo=github
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-psr12]: https://img.shields.io/static/v1?label=compliance&message=PSR-12&color=blue&style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/cerbero90/dto.svg?style=flat-square&logo=scrutinizer
[ico-code-quality]: https://img.shields.io/scrutinizer/g/cerbero90/dto.svg?style=flat-square&logo=scrutinizer
[ico-downloads]: https://img.shields.io/packagist/dt/cerbero/dto.svg?style=flat-square

[link-author]: https://twitter.com/cerbero90
[link-php]: https://www.php.net
[link-packagist]: https://packagist.org/packages/cerbero/dto
[link-actions]: https://github.com/cerbero90/dto/actions?query=workflow%3Abuild
[link-psr12]: https://www.php-fig.org/psr/psr-12/
[link-scrutinizer]: https://scrutinizer-ci.com/g/cerbero90/dto/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/cerbero90/dto
[link-downloads]: https://packagist.org/packages/cerbero/dto
[link-lachlan]: https://github.com/lachlankrautz
[link-repo]: https://github.com/rexlabsio/data-transfer-object
[link-contributors]: ../../contributors
