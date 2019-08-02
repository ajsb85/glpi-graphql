<?php
namespace glpiGraphql\Traits;

trait SchemaRefactory{
    private $_path;
    private $_fields = [];
    private function _schemaInfo( Bool $createFile = true ){
        $this->_path = '../'.pathinfo(str_replace('\\','/',__CLASS__))['dirname'];
        if($createFile){
            $this->_getDBCollumnInfo();
            $this->_classPath();
        }
        
    }

    private function _getDBCollumnInfo(){
        global $DB;
        $query = "SELECT COLUMN_NAME,DATA_TYPE FROM information_schema.columns WHERE table_name='".$this->_table."'";
        $request = $DB->request($query);
        $fields = [];
        if(count($request) > 0){
            foreach($request as $field){
                switch($field['DATA_TYPE']){
                    case 'int':
                    case 'tinyint':
                    case 'bigint':
                    case 'decimal':
                    case 'float':
                    case 'double':
                        $type = 'Int';
                    break;
                    case 'boolean':
                        $type = 'Boolean';
                    break;
                    default:
                        $type = 'String';
                    break;
                }
                $this->_fields[$field['COLUMN_NAME']] = $type;
            }
        }
    }
    private function _classPath(){
        $schemaName = pathinfo($this->_path)['basename'];
        $json = json_encode($this->_fields,JSON_PRETTY_PRINT);
        $json = preg_replace('/"/','',$json);
        $json = preg_replace('/,/','',$json);
        file_put_contents(__DIR__.'/../schemas/'.$schemaName.'.gql','type '.$schemaName.$json);
    }
}

?>