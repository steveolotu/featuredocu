# featuredocu

## What it does

- Auto-generates documentation from specific annotations.

### Problem 1: Spread out features

The truth is in the code, sure, but the code is oftentimes spread out across many files and folders. One business logic feature can be based on various different areas in the code. This is especially problematic if code is used, which is difficult to trace, for example if a class, method or property is called dynamically and hence difficult to find via searching.

In these situations, the easiest way of understanding components is asking the architect to explain it. While doing so, they can just across different files and give brief comments on what happens where.

## Problem 2: Rotting or neglected documentation

Documentation is work, usually associated with a context switch and oftentimes out of date. Also, without a good system, it's difficult to find the documentation for the area one is currently working on.

## Solution

To solve these problems, this library uses annotations to auto-generate up-to-date documentation in an appealing format and present it all in one place. It is sourced from bits and pieces spread out across the code. The documentation happens where the code happens. This enables developers to let documentation become part of their coding work without switching context.

## Installation

- Build for use in Symfony 5.2, compatibility with other software is uncertain.

### (Optional) Register Twig template

To use the Twig template, the path needs to be regisered first.

To do so, add the path to the file: "config/packages/twig.yaml".

If the variable "paths" doesn't exist yet, create it, but also add the default_path:

Before:

    twig:
        ...
        default_path: '%kernel.project_dir%/templates'
        ...

After:

    twig:
        ...
        default_path: '%kernel.project_dir%/templates'
        paths:
            - '%kernel.project_dir%/templates'
            - '%kernel.project_dir%/vendor/steveolotu/featuredocu/Twig'
        ...

## Usage

### Adding references

Add one or more @LivingDocumentationA annotations to any class, method or property.

Required parameters are:
- identifier: The name of the feature. It is recommended to use slashes to separate levels.
- order: Order in which entries of one specific identifier will be displayed. Must be unique.
  Tipp: Using steps of 100 allows to add elements later on without having to update all respective references.
- Description: Text that explains which part of the feature this specific class, method or property has.

Examples:

    @LivingDocumentationA(identifier="Backup/generate", order="1000", description="UI starting point")

    @LivingDocumentationA(identifier="Backup/delete", order="1040", description="UI starting point.")

### Using references

To generate output, three steps are required:

1. Initialize Object with your desired path
    
    $featureDocu = new FeatureDocu($path, $reader, $twig);

2. Analyzing the output checks the files in the specified path

    $featureDocu->analyze()

3. Generate output.

#### Available output formats

- ListListObject: The internal object used to gather the information. It's not recommended to use it.

    featureDocu->getListObject();

- Array

    featureDocu->getOutputArray();

- Html: A html table

    featureDocu->getOutputHtml();

### Additional notes

To get all classes found in files, after analyzing the code, use:

featureDocu->getClasses();