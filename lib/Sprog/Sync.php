<?php

/**
 * This file is part of the Sprog package.
 *
 * @author (c) Thomas Blum <thomas@addoff.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sprog;

class Sync
{

    public static function articleNameToCategoryName($params)
    {
        try {
            $id = $params['id'];
            $parentId = $params['parent_id'];
            $clangId = $params['clang'];
            $articleName = $params['name'];

            \rex_sql::factory()
                ->setTable(\rex::getTable('article'))
                ->setWhere('(id = :id OR (parent_id = :parent_id AND startarticle = 0)) AND clang_id = :clang', ['id' => $id, 'parent_id' => $parentId, 'clang' => $clangId])
                ->setValue('catname', $articleName)
                ->addGlobalUpdateFields()
                ->update();

            \rex_article_cache::delete($id, $clangId);

        } catch (\rex_sql_exception $e) {
            throw new \rex_api_exception($e);
        }
    }

    public static function categoryNameToArticleName($params)
    {
        try {
            $id = $params['id'];
            $clangId = $params['clang'];
            $categoryName = $params['data']['catname'];

            \rex_sql::factory()
                ->setTable(\rex::getTable('article'))
                ->setWhere('id = :id AND clang_id = :clang', ['id' => $id, 'clang' => $clangId])
                ->setValue('name', $categoryName)
                ->addGlobalUpdateFields()
                ->update();

            \rex_article_cache::delete($id, $clangId);

        } catch (\rex_sql_exception $e) {
            throw new \rex_api_exception($e);
        }
    }

    public static function articleStatus($params)
    {
        try {
            $id = $params['id'];
            $clangId = $params['clang'];
            $status = $params['status'];

            // ----- Update Article Status
            \rex_sql::factory()
                ->setTable(\rex::getTable('article'))
                ->setWhere('id = :id AND clang_id != :clang', ['id' => $id, 'clang' => $clangId])
                ->setValue('status', $status)
                ->addGlobalUpdateFields()
                ->update();

            \rex_article_cache::delete($id);

        } catch (\rex_sql_exception $e) {
            throw new \rex_api_exception($e);
        }
    }

    public static function articleTemplate($params)
    {
        try {
            $id = $params['id'];
            $clangId = $params['clang'];
            $templateId = $params['template_id'];

            // ----- Update Template Id
            \rex_sql::factory()
                ->setTable(\rex::getTable('article'))
                ->setWhere('id = :id AND clang_id != :clang', ['id' => $id, 'clang' => $clangId])
                ->setValue('template_id', $templateId)
                ->addGlobalUpdateFields()
                ->update();

            \rex_article_cache::delete($id);

        } catch (\rex_sql_exception $e) {
            throw new \rex_api_exception($e);
        }
    }

    public static function articleMetainfo($params, $fields)
    {
        $id = $params['id'];
        $clangId = $params['clang'];
        $saveFields = \rex_sql::factory()
            ->setTable(\rex::getTable('article'))
            ->setWhere('id = :id AND clang_id = :clang', ['id' => $id, 'clang' => $clangId])
            ->select(implode(',', $fields))
            ->getArray();

        if (count($saveFields) == 1) {
            $saveFields = $saveFields[0];
            try {
                // ----- Update Category Metainfo
                \rex_sql::factory()
                    ->setTable(\rex::getTable('article'))
                    ->setWhere('id = :id AND clang_id != :clang', ['id' => $id, 'clang' => $clangId])
                    ->setValues($saveFields)
                    ->addGlobalUpdateFields()
                    ->update();

                \rex_article_cache::delete($id);

            } catch (\rex_sql_exception $e) {
                throw new \rex_api_exception($e);
            }
        }
    }

    public static function categoryMetainfo($params, $fields)
    {
        self::articleMetainfo($params, $fields);
    }
}