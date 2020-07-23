<?php

/**
 * a class for storing CYOT chapter entities
 */
class CNodeCYOT implements JsonSerializable
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
    protected $isRootNode = false;
    protected $children = array();
    protected $suggestedValue = "";



    /**
     * Constructor
     */
    public function __construct($cyot_row, $terms)
    {
        $this->nid = $cyot_row['node.nid'];
        $this->vid = $cyot_row['node.vid'];
        $this->uid = $cyot_row['node.uid'];
        $this->username = $cyot_row['user.name'];

        // are they identical? which one to use
        $this->title = $cyot_row['revisions.title'];
        $this->title = $cyot_row['revisions.title'];

        try {
            $this->teaser = nl2p($cyot_row['revisions.teaser']);
            // some times, the data are formatted in a way, such that the teaser is again contained at the start of the body. We try crudely to remove it, as it is merged back together by the templating.
            $this->body = nl2p(str_replace($cyot_row['revisions.teaser'], "", $cyot_row['revisions.body']));
        } catch (Exception $e) {
            echo 'Exception: ',  $e->getMessage(), "\n";
        }


        // $this->teaser = "";
        // $this->body = "";
        $this->format = $cyot_row['revisions.format'];
        $this->parent_nid = $cyot_row['parent.nid'];

        $this->isRootNode = ($cyot_row['node.type'] === 'cyot_start');
        $this->suggestedValue = $cyot_row['suggested.value'];
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
     * return CYOT chapter title
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * return whether this is a root node
     */
    public function isRootNode()
    {
        return $this->isRootNode;
    }

    /**
     * return the CYOT chapter node id
     */
    public function getNid()
    {
        return $this->nid;
    }

    /**
     * return the CYOT chapter parent node id
     */
    public function getParentNid()
    {
        return $this->parent_nid;
    }

    /**
     * return a reference to the CYOT chapter parent
     */
    public function &getParent()
    {
        return $this->parent;
    }

    /**
     * set the reference to the CYOT chapter parrent
     * (also registers this node as child ofthe parent)
     */
    public function setParent(&$parent)
    {
        $this->parent = $parent;
        $parent->addChild($this);
    }

    /**
     * add child reference to this CYOT node
     */
    public function addChild(&$child)
    {
        $this->children[] = $child;
    }

    /**
     * return references to child nodes
     */
    public function &getChildren()
    {
        return $this->children;
    }

    /**
     * return text of suggested child or emty string
     */
    public function getSuggestedValue()
    {
        return $this->suggestedValue;
    }

    /**
     * return the number of children in the longest path starting from this node
     */
    public function getMaxDepth()
    {
        $maxchilddepth = 0;
        foreach ($this->children as $key => &$node) {
            $maxchilddepth = max($maxchilddepth, $node->getMaxDepth());
        }
        return $maxchilddepth + 1;
    }

    /**
     * return the number of children in the shortest path starting from this node
     */
    public function getMinDepth()
    {
        if (count($this->children) > 0) {
            $minchilddepth = PHP_INT_MAX;
            foreach ($this->children as $key => &$node) {
                $minchilddepth = min($minchilddepth, $node->getMinDepth());
            }
        } else {
            $minchilddepth = 0;
        }

        return $minchilddepth + 1;
    }

    /**
     * static function to compare two CYOT nodes
     */
    static function cmp_obj(&$a, &$b)
    {
        return strcasecmp($a->getTitle(), $b->getTitle());
    }

    /**
     * serialize a CYOT node into a string
     */
    public function jsonSerialize()
    {

        // get children nids
        $children = array();
        foreach ($this->children as $key => &$node) {
            $children[] = array(
                'nid' => $node->getNid(),
                'title' => $node->getTitle(),
                'isSuggested' => ($node->getTitle() == $this->getSuggestedValue())
            );
        }

        return
            [
                'nid' => $this->nid,
                'title' => $this->title,
                'teaser' => $this->teaser,
                'body' => $this->body,
                'author' => $this->username,
                'children' => $children,
                'parent' => $this->parent_nid,
                'terms' => $this->terms,
                'maxdepth' => $this->getMaxDepth(),
                'mindepth' => $this->getMinDepth()
            ];
    }
}
