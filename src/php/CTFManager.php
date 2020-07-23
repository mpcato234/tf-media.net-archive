<?php

/**
 * a class for providing the data
 */
class CTFManager
{

    /**
     * store a link to the database
     */
    protected $link;

    /**
     * store the CYOT tree
     */
    protected $nodelist_cyot;

    /**
     * store the Story list
     */
    protected $nodelist_story;

    /**
     * store the CYOT root nids
     */
    protected $nidlist_cyot;

    /**
     * constructor
     */
    public function __construct($cfg)
    {

        $this->nodelist_cyot = array();
        $this->nodelist_story = array();
        $this->nidlist_cyot = array();

        $this->link = new mysqli($cfg['host'], $cfg['username'], $cfg['password'], $cfg['dbname'], $cfg['port']);

        // make results being presented in utf8
        //$db->query('set character_set_client=utf8');
        //$db->query('set character_set_connection=utf8');
        $this->link->query('set character_set_results=utf8');
        //$db->query('set character_set_server=utf8');
        // print possible connection errors
        // print_r($this->link->connect_error);

        if ($this->link->connect_errno) {
            die('TFManager: connection to database failed.');
        }

        $this->queryNodesCYOT();
        $this->queryNodesStory();
    }

    /**
     * get all CYOT Nodes in an array indexed by nid
     */
    public function getAllCYOTNodes()
    {
        return $this->nodelist_cyot;
    }

    /**
     * retrieve all CYOT root nids sorted by node title ASC
     */
    public function getAllCYOTRootNids()
    {
        return $this->nidlist_cyot;
    }

    /**
     * retrieve all Story nodes indexed by nid
     */
    public function getAllStoryNodes()
    {
        return $this->nodelist_story;
    }


    /**
     * retrieve JSON string of cyot nodes
     */
    public function getAllCYOTNodesJSON()
    {
        return json_encode($this->getAllCYOTNodes(), JSON_PRETTY_PRINT);
    }

    /**
     * retrieve JSON string of story nodes
     */
    public function getAllStoryNodesJSON()
    {
        return json_encode($this->getAllStoryNodes(), JSON_PRETTY_PRINT);
    }
    /**
     * retrieve JSON string of story nodes
     */
    public function getAllCYOTRootNidsJSON()
    {
        return json_encode($this->getAllCYOTRootNids());
    }


