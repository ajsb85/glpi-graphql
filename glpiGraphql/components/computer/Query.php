<?php
namespace glpiGraphql\components\computer;

class Query extends \pjmd89\apiGraphQL\Components{
    private $_arguments = null;
    private $_table = 'glpi_computers';

    use \glpiGraphql\traits\SchemaRefactory;
    public function __construct( $arg = null ){
        $this->_schemaInfo();
    }

    public function computers(  $args ){
        
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
        $query = ['FROM'=>'glpi_computers'];

        if(isset($args['id']) && $args['id'] != 0){
            $query['WHERE'] = ['id'=>$args['id']];
        }
        return $DB->request($query);
    }
}
?>