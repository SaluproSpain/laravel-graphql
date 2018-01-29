<?php
namespace Salupro\GraphQL\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Salupro\GraphqlParser\Parser;

class GraphQLController extends \Illuminate\Routing\Controller
{
    public function execute(Request $request)
    {
        $query = preg_replace('/^query /', '', $request->get('query'));
        $result = $this->exec($query);

        return new JsonResponse($result);
    }

    public function exec($query)
    {
        $parser = new Parser('{'.$query.'}');
        $parsed_query = $parser->parseQuery();
//        $aliases = $this->searchForAliases($parsed_query->fieldList);

        $result = $this->iterate($parsed_query->fieldList[0]);


        return $result;
    }

    public function aliasesToArray($fields)
    {
        $result = [];
        foreach($fields as $field) {
            if($field->alias) {
                $result[$field->name] = $field->alias;
            }
        }
        return $result;
    }

    public function getFieldsToShow($fields)
    {
        $result = [];
        foreach($fields as $field) {
            $result[] = $field->name;
        }
        return $result;
    }

    public function iterate($fields)
    {
        $result = new \stdClass();
        $result->data = [];
        foreach($fields->fields as $field) {
            if(!$this->checkIfFieldsAreQueries($field)) {
                $result = $this->iterate($field->fields);
            } else {
                $class = '\App\GraphQL\Query\\'.ucfirst($field->name).'Query';
                $query = new $class();

                $resolution = $query->resolve($this->aliasesToArray($field->fields), $field->argumentsToArray());

                $resolution->items = $resolution->items->map(function($item) use ($field){
                    $item = (array) $item;
                    foreach($item as $name => $value) {
                        if(!in_array($name, $this->getFieldsToShow($field->fields))) {
                            unset($item[$name]);
                        }
                        foreach ($this->aliasesToArray($field->fields) as $column => $alias) {
                            if ($name == $column) {
                                $item[$alias] = $value;
                                unset($item[$name]);
                            }
                        }
                    }
                    return (object) $item;
                });

                $result->data = $resolution;
                break;
            }
        }

        return $result;
    }

    public function checkIfFieldsAreQueries($field)
    {
        $result = false;
        if(class_exists('\App\GraphQL\Query\\'.ucfirst($field->name).'Query')) {
            $result = true;
        }
        return $result;
    }
}