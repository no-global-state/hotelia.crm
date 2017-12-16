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
     * @param string $hotelId
     * @param array $ids
     * @return boolean
     */
    public function updateRelation($hotelId, array $ids)
    {
        return $this->itemMapper->updateRelation($hotelId, $ids);
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
                $category['items'] = $this->itemMapper->fetchAll($category['id'], $hotelId);
            }

            return $categories;
        } else {
            return $this->itemMapper->fetchAll(null, $hotelId);
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
     * @param array $data
     * @return boolean
     */
    public function saveItem(array $data)
    {
        return $this->itemMapper->persist($data);
    }

    /**
     * Gets item information by its associated ID
     * 
     * @param string $id
     * @return boolean
     */
    public function getItemById($id)
    {
        return $this->itemMapper->findByPk($id);
    }

    /**
     * Return a collection items
     * 
     * @param string $categoryId
     * @return array
     */
    public function getItems($categoryId = null)
    {
        return $this->itemMapper->fetchAll($categoryId);
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
     * @return array
     */
    public function getCategoryList()
    {
        return ArrayUtils::arrayList($this->categoryMapper->fetchAll(), 'id', 'name');
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
