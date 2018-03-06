<?php

namespace Site\Service;

final class Dictionary
{
    /**
     * Dictionary service instance
     * 
     * @var \Site\Service\DictionaryService
     */
    private $dictionaryService;

    /**
     * Current language ID
     * 
     * @var int
     */
    private $languageId;

    /**
     * State initialization
     * 
     * @param \Site\Service\DictionaryService $dictionaryService
     * @param int $languageId
     * @return void
     */
    public function __construct(DictionaryService $dictionaryService, int $languageId)
    {
        $this->dictionaryService = $dictionaryService;
        $this->languageId = $languageId;
    }

    /**
     * Translates a string by alias
     * 
     * @param string $alias
     * @return string
     */
    public function t(string $alias) : string
    {
        return $this->dictionaryService->findByAlias($alias, $this->languageId);
    }
}