    /**
     * populate the CYOT nodelist
     */
    private function queryNodesCYOT()
    {
        //node.nid is the primary key, so all entries are unique by nid
        $result = $this->link->query("SELECT `node`.`nid` AS `node.nid`,
                                `node`.`vid` AS `node.vid`,
                                `node`.`type` AS `node.type`,
                                `node`.`language` AS `node.language`,
                                `node`.`title` AS `node.title`,
                                `node`.`uid` AS `node.uid`,
                                `node`.`status` AS `node.status`,
                                `node`.`created` AS `node.created`,
                                `node`.`changed` AS `node.changed`,
                                `node`.`comment` AS `node.comment`,
                                `node`.`promote` AS `node.promote`,
                                `node`.`moderate`,
                                `node`.`sticky`,
                                `node`.`tnid`,
                                `node`.`translate`,
                                `node_revisions`.`title` AS `revisions.title`,
                                `node_revisions`.`body` AS `revisions.body`,
                                `node_revisions`.`teaser` AS `revisions.teaser`,
                                `node_revisions`.`log`,
                                `node_revisions`.`timestamp`,
                                `node_revisions`.`format` AS `revisions.format`,
                                `users`.`name` AS `user.name`,
                                `parent`.`delta` AS `parent.delta`,
                                `parent`.`field_cyot_parent_nid` AS `parent.nid`,
                                `suggested`.`delta` AS `suggested.delta`,
                                `suggested`.`field_suggested_links_value` AS `suggested.value`
                        FROM `node`
                        LEFT JOIN `node_revisions`
                        ON   `node`.`nid`=`node_revisions`.`nid`
                        AND  `node`.`vid`=`node_revisions`.`vid`
                        LEFT JOIN `users`
                        ON `node`.`uid`=`users`.`uid`
                        LEFT JOIN `content_field_cyot_parent` AS `parent`
                        ON `node`.`nid`=`parent`.`nid`
                        AND `node`.`vid`=`parent`.`vid`
                        LEFT JOIN `content_field_suggested_links` AS `suggested`
                        ON `node`.`nid`=`suggested`.`nid`
                        AND `node`.`vid` = `suggested`.`vid`
                        WHERE `node`.`type`='cyot_start'
                        OR    `node`.`type`='cyot_section'
                        ORDER BY `node`.`title` ASC");

        //$cyot_nodes = array();
        while ($row = $result->fetch_assoc()) {

            // get all the terms
            $terms = $this->link->query("SELECT `term_node`.`nid`,
                                `term_node`.`vid`,
                                `term_node`.`tid`,
                                `term_data`.`name`,
                                `term_data`.`description`,
                                `term_data`.`weight`
                                FROM `term_node`
                                LEFT JOIN `term_data`
                                ON `term_node`.`tid`=`term_data`.`tid`
                                WHERE `term_node`.`nid` = " . $row['node.nid'] . "
                                ORDER BY `term_data`.`name` ASC");
            $terms_a = array();
            while ($r_t = $terms->fetch_assoc()) {
                $terms_a[] = array(
                    'name' => $r_t['name'],
                    'description' => $r_t['description']
                );
            }



            $this->nodelist_cyot[$row['node.nid']] = new CNodeCYOT($row, $terms_a);
        }

        foreach ($this->nodelist_cyot as $nid => &$node) {
            if ($node->isRootNode()) {
                $this->nidlist_cyot[] = $nid;
            } else {
                if (!is_null($node->getParentNid())) {
                    if (array_key_exists($node->getParentNid(), $this->nodelist_cyot)) {
                        $node->setParent($this->nodelist_cyot[$node->getParentNid()]);
                    }
                }
            }
        }
    }

    /**
     * populate the Story nodelist
     */
    private function queryNodesStory()
    {
        //node.nid is the primary key, so all entries are unique by nid
        $result = $this->link->query("SELECT `node`.`nid` AS `node.nid`,
                                `node`.`vid` AS `node.vid`,
                                `node`.`type` AS `node.type`,
                                `node`.`language` AS `node.language`,
                                `node`.`title` AS `node.title`,
                                `node`.`uid` AS `node.uid`,
                                `node`.`status` AS `node.status`,
                                `node`.`created` AS `node.created`,
                                `node`.`changed` AS `node.changed`,
                                `node`.`comment` AS `node.comment`,
                                `node`.`promote` AS `node.promote`,
                                `node`.`moderate`,
                                `node`.`sticky`,
                                `node`.`tnid`,
                                `node`.`translate`,
                                `node_revisions`.`title` AS `revisions.title`,
                                `node_revisions`.`body` AS `revisions.body`,
                                `node_revisions`.`teaser` AS `revisions.teaser`,
                                `node_revisions`.`log`,
                                `node_revisions`.`timestamp`,
                                `node_revisions`.`format` AS `revisions.format`,
                                `users`.`name` AS `user.name`
                        FROM `node`
                        LEFT JOIN `node_revisions`
                        ON   `node`.`nid`=`node_revisions`.`nid`
                        AND  `node`.`vid`=`node_revisions`.`vid`
                        LEFT JOIN `users`
                        ON `node`.`uid`=`users`.`uid`
                        WHERE `node`.`type`='story'
                        ORDER BY `node`.`title` ASC");

        //$cyot_nodes = array();
        while ($row = $result->fetch_assoc()) {

            // get all the terms
            $terms = $this->link->query("SELECT `term_node`.`nid`,
                                `term_node`.`vid`,
                                `term_node`.`tid`,
                                `term_data`.`name`,
                                `term_data`.`description`,
                                `term_data`.`weight`
                                FROM `term_node`
                                LEFT JOIN `term_data`
                                ON `term_node`.`tid`=`term_data`.`tid`
                                WHERE `term_node`.`nid` = " . $row['node.nid'] . "
                                ORDER BY `term_data`.`name` ASC");
            $terms_a = array();
            while ($r_t = $terms->fetch_assoc()) {
                $terms_a[] = array(
                    'name' => $r_t['name'],
                    'description' => $r_t['description']
                );
            }

            $this->nodelist_story[] = new CNodeStory($row, $terms_a);
        }
    }
}
