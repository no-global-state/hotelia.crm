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
     * @return array
     */
    public function getCollection()
    {
        $categories = $this->categoryMapper->fetchAll();

        foreach ($categories as &$category) {
            $category['items'] = $this->itemMapper->fetchAll($category['id']);
        }

        return $categories;
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
     * @return array
     */
    public function getItems()
    {
        return $this->itemMapper->fetchAll();
    }

    /**
     * Persists a category
     * 
     * @param array $data Category data
     * @return boolean
     */
    public function saveCategory(array $data)
    {
        return $this->categoryMapper->persist($data);
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
     * @return boolean
     */
    public function getCategoryById($id)
    {
        return $this->categoryMapper->findByPk($id);
    }

    /**
     * Returns a collection of categories
     * 
     * @return array
     */
    public function getCategories()
    {
        return $this->categoryMapper->fetchAll();
    }
}
