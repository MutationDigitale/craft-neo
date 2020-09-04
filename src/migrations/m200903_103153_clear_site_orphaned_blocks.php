<?php

namespace benf\neo\migrations;

use benf\neo\elements\Block;
use Craft;
use craft\db\Migration;
use craft\db\Query;
use craft\db\Table;

/**
 * Deletes any Neo block data that is no longer valid for any site.
 *
 * @package benf\neo\migrations
 * @author Spicy Web <plugins@spicyweb.com.au>
 * @since 2.8.8
 */
class m200903_103153_clear_site_orphaned_blocks extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $siteIds = array_map(function($site) {
            return $site->id;
        }, Craft::$app->getSites()->getAllSites());
        $dbBlockIds = (new Query())
            ->select(['id'])
            ->from('{{%neoblocks}}')
            ->where(['not', ['id' => Block::find()->siteId($siteIds)->ids()]])
            ->all();

        $dbBlockIds = array_map(function($block) {
            return $block['id'];
        }, $dbBlockIds);

        $this->delete(Table::ELEMENTS, ['id' => $dbBlockIds]);

        // The above should have also deleted the relevant blocks from the `neoblocks` table, but just in case...
        $this->delete('{{%neoblocks}}', ['id' => $dbBlockIds]);

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m200903_103153_clear_orphaned_blocks cannot be reverted.\n";
        return false;
    }
}
