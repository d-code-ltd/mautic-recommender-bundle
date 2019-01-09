<?php

/*
 * @copyright   2018 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticRecommenderBundle\Filter\Recommender\Decorator;


use MauticPlugin\MauticRecommenderBundle\Filter\Fields\Fields;
use MauticPlugin\MauticRecommenderBundle\Filter\Recommender\Query\ItemEventQueryBuilder;
use MauticPlugin\MauticRecommenderBundle\Filter\Recommender\Query\ItemEventValueQueryBuilder;
use MauticPlugin\MauticRecommenderBundle\Filter\Recommender\Query\ItemQueryBuilder;
use MauticPlugin\MauticRecommenderBundle\Filter\Recommender\Query\ItemValueQueryBuilder;

class Dictionary
{
    CONST ALLOWED_TABLES = ['recommender_item','recommender_item_property_value', 'recommender_event_log', 'recommender_event_log_property_value'];

    /**
     * @var Fields
     */
    private $fields;


    /**
     * SegmentChoices constructor.
     *
     * @param Fields              $fields
     */
    public function __construct(Fields $fields)
    {

        $this->fields = $fields;
    }

    public function getDictionary()
    {
        $dictionary = [];
        foreach (self::ALLOWED_TABLES as $table) {
            $fields = $this->fields->getFields($table);
            foreach ($fields as $key => $field) {

                switch ($table) {
                    case 'recommender_item':
                        $dictionary[$key] = [
                            'type'          => ItemQueryBuilder::getServiceId(),
                            'foreign_table' => $table,
                            'foreign_table_field' => $key,
                        ];
                        break;
                    case 'recommender_item_property_value':
                        $dictionary[$key] = [
                            'type'          => ItemValueQueryBuilder::getServiceId(),
                            'foreign_table' => $table,
                            'foreign_table_field' => $key,
                        ];
                        break;
                    case 'recommender_event_log':
                        $dictionary[$key] = [
                            'type'          => ItemEventQueryBuilder::getServiceId(),
                            'foreign_table' => $table,
                            'foreign_table_field' => $key,
                        ];
                        break;
                    case 'recommender_event_log_property_value':
                        $dictionary[$key] = [
                            'type'          => ItemEventValueQueryBuilder::getServiceId(),
                            'foreign_table' => $table,
                            'field' => $key,
                            'foreign_table_field' => 'value',
                        ];
                        break;
                }
            }
        }
        return $dictionary;
    }
}

