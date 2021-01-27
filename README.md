# Feature Docu

## Disclaimer / Warning

This is still work in progress. The code already works (in a different context), but I'm new to the dependency publishing game. So I'm still trying to figure stuff out and had to publish it first, in order to test it.

## Table of Contents

1. [Introduction](#introduction)
    1. [Problem 1: Spread out features](#problem-1-spread-out-features)
    1. [Problem 2: Rotting or neglected documentation](#problem-2-rotting-or-neglected-documentation)
    1. [Solution](#solution)
1. [Requirements](#requirements)
1. [Installation](#installation)
    1. [Step 1: Install the bundle using composer](#step-1-install-the-bundle-using-composer)
    1. [Step 2: Add the bundle to your bundles.php](#step-2-add-the-bundle-to-your-bundlesphp)
    1. [(Optional) Register Twig template](#optional-register-twig-template)
1. [Usage](#usage)
    1. [Adding references](#adding-references)
    1. [Using references](#using-references)
        1. [Output references](#output-references)
        1. [Output a "table of contents"-list of identifiers](#output-a-table-of-contents-list-of-identifiers)
        1. [Internally used output formats](#internally-used-output-formats)
1. [TODO](#todo)
1. [Contributors](#contributors)

## Introduction

This bundle auto-generates documentation from specific annotations.

### Problem 1: Spread out features

The truth is in the code, sure, but the code is oftentimes spread out across many files and folders. One business logic feature can be based on various different areas in the code. This is especially problematic if code is used, which is difficult to trace, for example if a class, method or property is called dynamically and hence difficult to find via searching.

In these situations, the easiest way of understanding components is asking the architect to explain it. While doing so, they can just across different files and give brief comments on what happens where.

### Problem 2: Rotting or neglected documentation

Documentation is work, usually associated with a context switch and oftentimes out of date. Also, without a good system, it's difficult to find the documentation for the area one is currently working on.

### Solution

To solve these problems, this library uses annotations to auto-generate up-to-date documentation in an appealing format and present it all in one place. It is sourced from bits and pieces spread out across the code. The documentation happens where the code happens. This enables developers to let documentation become part of their coding work without switching context.

## Requirements

- Build for use in Symfony 5.2, compatibility with other software is uncertain.
- Requires that all analyzed PHP-files follow the PSR4 specification. Specifically, Specification 3.3 "The terminating
  class name corresponds to a file name ending in .php. The file name MUST match the case of the terminating 
  class name." (See https://www.php-fig.org/psr/psr-4/#2-specification)

## Installation

### Step 1: Install the bundle using composer

    composer require steveolotu/featuredocu

### Step 2: Add the bundle to your bundles.php

```php
// config/bundles.php
return [
//..
SteveOlotu\FeatureDocu\FeatureDocuBundle::class => ['all' => true],
];
```

### (Optional) Register Twig template

To use the Twig template, the path needs to be registered first.

To do so, add the path to the file: `config/packages/twig.yaml`.

If the variable "paths" doesn't exist yet, create it, but also add the default_path:

Before:

```twig
twig:
    ...
    default_path: '%kernel.project_dir%/templates'
    ...
```

After:

```twig
twig:
    ...
    default_path: '%kernel.project_dir%/templates'
    paths:
        - '%kernel.project_dir%/templates'
        - '%kernel.project_dir%/vendor/steveolotu/featuredocu/templates'
    ...
```

## Usage

### Adding references

Add one or more `@FeatureDocuAnnotation` annotations to any class, method or property.

Required parameters are:
- identifier: The name of the feature. It is recommended to use slashes to separate levels.
- order: Order in which entries of one specific identifier will be displayed. Must be unique.
  Tipp: Using steps of 100 allows to add elements later on without having to update all respective references.
- Description: Text that explains which part of the feature this specific class, method or property has.

Examples:

    @FeatureDocuAnnotation(identifier="Backup/generate", order="1000", description="UI starting point")

    @FeatureDocuAnnotation(identifier="Backup/delete", order="1040", description="UI starting point.")

### Using references

To generate output, three steps are required:

1. Initialize Object with your desired path

```php
$featureDocu = new FeatureDocu($path, $reader, $twig);
```

2. Analyzing the output checks the files in the specified path
```php
$featureDocu->analyze();
```

3. Generate output.

#### Output references

- Array

```php
$featureDocu->getOutputArray();
```

Html: A html table
 
```php
$featureDocu->getOutputHtml();
```

#### Output a "table of contents"-list of identifiers

Assume there are several references with the two identifiers `backup/create` and `backup/delete`.

- Array: List of features

The following code:
```php
$featureDocu->getFeatureListNestedArray();
```
...will generate an array in the format:
```
['backup' => [
    ['create' => true],
    ['delete' => true],
]
 ```
Note: The value "true" is only a placeholder. The useful data is stored in the keys.
 
- Html: A hierarchically indented and numbered list

The following code:
```php
$featureDocu->getFeatureListHtmlList();
```
...will generate a nested `<ul>`-HTML list:
```
1 backup
 1.1 create
 1.2 delete
 ```
Note: The lowest level entries (those which represent actual identifiers, here: `create` and `backup`) are links to the sections in the HTML reference table (which needs to be on the same page in order for the connection to work). 

#### Internally used output formats

- ListListObject: The internal object used to gather the information.

```php
$featureDocu->getListObject();
```

- To get all class objects found in files, after analyzing the code, use:

```php
$featureDocu->getClasses();
```

## TODO#

- Add some tests
- Requirements are uncertain, check
- Check code style
- Check for unused code

## Contributors

Special thanks to [eFrane](https://github.com/eFrane).
