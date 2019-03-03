<?php
/**
 * Created by PhpStorm.
 * User: PBX_g33k
 * Date: 4/12/2018
 * Time: 10:41 PM.
 */

namespace App\Event;

use App\Model\JAVTitle;
use Symfony\Component\EventDispatcher\Event;

class DuplicateTitleFoundEvent extends Event
{
    const NAME = 'title.duplicate';

    /**
     * @var JAVTitle
     */
    protected $left;

    /**
     * @var JAVTitle
     */
    protected $right;

    public function __construct(JAVTitle $left, JAVTitle $right)
    {
        $this->left = $left;
        $this->right = $right;
    }

    /**
     * @return JAVTitle
     */
    public function getLeft(): JAVTitle
    {
        return $this->left;
    }

    /**
     * @return JAVTitle
     */
    public function getRight(): JAVTitle
    {
        return $this->right;
    }
}
