<?php

namespace EFrane\ConsoleAdditions\FeatureDocu\Service;

use EFrane\ConsoleAdditions\FeatureDocu\Exceptions\InvalidArgumentException;
use EFrane\ConsoleAdditions\FeatureDocu\Exceptions\WrongVariableTypeException;
use DateTime;
use Doctrine\Common\Util\ClassUtils;
use UnexpectedValueException;

class PhpHelperService
{
    static public function customScandir(string $path, bool $includeFiles = true, bool $includeDirectories = true): array
    {
        $filesAndDirectories = array_diff(
            scandir($path, 1),
            ['.', '..']
        );
        $output = [];
        foreach ($filesAndDirectories as $fileOrDirectory) {
            $fullPath = $path . '/' . $fileOrDirectory;
            if (is_dir($fullPath) and true === $includeDirectories) {
                $output[] = $fileOrDirectory;
            }
            if (is_file($fullPath) and true === $includeFiles) {
                $output[] = $fileOrDirectory;
            }
        }

        return $output;
    }

    static public function hasExtension(string $fileName, string $extension): bool
    {
        $lengthFileName = strlen($fileName);
        $lengthExtension = strlen($extension);
        $substring = substr($fileName, $lengthFileName - $lengthExtension);

        return $substring === $extension;
    }

    static public function filterByClassType(array $arrayOfObjects, string $desiredClassType): array
    {
        $filteredArray = [];
        foreach ($arrayOfObjects as $object) {
            if (self::isClassTypeOrImplementationOrExtension($object, $desiredClassType)) {
                $filteredArray[] = $object;
            }
        }

        return $filteredArray;
    }

    static public function isClassTypeOrImplementationOrExtension(object $object, string $desiredClassType): bool
    {
        return self::isClassType($object, $desiredClassType) or self::classImplements($object, $desiredClassType);
    }

    static private function isClassType(object $objectToCheck, string $desiredClassType): bool
    {
        return in_array($desiredClassType, [get_class($objectToCheck), ClassUtils::getClass($objectToCheck)]);
    }

    static private function classImplements(object $objectToCheck, string $desiredClassType): bool
    {
        $implementedInterfaces = class_implements($objectToCheck);

        return in_array($desiredClassType, $implementedInterfaces);
    }

