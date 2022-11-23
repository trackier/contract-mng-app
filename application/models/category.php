<?php
use Shared\Services\Db;
use Framework\{Security};

class Category extends Shared\Model
{
   /**
    * @column
    * @readwrite
    * @type text
    * @validate required
    */
    protected $_name;
}
