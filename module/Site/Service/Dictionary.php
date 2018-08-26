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
     * @param array $vars Extra vars to be replaced in the string
     * @return string
     */
    public function __invoke(string $alias, array $vars = [])
    {
        return $this->t($alias, $vars);
    }

    /**
     * Translates a string by alias
     * 
     * @param string $alias
     * @param array $vars Extra vars to be replaced in the string
     * @return string
     */
    public function t(string $alias, array $vars = []) : string
    {
        return $this->dictionaryService->findByAlias($alias, $this->languageId, $vars);
    }
}
