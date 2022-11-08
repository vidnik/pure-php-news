<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Utils\ImageHandler;

class News extends Model
{
    public function getAllArticles(): array
    {
        {
            $query =  $this->db->prepare(
                'SELECT na.id, na.title, na.date, au.username, na.image FROM news_article as na
                INNER JOIN auth_user au on na.user = au.id order by na.id desc'
            );
            $query->execute();
            $articles = $query->fetchAll();
            return $articles;
        }
    }

    public function getArticlesInDiapason(int $offset = 0, int $limit = 10): array
    {
        {
            $query =  $this->db->prepare(
                'SELECT na.id, na.title, na.text, na.image, na.image, na.date, au.username FROM news_article as na
                INNER JOIN auth_user au on na.user = au.id order by na.date desc LIMIT :limit OFFSET :offset'
            );
            $query->execute([$limit, $offset]);
            return $query->fetchAll();
        }
    }

    public function doesArticleExist(int $id): bool
    {
        $query =  $this->db->prepare('SELECT * FROM news_article WHERE id = :id');
        $query->execute(["id" => $id]);

        if ($query->rowCount() > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function getArticleById(int $id): array|bool
    {
        $query =  $this->db->prepare('SELECT na.id, na.title, na.text, na.image, na.image, na.date, au.username 
                                FROM news_article as na INNER JOIN auth_user au on na.user = au.id where na.id = :id');
        $query->execute(["id" => $id]);
        return $query->fetch();
    }

    public function addArticle(array $data):int
    {
        $query =  $this->db->prepare('INSERT INTO news_article (title, text, date, image, user) 
             VALUES(:title, :text, :date, :image_path, :user)');
        $query->execute([$data["title"], $data["text"], $data["date"], $data["image_path"], $data["user"]]);
        return $this->db->lastInsertId();
    }

    public function updateArticle(array $data):bool
    {
        $query =  $this->db->prepare('UPDATE news_article SET title = :title, text = :text, image = :image_path
                    WHERE id = :id');
        return $query->execute([$data["title"], $data["text"], $data["image_path"], $data["id"]]);
    }

    public function deleteArticle(array $article): bool
    {
        $query =  $this->db->prepare('DELETE FROM news_article WHERE id=:id');
        if ($query->execute([$article["id"]])) {
            ImageHandler::deleteImage($article["image"]);
            return true;
        } else {
            return false;
        }
    }

    public function updateArticleCategories(int $articleId, array $categories): void
    {
        $query = 'DELETE FROM news_article_category WHERE article_id = ?' ;
        $query = $this->db->prepare($query);
        $query->execute([$articleId]);
        if (!empty($categories)) {
            $query = 'INSERT INTO news_article_category (article_id, category_id) VALUES ';
            $this->multipleInsert($categories, $articleId, $query);
        }
    }

    public function getArticleCategories(int $id): array
    {
        $query = $this->db->prepare('SELECT nc.id, nc.name, nc.slug, nc.description FROM news_category nc
                  LEFT JOIN news_article_category nac on nc.id = nac.category_id 
                  LEFT JOIN news_article na on nac.article_id = na.id where na.id = :id;');
        $query->execute(['id' => $id]);
        return $query->fetchAll();
    }

    public function serializeArticleCategories(int $id): array
    {
        $ArticleCategories = $this->getArticleCategories($id);
        $allCategories = (new Category())->getAllCategories();
        $serializedCategories = [];
        foreach ($allCategories as $category) {
            if (in_array($category, $ArticleCategories)) {
                $category["active"] = true;
            } else {
                $category["active"] = false;
            }
            $serializedCategories[] = $category;
        }
        return $serializedCategories;
    }

    public function search(string $search_query): array
    {
        $query =  $this->db->prepare(
            'SELECT na.id, na.title, na.text, na.image, na.image, na.date, au.username FROM news_article as na
                INNER JOIN auth_user au on na.user = au.id where na.title like :query order by na.date desc'
        );
        $query->execute(['query' => '%' . $search_query . '%']);
        return $query->fetchAll();
    }
}
