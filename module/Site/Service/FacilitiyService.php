<?php

namespace Site\Service;

use Site\Storage\MySQL\FacilitiyCategoryMapper;
use Site\Storage\MySQL\FacilitiyItemMapper;
use Krystal\Stdlib\ArrayUtils;

final class FacilitiyService
{
    /**
     * Any compliant category mapper
     * 
     * @var \Site\Storage\MySQL\FacilitiyCategoryMapper
     */
    private $categoryMapper;

    /**
     * Any compliant item mapper
     * 
     * @var \Site\Storage\MySQL\FacilitiyItemMapper
     */
    private $itemMapper;

    /**
     * State initialization
     * 
     * @param \Site\Storage\MySQL\FacilitiyCategoryMapper $categoryMapper
     * @param \Site\Storage\MySQL\FacilitiyItemMapper $itemMapper
     * @return void
     */
    public function __construct(FacilitiyCategoryMapper $categoryMapper, FacilitiyItemMapper $itemMapper)
    {
        $this->categoryMapper = $categoryMapper;
        $this->itemMapper = $itemMapper;
    }

    /**
     * Update relational data
     * 
     * @param int $hotelId
     * @param array $data
     * @return boolean
     */
    public function updateRelation(int $hotelId, array $data)
    {
        $output = [];
        $ids = array_keys($data['checked']);

        foreach ($ids as $id) {
            // Special type
            $type = isset($data['type'][$id]) ? $data['type'][$id] : null;
            // Append prepared data
            $output[] = [$hotelId, $id, $type];
        }

        return $this->itemMapper->updateRelation($hotelId, $output);
    }

    /**
     * Returns a collection of items
     * 
     * @param int $hotelId
     * @param int $langId
     * @param bool $front Whether to fetch only front items
     * @return array
     */
    public function getItemList($hotelId, int $langId, bool $front = false) : array
    {
        $items = $this->itemMapper->fetchAll($langId, null, $hotelId, $front);
        return ArrayUtils::arrayList($items, 'id', 'name');
    }

    /**
     * Returns collection of categories and their attached items
     * 
     * @param int $langId
     * @param boolean $withCategories Whether to fetch with categories
     * @param integer $hotelId Optional hotel ID filter
     * @return array
     */
    public function getCollection(int $langId, $withCategories = true, $hotelId = null)
    {
        if ($withCategories == true) {
            $categories = $this->categoryMapper->fetchAll($langId);

            foreach ($categories as &$category) {
                $category['items'] = $this->itemMapper->fetchAll($langId, $category['id'], $hotelId);
            }

            return $categories;
        } else {
            return $this->itemMapper->fetchAll($langId, null, $hotelId);
        }
    }

    /**
     * Deletes an item by its associated id
     * 
     * @param string $id
     * @return boolean
     */
    public function deleteItem($id)
    {
        return $this->itemMapper->deleteByPk($id);
    }

    /**
     * Saves an item
     * 
     * @param array $input
     * @return boolean
     */
    public function saveItem(array $input)
    {
        return $this->itemMapper->saveEntity($input['item'], $input['translation']);
    }

    /**
     * Gets item information by its associated ID
     * 
     * @param int $id
     * @param int $langId
     * @return array
     */
    public function getItemById(int $id, int $langId = 0)
    {
        return $this->itemMapper->fetchById($id, $langId);
    }

    /**
     * Return a collection items
     * 
     * @param int $langId
     * @param string $categoryId
     * @return array
     */
    public function getItems(int $langId, $categoryId = null)
    {
        return $this->itemMapper->fetchAll($langId, $categoryId);
    }

    /**
     * Persists a category
     * 
     * @param array $data Category data
     * @return boolean
     */
    public function saveCategory(array $input)
    {
        return $this->categoryMapper->saveEntity($input['category'], $input['translation']);
    }

    /**
     * Deletes a category by its associated ID
     * 
     * @param string $id Category ID
     * @return boolean
     */
    public function deleteCategory($id)
    {
        return $this->categoryMapper->deleteByPk($id);
    }

    /**
     * Returns category list
     * 
     * @param int $langId
     * @return array
     */
    public function getCategoryList(int $langId)
    {
        return ArrayUtils::arrayList($this->categoryMapper->fetchAll($langId), 'id', 'name');
    }

    /**
     * Gets category information by its associated ID
     * 
     * @param string $id
     * @param int $langId
     * @return boolean
     */
    public function getCategoryById(int $id, int $langId = 0)
    {
        return $this->categoryMapper->fetchById($id, $langId);
    }

    /**
     * Returns a collection of categories
     * 
     * @param int $langId
     * @return array
     */
    public function getCategories(int $langId)
    {
        return $this->categoryMapper->fetchAll($langId);
    }
}
