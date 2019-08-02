<?php
namespace glpiGraphql\components\monitor;

class Query extends \pjmd89\apiGraphQL\Components{
    private $_arguments = null;
    private $_table = 'glpi_monitors';

    use \glpiGraphql\traits\SchemaRefactory;
    public function __construct($args = null){
        $this->_arguments = $args;
        //$this->_schemaInfo();
    }

    public function monitors(  $args ){
        
        $request = $this->_select($args);
        $return = $this->_getAllValues($request);
        
        return $return;
    }
    private function _getAllValues($request){
        $return = null;
        if(count($request) > 0){
            $return = [];
            foreach($request as $id =>$row){    
                array_push($return,$row);
            }
        }
        return $return;
    }
    private function _select($args){
        global $DB;

        $query = ['FROM'=>'glpi_monitors'];

        if(isset($args['id']) && $args['id'] != 0){
            $query['WHERE'] = ['id'=>$args['id']];
        }
        return $DB->request($query);
    }
}
?>