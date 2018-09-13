<?php

namespace Site\Service;

use Site\Storage\MySQL\DictionaryMapper;
use Krystal\Stdlib\ArrayUtils;
use Krystal\Templating\StringTemplate;
use Krystal\Cache\MemoryCache;

final class DictionaryService
{
    /**
     * Any compliant dictionary mapper
     * 
     * @var \Site\Storage\MySQL\DictionaryMapper
     */
    private $dictionaryMapper;

    /**
     * State initialization
     * 
     * @param \Site\Storage\MySQL\DictionaryMapper $dictionaryMapper
     * @return void
     */
    public function __construct(DictionaryMapper $dictionaryMapper)
    {
        $this->dictionaryMapper = $dictionaryMapper;
    }

    /**
     * Save dictionary entry
     * 
     * @param array $input
     * @return boolean
     */
    public function save(array $input)
    {
        return $this->dictionaryMapper->saveEntity($input['dictionary'], $input['translation']);
    }

    /**
     * Deletes dictionary entry
     * 
     * @param int $id
     * @return boolean
     */
    public function deleteById(int $id)
    {
        return $this->dictionaryMapper->deleteByPk($id);
    }

    /**
     * Finds by alias
     * 
     * @param string $alias
     * @param int $languageId
     * @param array $vars Extra vars to be replaced in the string
     * @return string
     */
    public function findByAlias(string $alias, int $languageId, array $vars = []) : string
    {
        static $cache = null;

        if (is_null($cache)) {
            $cache = new MemoryCache();
        }

        if ($cache->has($languageId)) {
            $rows = $cache->get($languageId);
        } else {
            $rows = ArrayUtils::arrayList($this->fetchAll($languageId), 'alias', 'value');
            $cache->set($languageId, $rows, null);
        }

        return isset($rows[$alias]) ? StringTemplate::template($rows[$alias], $vars) : StringTemplate::template($alias, $vars);
    }

    /**
     * Fetch dictionary entry by its ID
     * 
     * @param int $id Entry ID
     * @param int $langId Language ID filter
     * @return array
     */
    public function fetchById(int $id, int $langId = 0)
    {
        return $this->dictionaryMapper->fetchById($id, $langId);
    }

    /**
     * Fetch all dictionary entries
     * 
     * @param int $langId Language ID filter
     * @return array
     */
    public function fetchAll(int $langId) : array
    {
        return $this->dictionaryMapper->fetchAll($langId);
    }
}
