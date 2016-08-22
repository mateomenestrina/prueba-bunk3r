<?php

use Phinx\Migration\AbstractMigration;

class UpdateCampoDatetime extends AbstractMigration
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

        // update para fechas como esta: Sun May 20 01:04:39 +0000 2012
        $this->query("UPDATE bunker.twitter_tweets SET twitter_created_at_datetime = STR_TO_DATE(twitter_created_at, '%a %M %d %H:%i:%S +0000 %Y') WHERE twitter_created_at LIKE  '%+%'; ");

        // ahora solo me quedan las fechas que no tiene el string "+", y se que el campo twitter_created_at_datetime para estas otras fechas sigue en "0000-00-00 00:00:00", entonces hago el update sobre estos.

        // update para fechas como esta: 2014-01-03T18:04:58.000Z
        $this->query("UPDATE bunker.twitter_tweets SET twitter_created_at_datetime = STR_TO_DATE(twitter_created_at, '%Y-%m-%dT%H:%i:%S')  WHERE twitter_created_at_datetime='0000-00-00 00:00:00'; ");


    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}