<?php

namespace SteveOlotu\FeatureDocu\Service;

use SteveOlotu\FeatureDocu\Exceptions\FileComplicationException;
use Doctrine\Common\Util\ClassUtils;
use Symfony\Component\Intl\Exception\InvalidArgumentException;

class PhpHelperService
{
    static public function customScanDir(string $path, bool $includeFiles = true, bool $includeDirectories = true): array
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

    public static function extractShortNameFromFullClassName(string $fullClassName): string
    {
        return array_slice(explode('\\', $fullClassName), -1)[0];
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
     *
     * @throws FileComplicationException
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
        throw new FileComplicationException(sprintf('No class or interface found in file "%s".', $file));
    }



    /**
     * @throws InvalidArgumentException
     */
    static public function ensureCorrectType($providedThing, string $expectedClass): void
    {
        if (!self::isClassTypeOrImplementationOrExtension($providedThing, $expectedClass)) {
            throw new InvalidArgumentException(sprintf(
                'Got "%s", expected "%s".',
                get_class($providedThing),
                $expectedClass
            ));
        }
    }
}