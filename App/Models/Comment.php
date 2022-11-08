<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Utils\ImageHandler;

class Comment extends Model
{
    public function addComment(array $data):int
    {
        $query =  $this->db->prepare('INSERT INTO news_comment (title, text, user, article_id) 
             VALUES(:title, :text, :user, :id)');
        $query->execute([$data["title"], $data["text"], $data["user"], $data["id"]]);
        return $this->db->lastInsertId();
    }

    public function getAllComments(int $article_id)
    {
        $query = $this->db->prepare('SELECT nc.id, nc.article_id, nc.title, nc.text, nc.parent_id, nc.datetime, nc.user,
                                     au.username FROM news_comment nc 
                                    INNER JOIN auth_user au on nc.user = au.id where 
                                    (nc.article_id = :id and nc.parent_id is null);');
        $query->execute(['id' => $article_id]);
        return $query->fetchAll();
    }

    public function getCommentReplies(int $comment_id)
    {
        $query = $this->db->prepare('SELECT nc.id, nc.article_id, nc.title, nc.text, nc.parent_id, nc.datetime, nc.user,
                                     au.username FROM news_comment nc 
                                    INNER JOIN auth_user au on nc.user = au.id where 
                                    (nc.parent_id = :comment_id);');
        $query->execute(['comment_id' => $comment_id]);
        return $query->fetchAll();
    }

    public function doesCommentExist(int $id): bool
    {
        $query =  $this->db->prepare('SELECT * FROM news_comment WHERE id = :id');
        $query->execute(["id" => $id]);

        if ($query->rowCount() > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function getCommentById(int $id): array|bool
    {
        $query =  $this->db->prepare('SELECT nc.id, nc.article_id, nc.title, nc.text, nc.parent_id, nc.datetime,
                    nc.user, au.username FROM news_comment nc 
                                    INNER JOIN auth_user au on nc.user = au.id where nc.id = :id');
        $query->execute(["id" => $id]);
        return $query->fetch();
    }

    public function addReply(array $data):int
    {
        $query =  $this->db->prepare('INSERT INTO news_comment (title, text, user, article_id, parent_id) 
             VALUES(:title, :text, :user, :article_id, :parent_id)');
        $query->execute([$data["title"], $data["text"], $data["user"], $data["article_id"], $data["parent_id"]]);
        return $this->db->lastInsertId();
    }

    public function editComment(array $data)
    {
        $query = $this->db->prepare('UPDATE news_comment SET title = :title, text = :text, datetime=NOW()
                     WHERE id = :id');
        return $query->execute([$data["title"], $data["text"], $data["id"],]);
    }

    public function deleteComment(array $comment): bool
    {
        $query =  $this->db->prepare('DELETE FROM news_comment WHERE id=:comment_id or parent_id=:parrent_id');
        if ($query->execute([$comment["id"], $comment["id"]])) {
            return true;
        } else {
            return false;
        }
    }
}