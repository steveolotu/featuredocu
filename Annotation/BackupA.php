<?php

namespace EFrane\ConsoleAdditions\FeatureDocu\Annotation;

use EFrane\ConsoleAdditions\FeatureDocu\ValueObject\DpaPages\BackupDpa;

/**
 * @Annotation
 * @Target({"CLASS", "METHOD", "PROPERTY"})
 */
class BackupA extends AbstractCoreA
{
    public const KEY_TYPE = 'type';
    public const VALUE_CODE_OF_COMPONENT = 'codeOfComponent';
    public const VALUE_BACKED_UP_CONTENT = 'contentToBackup';
    public const VALUE_GETTER_LIST_FOR_ENTITY_TO_BACKUP = 'getterListForEntityToBackup';

    protected ?string $type = null;
    protected ?string $for = null;

    static public function getAnnotationReadme(): array
    {
        return [
            'Annotation of the type "@BackupA(type="backedUpContent")" must be added:',
            '-1. to the class, which should be backed up:',
            '-2. to the properties which should be backed up',
            '-3. to the corresponding methods (to get 2.). Here, the parameter "for" must be added and match the name
                 of the corresponding property.',
            'fixme: add this:      * @BackupA(type="getterListForEntityToBackup", entity="Topic")'
        ];
        //todo: implement a check, that his has been done
    }

    static public function getDocuComponent(): ?string
    {
        return BackupDpa::NAME;
    }

    public function isCodeOfComponent(): bool
    {
        return $this->type === self::VALUE_CODE_OF_COMPONENT;
    }

    public function isBackedUpContent(): bool
    {
        return $this->type === self::VALUE_BACKED_UP_CONTENT;
    }

    public function getFor(): ?string
    {
        return $this->for;
    }

    public function setFor(string $for): void
    {
        $this->for = $for;
    }
}