    static public function isInterfaceOrAbstract(string $classType): bool
    {
        foreach (['Interface', 'Abstract'] as $item) {
            if (false !== strpos($classType, $item)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @throws InvalidArgumentException
     */
    static public function ensureCorrectType($providedObject, $expectedClass)
    {
        if (!PhpHelperService::isClassTypeOrImplementationOrExtension($providedObject, $expectedClass)) {
            throw new InvalidArgumentException(sprintf(
                'Got "%s", expected "%s".',
                get_class($providedObject),
                $expectedClass
            ));
        }
    }

    public static function extractShortNameFromFullClassName(string $fullClassName): string
    {
        return array_slice(explode('\\', $fullClassName), -1)[0];
    }

    static public function implode(array $array): string
    {
        return implode('<br />', $array);
    }

    static public function ensureThatDatabasesAreReachable(array $tables)
    {
        if (0 === count($tables)) {
            throw new UnexpectedValueException('No tables available, probably no connection to database.');
        }
    }

    /**
     * Based on https://stackoverflow.com/questions/3338123/how-do-i-recursively-delete-a-directory-
     * and-its-entire-contents-files-sub-dir
     */
    static public function recursivelyDeleteFolderAndContents($dir)
    {
        $objects = scandir($dir);
        foreach ($objects as $object) {
            if ($object != "." && $object != "..") {
                if (is_dir($dir . DIRECTORY_SEPARATOR . $object) && !is_link($dir . "/" . $object))
                    self::recursivelyDeleteFolderAndContents($dir . DIRECTORY_SEPARATOR . $object);
                else
                    unlink($dir . DIRECTORY_SEPARATOR . $object);
            }
        }
        rmdir($dir);
    }

    static public function deleteFolderAndContentsRecursively(string $tempDirectory, string $tempSubfolder = '')
    {
        $path = $tempDirectory . $tempSubfolder;
        self::recursivelyDeleteFolderAndContents($path);
    }

    /**
     * Based on https://stackoverflow.com/questions/6041741/fastest-way-to-check-if-a-string-is-json-in-php
     */
    static public function isJson($string): bool
    {
        json_decode($string);

        return (json_last_error() === JSON_ERROR_NONE);
    }

    public static function hashArray($array): string
    {
        return md5(json_encode($array));
    }

    public static function getPercentage(float $absoluteShare, float $total): string
    {
        return round($absoluteShare / $total * 100, 1);
    }

    /**
     * Based on https://stackoverflow.com/questions/4128323/in-array-and-multidimensional-array
     */
    function in_array_recursive(string $needle, array $haystack): bool
    {
        foreach ($haystack as $item) {
            if ($item === $needle || (is_array($item) && self::in_array_recursive($needle, $item))) {
                return true;
            }
        }

        return false;
    }

    /**
     * @see https://stackoverflow.com/questions/18857507/convert-datetime-into-time-ago
     */
    static public function calculateTimeAgo(DateTime $dateTime): string
    {
        $now = new DateTime();
        $delta = $now->diff($dateTime);

        $quantities = array(
            'year' => $delta->y,
            'month' => $delta->m,
            'day' => $delta->d,
            'hour' => $delta->h,
            'minute' => $delta->i,
            'second' => $delta->s);

        $str = '';
        foreach($quantities as $unit => $value) {
            if($value == 0) continue;
            $str .= $value . ' ' . $unit;
            if($value != 1) {
                $str .= 's';
            }
            $str .=  ', ';
        }
        $str = $str == '' ? 'a moment ' : substr($str, 0, -2);

        return $str;
    }

    static public function cutOffSuffix(string $fullString, string $suffix)
    {
        return substr($fullString, 0, strlen($fullString) - strlen($suffix));
    }

    /**
     * @throws InvalidArgumentException
     */
    static public function ensureThatMethodExists(object $item, string $method): void
    {
        if (!method_exists($item, $method)) {
            throw new InvalidArgumentException(sprintf(
                'Object %s does not have a method called "%s".',
                get_class($item),
                $method
            ));
        }
    }

    /**
     * @throws InvalidArgumentException
     * @throws WrongVariableTypeException
     */
    static public function sortByPropertyGetter(string $propertyGetter, array $arrayOfObjects): array
    {
        if (0 === count($arrayOfObjects)) {
            return [];
        }
        $firstObject = array_values($arrayOfObjects)[0];
        if (!is_object($firstObject)) {
            throw new WrongVariableTypeException(sprintf(
                'Got array with elements of type "%s", expected object.',
                get_resource_type($firstObject)
            ));
        }
        self::ensureThatMethodExists($firstObject, $propertyGetter);

        uasort($arrayOfObjects, static function($a, $b) use ($propertyGetter){
            return strcasecmp($a->$propertyGetter(), $b->$propertyGetter()) < 0 ? -1 : 1;
        });

        return $arrayOfObjects;
    }

    /**
     * Source: https://stackoverflow.com/questions/4512398/php-get-namespace-of-included-file
     */
    static public function getNamespaceFromFile(string $file): string
    {
        $ns = NULL;
        $handle = fopen($file, "r");
        if ($handle) {
            while (($line = fgets($handle)) !== false) {
                if (strpos($line, 'namespace') === 0) {
                    $parts = explode(' ', $line);
                    $ns = rtrim(trim($parts[1]), ';');
                    break;
                }
            }
            fclose($handle);
        }

        return $ns;
    }

    /**
     * Based on, but modified: https://stackoverflow.com/questions/7153000/get-class-name-from-file
     */
    static public function getClassNameFromFile(string $file): string
    {
        $fp = fopen($file, 'r');
        $class = $buffer = '';
        $i = 0;
        while (!$class) {
            if (feof($fp)) break;

            $buffer .= fread($fp, 512);
            $tokens = token_get_all($buffer);

            if (strpos($buffer, '{') === false) continue;

            for (;$i<count($tokens);$i++) {
                if (in_array($tokens[$i][0], [T_CLASS, T_INTERFACE, T_ABSTRACT])) {
                    $potentialClassName = $tokens[$i+2][1];
                    if ('class' !== $potentialClassName) {
                        return $tokens[$i+2][1];
                    }
                }
            }
        }
        throw new \Exception(sprintf('No class or interface found in file "%s".', $file));
    }
}