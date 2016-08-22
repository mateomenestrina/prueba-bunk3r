<?php

use Phinx\Migration\AbstractMigration;

class AgregarIndices extends AbstractMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-change-method
     *
     * Uncomment this method if you would like to use it.
     *
    public function change()
    {
    }
    */
    
    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->query("CREATE INDEX `idx_twitter_tweet_entities_tag`  ON `bunker`.`twitter_tweet_entities` (tag) COMMENT '';");
        $this->query("CREATE INDEX `idx_twitter_tweet_entities_type`  ON `bunker`.`twitter_tweet_entities` (type) COMMENT '';");
        $this->query("CREATE INDEX `idx_twitter_actors_username`  ON `bunker`.`twitter_actors` (username) COMMENT '';");
        $this->query("CREATE INDEX `idx_twitter_tweets_twitter_created_at_datetime`  ON `bunker`.`twitter_tweets` (twitter_created_at_datetime) COMMENT ''; ");
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}