# DTO

[![Required PHP Version][ico-php]][link-packagist]
[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]
[![Coverage Status][ico-scrutinizer]][link-scrutinizer]
[![Quality Score][ico-code-quality]][link-code-quality]
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
* [Interact with values](#interact-with-values)
* [Available flags](#available-flags)
   * [NONE](#none)
   * [IGNORE_UNKNOWN_PROPERTIES](#ignore_unknown_properties)
   * [MUTABLE](#mutable)
   * [PARTIAL](#partial)
   * [NULLABLE](#nullable)
   * [NOT_NULLABLE](#not_nullable)
   * [NULLABLE_DEFAULT_TO_NULL](#nullable_default_to_null)
   * [BOOL_DEFAULT_TO_FALSE](#bool_default_to_false)
   * [ARRAY_DEFAULT_TO_EMPTY_ARRAY](#array_default_to_empty_array)
* [Default flags](#default-flags)
* [Retrieve flags](#retrieve-flags)
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

Flags determine how a DTO behaves and can be set when instantiating a new DTO. They support bitwise operations, so we can combine multiple behaviours via `PARTIAL | NULLABLE | MUTABLE`.

#### NONE

The flag `Cerbero\Dto\NONE` is simply a placeholder and doesn't alter the behaviour of a DTO in any way.

#### IGNORE_UNKNOWN_PROPERTIES

The flag `Cerbero\Dto\IGNORE_UNKNOWN_PROPERTIES` lets a DTO ignore extra data that is not part of its properties. If this flag is not provided, a `Cerbero\Dto\Exceptions\UnknownDtoPropertyException` is thrown when trying to set a property that is not declared.

#### MUTABLE

The flag `Cerbero\Dto\MUTABLE` lets a DTO override its property values without creating a new DTO instance, as DTOs are immutable by default. If not provided, a `Cerbero\Dto\Exceptions\ImmutableDtoException` is thrown when trying to alter a property without calling `set()` or `unset()`, e.g. `$dto->property = 'foo'` or `unset($dto['property'])`.

#### PARTIAL

The flag `Cerbero\Dto\PARTIAL` lets a DTO be instantiated without some properties. If not provided, a `Cerbero\Dto\Exceptions\MissingValueException` is thrown when properties are missing or when unsetting a property.

#### NULLABLE

The flag `Cerbero\Dto\NULLABLE` lets all DTO properties accept NULL as their own value.

#### NOT_NULLABLE

The flag `Cerbero\Dto\NOT_NULLABLE` forbids all DTO properties to have NULL as their own value.

#### NULLABLE_DEFAULT_TO_NULL

The flag `Cerbero\Dto\NULLABLE_DEFAULT_TO_NULL` lets DTO properties accepting NULL as a value to be set to NULL if no other value is provided.

#### BOOL_DEFAULT_TO_FALSE

The flag `Cerbero\Dto\BOOL_DEFAULT_TO_FALSE` lets DTO properties accepting boolean values to be set to FALSE if no other value is provided.

#### ARRAY_DEFAULT_TO_EMPTY_ARRAY

The flag `Cerbero\Dto\ARRAY_DEFAULT_TO_EMPTY_ARRAY` lets DTO properties accepting an array as a value to be set to an empty array if no other value is provided.


### Default flags

While flags can be set when instatiating a DTO, default flags can also be defined in the DTO class:

```php
use Cerbero\Dto\Dto;

use const Cerbero\Dto\PARTIAL;
use const Cerbero\Dto\IGNORE_UNKNOWN_PROPERTIES;

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

Default flags are combined with the flags passed during the DTO creation, so in the code above `$user` will have the following flags set: `PARTIAL`, `IGNORE_UNKNOWN_PROPERTIES` and `MUTABLE`.


### Retrieve flags

Default flags in a DTO can be retrieved by calling the static method `getDefaultFlags()`. Flags of a DTO instance can be read via `getFlags()` instead:

```php
// PARTIAL | IGNORE_UNKNOWN_PROPERTIES
UserDto::getDefaultFlags();

// PARTIAL | IGNORE_UNKNOWN_PROPERTIES | MUTABLE
$user->getFlags();
```

This can be useful for example to check whether a DTO has a given flag set:

```php
$isMutable = ($user->getFlags() & MUTABLE) == MUTABLE;
```


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
], PARTIAL | NOT_NULLABLE);

// [
//     'name' => 'Anna',
//     'address' => [
//         'street' => 'King Street',
//         'unit' => 10,
//     ],
// ]
$mergedDto = $user1->merge($user2);

// PARTIAL | IGNORE_UNKNOWN_PROPERTIES | NOT_NULLABLE
$mergedDto->getFlags();
```

In the example above, the two DTOs are immutable, so another DTO will be created after they merge. If `$user1` was mutable, its own properties would have changed without creating a new DTO instance. Please also note that even DTO flags are merged.

In order to let a DTO carry only some specific properties, we can call the `only()` method and pass a list of properties to keep:

```php
$result = $user->only(['name', 'address'], NULLABLE);
```

Any optional flag passed as second parameter will be merged with the existing flags of the DTO. The changes will be applied to a new instance if the DTO is immutable or to the same instance if it is mutable.

The `only()` method has also an opposite method called `except` that keeps all the DTO properties except for the ones excluded:

```php
$result = $user->except(['name', 'address'], NULLABLE);
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

We can call the method `toArray()` to get an array representation of a DTO and its nested DTOs:

```php
// [
//     'name' => 'Anna',
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

[ico-php]: https://img.shields.io/packagist/php-v/cerbero/dto?color=%238892BF&style=flat-square
[ico-version]: https://img.shields.io/packagist/v/cerbero/dto.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/cerbero90/dto/master.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/cerbero90/dto.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/cerbero90/dto.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/cerbero/dto.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/cerbero/dto
[link-travis]: https://travis-ci.org/cerbero90/dto
[link-scrutinizer]: https://scrutinizer-ci.com/g/cerbero90/dto/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/cerbero90/dto
[link-downloads]: https://packagist.org/packages/cerbero/dto
[link-author]: https://github.com/cerbero90
[link-lachlan]: https://github.com/lachlankrautz
[link-repo]: https://github.com/rexlabsio/data-transfer-object
[link-contributors]: ../../contributors
