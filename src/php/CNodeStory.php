<?php


/**
 * a class for storing stories
 */
class CNodeStory implements jsonSerializable
{


    protected $nid = null;
    protected $vid = null;
    protected $uid = null;
    protected $username = "";
    protected $title = "";
    protected $teaser = "";
    protected $body = "";
    protected $format = 0;
    protected $parent_nid = null;
    protected $parent = null;
    protected $terms = array();
    protected $promote = "";



    /**
     * Constructor
     */
    public function __construct($story_row, $terms)
    {
        $this->nid = $story_row['node.nid'];
        $this->vid = $story_row['node.vid'];
        $this->uid = $story_row['node.uid'];
        $this->username = $story_row['user.name'];

        // are they identical? which one to use
        $this->title = $story_row['revisions.title'];
        $this->title = $story_row['revisions.title'];



        $this->teaser = nl2p($story_row['revisions.teaser']);
        $this->body = nl2p(str_replace($story_row['revisions.teaser'], "", $story_row['revisions.body']));
        $this->format = $story_row['revisions.format'];


        $this->promote = $story_row['node.promote'];
        $this->terms = $terms;
    }

    /**
     * debug method overrides for print
     */
    public function __toString()
    {
        return __CLASS__ . ":" . $this->title;
    }

    /**
     * return story  title
     */
    public function getTitle()
    {
        return $this->title;
    }


    /**
     * return the story  node id
     */
    public function getNid()
    {
        return $this->nid;
    }


    /**
     * return promote value
     */
    public function getPromote()
    {
        return $this->promote;
    }


    /**
     * static function to compare two story nodes
     */
    static function cmp_obj(&$a, &$b)
    {
        return strcasecmp($a->getTitle(), $b->getTitle());
    }

    /**
     * serialize a story node into a string
     */
    public function jsonSerialize()
    {


        return
            [
                'nid' => $this->nid,
                'title' => $this->title,
                'teaser' => $this->teaser,
                'body' => $this->body,
                'author' => $this->username,
                'terms' => $this->terms,
            ];
    }
}
