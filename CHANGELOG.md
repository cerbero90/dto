# Changelog

All notable changes to `dto` will be documented in this file.

Updates should follow the [Keep a CHANGELOG](http://keepachangelog.com/) principles.

## 1.2.0 - 2020-04-01

### Added
- Method `toSnakeCaseArray()` to convert a DTO into an array with snake case keys

### Fixed
- Set values of properties that have not been mapped yet in a partial DTO

### Removed
- Methods to set and get the array converter


## 1.1.1 - 2020-03-27

### Fixed
- Throw exception when data has unknown properties, unless they are ignored


## 1.1.0 - 2020-03-27

### Added
- PSR-12 as standard
- Traits to split DTO logic
- Manipulators: array converter, value converter and listener
- Method to get the declared name of a property type
- Method to merge DTOs
- Method to get only some DTO properties
- Method to exclude some DTO properties

### Removed
- Previous implementation of array converter


## 1.0.0 - 2020-03-19

### Added
- First implementation
