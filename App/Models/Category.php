<?php

namespace App\Models;

use App\Core\Model;

class Category extends Model
{
    public function doesCategoryNameExist(string $name): bool
    {
        $query =  $this->db->prepare('SELECT * FROM news_category WHERE name = :name');
        $query->execute(["name" => $name]);

        if ($query->rowCount() > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function addCategory(array $data): int
    {
        $query = $this->db->prepare('INSERT INTO news_category (name, description, slug) 
                                      VALUES(:name, :description, :slug)');
        $query->execute([$data["name"], $data["description"], $data["slug"]]);

        return $this->db->lastInsertId();
    }

    public function getCategoryById(int $id)
    {
        $query =  $this->db->prepare('SELECT * FROM news_category WHERE id = :id');

        $query->execute(["id" => $id]);
        return $query->fetch();
    }

    public function updateCategory(array $category)
    {
        $query = $this->db->prepare('UPDATE news_category SET name = :name, description = :description, slug = :slug
                     WHERE id = :id');
        return $query->execute([$category["name"], $category["description"], $category["slug"], $category["id"]]);
    }

    public function deleteCategory(int $id): bool
    {
        $query =  $this->db->prepare('DELETE FROM news_category WHERE id=:id');
        if ($query->execute([$id])) {
            return true;
        } else {
            return false;
        }
    }

    public function getAllCategories(): array
    {
        {
            $query =  $this->db->prepare(
                'SELECT * FROM news_category'
            );
            $query->execute();
            return $query->fetchAll();
        }
    }

    public function getAllArticlesInCategory(string $slug, int $offset = 0, int $limit = 10): array
    {
        {
            $query =  $this->db->prepare(
                'SELECT na.id, na.title, na.text, na.image, na.image, na.date, au.username FROM news_article as na
                 INNER JOIN auth_user au on na.user = au.id INNER JOIN news_article_category nac on na.id =
                 nac.article_id INNER JOIN news_category nc on nac.category_id = nc.id where nc.slug = :slug ORDER BY 
                 na.date desc LIMIT :limit OFFSET :offset'
            );
            $query->execute([$slug, $limit, $offset]);
            return $query->fetchAll();
        }
    }

    public function doesCategoryExist(string $slug): bool
    {
        $query =  $this->db->prepare('SELECT * FROM news_category WHERE slug = :slug');
        $query->execute(["slug" => $slug]);

        if ($query->rowCount() > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function serializeArticleCategoriesByData(array $data): array
    {
        $allCategories = $this->getAllCategories();
        $serializedCategories = [];

        foreach ($allCategories as $element) {
            if (in_array($element["id"], $data)) {
                $category = [];
                $category["id"] = $element["id"];
                $category["slug"] = $element["slug"];
                $category["name"] = $element["name"];
                $category["active"] = true;
            } else {
                $category["id"] = $element["id"];
                $category["slug"] = $element["slug"];
                $category["name"] = $element["name"];
                $category["active"] = false;
            }
            $serializedCategories[] = $category;
        }
        return $serializedCategories;
    }

    public function getCategoryBySlug(string $slug)
    {
        $query =  $this->db->prepare('SELECT * FROM news_category WHERE slug = :slug');

        $query->execute(["slug" => $slug]);
        return $query->fetch();
    }
}
